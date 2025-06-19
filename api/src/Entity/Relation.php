<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use App\Repository\RelationRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\ExtractedEntity;
use App\Entity\Document;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: RelationRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['relation:read']],
    denormalizationContext: ['groups' => ['relation:write']],
    graphQlOperations: [
        new Query(),           // relation(id: ID!): Relation
        new QueryCollection()  // relations(...): [Relation]
    ]
)]
class Relation
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    #[Groups(['relation:read', 'document_graph:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(
        targetEntity: ExtractedEntity::class,
        inversedBy: 'relationsFrom'
    )]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['relation:read', 'relation:write', 'document_graph:read'])]
    private ?ExtractedEntity $fromEntity = null;

    #[ORM\ManyToOne(
        targetEntity: ExtractedEntity::class,
        inversedBy: 'relationsTo'
    )]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['relation:read', 'relation:write', 'document_graph:read'])]
    private ?ExtractedEntity $toEntity = null;

    #[ORM\Column(length: 255)]
    #[Groups(['relation:read', 'relation:write', 'document_graph:read'])]
    private ?string $label = null;

    #[ORM\ManyToOne(
        targetEntity: Document::class,
        inversedBy: 'relations'
    )]
    #[ORM\JoinColumn(nullable: false)]
    private ?Document $document = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFromEntity(): ?ExtractedEntity
    {
        return $this->fromEntity;
    }

    public function setFromEntity(ExtractedEntity $fromEntity): static
    {
        $this->fromEntity = $fromEntity;
        return $this;
    }

    public function getToEntity(): ?ExtractedEntity
    {
        return $this->toEntity;
    }

    public function setToEntity(ExtractedEntity $toEntity): static
    {
        $this->toEntity = $toEntity;
        return $this;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): static
    {
        $this->label = $label;
        return $this;
    }

    public function getDocument(): ?Document
    {
        return $this->document;
    }

    public function setDocument(Document $document): static
    {
        $this->document = $document;
        return $this;
    }
}
