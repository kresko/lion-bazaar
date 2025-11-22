<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "category_tree")]
#[ORM\HasLifecycleCallbacks]
class CategoryTree
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    /** @var array<int, mixed> */
    #[ORM\Column(name: "tree_json", type: "json")]
    private array $treeJson = [];

    #[ORM\Column(type: "datetime_immutable", nullable: true)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: "datetime", nullable: true)]
    private ?\DateTime $updatedAt = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return array<int, mixed>
     */
    public function getTreeJson(): array
    {
        return $this->treeJson;
    }

    /**
     * @param array<int, mixed> $treeJson
     *
     * @return self
     */
    public function setTreeJson(array $treeJson): self
    {
        $this->treeJson = $treeJson;

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
