<?php

namespace App\MessageHandler;

use App\Entity\Document;
use App\Entity\ExtractedEntity;
use App\Entity\Relation;
use App\Message\ExtractDocumentEntities;
use App\Service\Neo4jSync;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsMessageHandler]
class ExtractDocumentEntitiesHandler
{
    public function __construct(
        private EntityManagerInterface $em,
        private HttpClientInterface    $http,
        private string                 $hfToken,
        private string                 $nerModel,
        private Neo4jSync              $neo4jSync,
        private ?string                $reModel = null
    ) {}

    public function __invoke(ExtractDocumentEntities $msg)
    {
        $doc = $this->em->getRepository(Document::class)
                        ->find($msg->getDocumentId());
        if (! $doc) {
            return;
        }
        $text = $doc->getRawContent();

        // 2) Call HF NER
        $url = sprintf('https://api-inference.huggingface.co/models/%s', $this->nerModel);

        $nerResponse = $this->http->request('POST',
            $url,
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->hfToken,
                    'Content-Type'  => 'application/json',
                ],
                'json'    => ['inputs' => $text],
            ]
        )->toArray();

        // 3) Persist entities and stash in a map
        $entityMap = [];
        foreach ($nerResponse as $ent) {
            $name  = $ent['word'];
            $group = $ent['entity_group'];

            $e = new ExtractedEntity();
            $e->setName($name)
              ->setType($group)
              ->setDocument($doc);

            $this->em->persist($e);
            $entityMap[$name] = $e;
        }

        // 4) Build co-occurrence relations per sentence
        $sentences = preg_split('/(?<=[\.\!?])\s+/', $text);
        $relationList = [];
        foreach ($sentences as $sentence) {
            $found = [];
            foreach ($entityMap as $name => $entity) {
                if (stripos($sentence, $name) !== false) {
                    $found[$name] = $entity;
                }
            }
            $names = array_keys($found);
            for ($i = 0, $n = count($names); $i < $n; $i++) {
                for ($j = $i + 1; $j < $n; $j++) {
                    $from = $found[$names[$i]];
                    $to   = $found[$names[$j]];

                    $r = new Relation();
                    $r->setFromEntity($from)
                      ->setToEntity($to)
                      ->setLabel('co_occurrence')
                      ->setDocument($doc);

                    $this->em->persist($r);
                    $relationList[] = $r;
                }
            }
        }

        // 5) Flush everything
        $this->em->flush();

        // 6) Sync to Neo4j
        $this->neo4jSync->syncEntities($entityMap);
        $this->neo4jSync->syncRelations($relationList);
    }
}
