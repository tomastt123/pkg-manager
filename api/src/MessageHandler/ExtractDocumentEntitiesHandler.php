<?php

namespace App\MessageHandler;

use App\Entity\Document;
use App\Entity\ExtractedEntity;
use App\Entity\Relation;
use App\Message\ExtractDocumentEntities;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsMessageHandler]
class ExtractDocumentEntitiesHandler
{
    public function __construct(
        private EntityManagerInterface $em,
        private HttpClientInterface $http,
        private string $hfToken,
        private string $nerModel,
        private ?string $reModel = null,
    ) {}

    public function __invoke(ExtractDocumentEntities $msg)
    {
        // 1) Load document
        $doc = $this->em->getRepository(Document::class)
                        ->find($msg->getDocumentId());
        if (! $doc) {
            return; // or throw
        }
        $text = $doc->getRawContent();

        // 2) Call HF NER
        $nerResponse = $this->http->request('POST',
            "https://api-inference.huggingface.co/models/{$this->nerModel}",
            [
                'headers' => [
                    'Authorization' => "Bearer {$this->hfToken}"
                ],
                'json' => ['inputs' => $text],
            ]
        )->toArray();

        // 3) Persist entities **and** stash in a map
        $entityMap = [];
        foreach ($nerResponse as $ent) {
            $name  = $ent['word'];
            $group = $ent['entity_group'];

            $e = new ExtractedEntity();
            $e->setName($name)
              ->setType($group)
              ->setDocument($doc);

            $this->em->persist($e);
            // key by the exact text the RE model will reference
            $entityMap[$name] = $e;
        }

        // 4) (Optional) call RE and link up via the in-memory map
        if ($this->reModel) {
            $reResponse = $this->http->request('POST',
                "https://api-inference.huggingface.co/models/{$this->reModel}",
                [
                    'headers' => [
                        'Authorization' => "Bearer {$this->hfToken}"
                    ],
                    'json' => ['inputs' => $text],
                ]
            )->toArray();

            foreach ($reResponse as $rel) {
                $head = $rel['head_entity'];
                $tail = $rel['tail_entity'];
                $label = $rel['relation'];

                $from = $entityMap[$head] ?? null;
                $to   = $entityMap[$tail] ?? null;

                if (! $from || ! $to) {
                    continue;
                }

                $r = new Relation();
                $r->setFromEntity($from)
                  ->setToEntity($to)
                  ->setLabel($label)
                  ->setDocument($doc);

                $this->em->persist($r);
            }
        }

        // 5) Flush everything at once
        $this->em->flush();
    }
}
