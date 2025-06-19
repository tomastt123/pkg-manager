<?php

namespace App\GraphQL\Resolver;

use ApiPlatform\GraphQl\Resolver\QueryItemResolverInterface;
use App\Dto\DocumentGraph;
use App\Entity\Document;
use Doctrine\ORM\EntityManagerInterface;

final class DocumentGraphResolver implements QueryItemResolverInterface
{
    public function __construct(private EntityManagerInterface $em) {}

    /**
     * @param mixed $root   // unused here
     * @param array $args   // ['id' => …]
     */
    public function __invoke($root, array $args): DocumentGraph
    {
        $doc = $this->em
            ->getRepository(Document::class)
            ->find($args['id']);

        if (! $doc) {
            throw new \RuntimeException(sprintf('Document %d not found', $args['id']));
        }

        return new DocumentGraph(
            $doc->getId(),
            $doc->getExtractedEntities()->toArray(),
            $doc->getRelations()->toArray()
        );
    }
}