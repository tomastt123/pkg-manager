<?php

namespace App\Dto;

use App\Entity\ExtractedEntity;
use App\Entity\Relation;

final class DocumentGraph
{
    public function __construct(
        public int $documentId,
        /** @var ExtractedEntity[] */
        public array $entities,
        /** @var Relation[] */
        public array $relations,
    ) {}
}