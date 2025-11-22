<?php

namespace App\Entity;

use App\Repository\CategoryRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Category
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $node_order = null;

    #[ORM\Column(length: 255)]
    private ?string $category_key = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $parent_category_key = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column]
    private ?bool $is_root = null;

    #[ORM\Column(type: "datetime_immutable")]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: "datetime")]
    private ?\DateTime $updatedAt = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNodeOrder(): ?int
    {
        return $this->node_order;
    }

    public function setNodeOrder(int $node_order): static
    {
        $this->node_order = $node_order;

        return $this;
    }

    public function getCategoryKey(): ?string
    {
        return $this->category_key;
    }

    public function setCategoryKey(string $category_key): static
    {
        $this->category_key = $category_key;

        return $this;
    }

    public function getParentCategoryKey(): ?string
    {
        return $this->parent_category_key;
    }

    public function setParentCategoryKey(?string $parent_category_key): static
    {
        $this->parent_category_key = $parent_category_key;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function isRoot(): ?bool
    {
        return $this->is_root;
    }

    public function setIsRoot(bool $is_root): static
    {
        $this->is_root = $is_root;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTime();
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTime();
    }
}
