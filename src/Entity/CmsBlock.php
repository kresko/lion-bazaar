<?php

namespace App\Entity;

use App\Repository\CmsBlockRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CmsBlockRepository::class)]
class CmsBlock
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    private ?CmsSlot $fk_cms_slot = null;

    #[ORM\Column(length: 255)]
    private string $key;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column]
    private \DateTimeImmutable $created_at;

    #[ORM\Column(type: "datetime")]
    private \DateTime $updated_at;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFkCmsSlot(): ?CmsSlot
    {
        return $this->fk_cms_slot;
    }

    public function setFkCmsSlot(?CmsSlot $fk_cms_slot): static
    {
        $this->fk_cms_slot = $fk_cms_slot;

        return $this;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function setKey(string $key): static
    {
        $this->key = $key;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): \DateTime
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTime $updated_at): static
    {
        $this->updated_at = $updated_at;

        return $this;
    }
}
