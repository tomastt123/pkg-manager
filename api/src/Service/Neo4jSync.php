<?php

namespace App\Service;

use App\Entity\ExtractedEntity;
use App\Entity\Relation;
use Neo4j\Neo4jBundle\Decorators\SymfonyClient;

class Neo4jSync
{
    private SymfonyClient $client;

    public function __construct(SymfonyClient $client)
    {
        $this->client = $client;
    }

    public function syncEntities(iterable $entities): void
    {
        foreach ($entities as $entity) {
            $this->client->run(
                'MERGE (e:Entity {id: $id})
                 SET e.name = $name, e.type = $type',
                [
                    'id'   => $entity->getId(),
                    'name' => $entity->getName(),
                    'type' => $entity->getType(),
                ]
            );
        }
    }

    public function syncRelations(iterable $relations): void
    {
        foreach ($relations as $relation) {
            $this->client->run(
                'MATCH (a:Entity {id: $fromId}), (b:Entity {id: $toId})
                 MERGE (a)-[r:CO_OCCURRENCE]->(b)
                 SET r.docId = $docId, r.label = $label',
                [
                    'fromId' => $relation->getFromEntity()->getId(),
                    'toId'   => $relation->getToEntity()->getId(),
                    'docId'  => $relation->getDocument()->getId(),
                    'label'  => $relation->getLabel(),
                ]
            );
        }
    }
}
