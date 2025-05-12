<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\DocumentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use DateTimeInterface;

#[ORM\Entity(repositoryClass: DocumentRepository::class)]
#[ApiResource]
class Document
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $url = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $rawContent = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?DateTimeInterface $fetchedAt = null;

    /**
     * @var Collection<int, ExtractedEntity>
     */
    #[ORM\OneToMany(targetEntity: ExtractedEntity::class, mappedBy: 'document')]
    private Collection $extractedEntities;

    /**
     * @var Collection<int, Relation>
     */
    #[ORM\OneToMany(targetEntity: Relation::class, mappedBy: 'document')]
    private Collection $relations;

    public function __construct()
    {
        $this->extractedEntities = new ArrayCollection();
        $this->relations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getRawContent(): ?string
    {
        return $this->rawContent;
    }

    public function setRawContent(string $rawContent): self
    {
        $this->rawContent = $rawContent;

        return $this;
    }

    public function getFetchedAt(): ?DateTimeInterface
    {
        return $this->fetchedAt;
    }

    public function setFetchedAt(DateTimeInterface $fetchedAt): self
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

    public function addExtractedEntity(ExtractedEntity $extractedEntity): static
    {
        if (!$this->extractedEntities->contains($extractedEntity)) {
            $this->extractedEntities->add($extractedEntity);
            $extractedEntity->setDocument($this);
        }

        return $this;
    }

    public function removeExtractedEntity(ExtractedEntity $extractedEntity): static
    {
        if ($this->extractedEntities->removeElement($extractedEntity)) {
            // set the owning side to null (unless already changed)
            if ($extractedEntity->getDocument() === $this) {
                $extractedEntity->setDocument(null);
            }
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
        if ($this->relations->removeElement($relation)) {
            // set the owning side to null (unless already changed)
            if ($relation->getDocument() === $this) {
                $relation->setDocument(null);
            }
        }

        return $this;
    }
}
