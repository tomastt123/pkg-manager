<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\ExtractedEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Document;
use App\Entity\Relation;

#[ORM\Entity(repositoryClass: ExtractedEntityRepository::class)]
#[ApiResource]
class ExtractedEntity
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name = null;

    #[ORM\Column(length: 100)]
    private ?string $type = null;

    #[ORM\ManyToOne(targetEntity: Document::class, inversedBy: 'extractedEntities')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Document $document = null;

    #[ORM\OneToMany(
        mappedBy: 'fromEntity',
        targetEntity: Relation::class,
        cascade: ['persist','remove']
    )]
    private Collection $relationsFrom;

    #[ORM\OneToMany(
        mappedBy: 'toEntity',
        targetEntity: Relation::class,
        cascade: ['persist','remove']
    )]
    private Collection $relationsTo;

    public function __construct()
    {
        $this->relationsFrom = new ArrayCollection();
        $this->relationsTo   = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }
    public function getName(): ?string { return $this->name; }
    public function setName(?string $name): static
    {
        $this->name = $name;
        return $this;
    }
    public function getType(): ?string { return $this->type; }
    public function setType(string $type): static
    {
        $this->type = $type;
        return $this;
    }
    public function getDocument(): ?Document { return $this->document; }
    public function setDocument(?Document $doc): static
    {
        $this->document = $doc;
        return $this;
    }

    /** @return Collection<int,Relation> */
    public function getRelationsFrom(): Collection
    {
        return $this->relationsFrom;
    }
    public function addRelationFrom(Relation $relation): static
    {
        if (!$this->relationsFrom->contains($relation)) {
            $this->relationsFrom->add($relation);
            $relation->setFromEntity($this);
        }
        return $this;
    }
    public function removeRelationFrom(Relation $relation): static
    {
        if ($this->relationsFrom->removeElement($relation)) {
            if ($relation->getFromEntity() === $this) {
                $relation->setFromEntity(null);
            }
        }
        return $this;
    }

    /** @return Collection<int,Relation> */
    public function getRelationsTo(): Collection
    {
        return $this->relationsTo;
    }
    public function addRelationTo(Relation $relation): static
    {
        if (!$this->relationsTo->contains($relation)) {
            $this->relationsTo->add($relation);
            $relation->setToEntity($this);
        }
        return $this;
    }
    public function removeRelationTo(Relation $relation): static
    {
        if ($this->relationsTo->removeElement($relation)) {
            if ($relation->getToEntity() === $this) {
                $relation->setToEntity(null);
            }
        }
        return $this;
    }
}
