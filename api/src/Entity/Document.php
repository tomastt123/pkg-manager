<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use App\Repository\DocumentRepository;
use App\GraphQL\Resolver\DocumentGraphResolver;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use DateTimeInterface;

#[ORM\Entity(repositoryClass: DocumentRepository::class)]
#[ApiResource(
    // REST operations
    operations: [
        new Get(normalizationContext: ['groups' => ['document:read']]),
        new GetCollection(normalizationContext: ['groups' => ['document:read']])
    ],
    // GraphQL operations
    graphQlOperations: [
        new Query(normalizationContext: ['groups' => ['document:read']]),
        new QueryCollection(normalizationContext: ['groups' => ['document:read']]),
        // custom combined-graph query
        new Query(
            name: 'documentGraph',
            description: 'Fetch entities + relations for a document',
            resolver: DocumentGraphResolver::class,
            read: false,
            normalizationContext: ['groups' => ['document_graph:read']]
        )
    ]
)]
class Document
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    #[Groups(['document:read','document_graph:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['document:read','document:write'])]
    private ?string $url = null;

    #[ORM\Column(length: 255)]
    #[Groups(['document:read','document:write'])]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['document:read','document:write'])]
    private ?string $rawContent = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['document:read','document:write'])]
    private ?DateTimeInterface $fetchedAt = null;

    /** @var Collection<int,ExtractedEntity> */
    #[ORM\OneToMany(
        mappedBy: 'document',
        targetEntity: ExtractedEntity::class,
        cascade: ['persist','remove']
    )]
    #[Groups(['document_graph:read'])]
    private Collection $extractedEntities;

    /** @var Collection<int,Relation> */
    #[ORM\OneToMany(
        mappedBy: 'document',
        targetEntity: Relation::class,
        cascade: ['persist','remove']
    )]
    #[Groups(['document_graph:read'])]
    private Collection $relations;

    public function __construct()
    {
        $this->extractedEntities = new ArrayCollection();
        $this->relations         = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }
    public function setUrl(string $url): static
    {
        $this->url = $url;
        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }
    public function setTitle(string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function getRawContent(): ?string
    {
        return $this->rawContent;
    }
    public function setRawContent(?string $rawContent): static
    {
        $this->rawContent = $rawContent;
        return $this;
    }

    public function getFetchedAt(): ?DateTimeInterface
    {
        return $this->fetchedAt;
    }
    public function setFetchedAt(?DateTimeInterface $fetchedAt): static
    {
        $this->fetchedAt = $fetchedAt;
        return $this;
    }

    /**
     * @return Collection<int, ExtractedEntity>
     */
    public function getExtractedEntities(): Collection
    {
        return $this->extractedEntities;
    }

    public function addExtractedEntity(ExtractedEntity $entity): static
    {
        if (!$this->extractedEntities->contains($entity)) {
            $this->extractedEntities->add($entity);
            $entity->setDocument($this);
        }
        return $this;
    }

    public function removeExtractedEntity(ExtractedEntity $entity): static
    {
        if ($this->extractedEntities->removeElement($entity) &&
            $entity->getDocument() === $this) {
            $entity->setDocument(null);
        }
        return $this;
    }

    /**
     * @return Collection<int, Relation>
     */
    public function getRelations(): Collection
    {
        return $this->relations;
    }

    public function addRelation(Relation $relation): static
    {
        if (!$this->relations->contains($relation)) {
            $this->relations->add($relation);
            $relation->setDocument($this);
        }
        return $this;
    }

    public function removeRelation(Relation $relation): static
    {
        if ($this->relations->removeElement($relation) &&
            $relation->getDocument() === $this) {
            $relation->setDocument(null);
        }
        return $this;
    }
}
