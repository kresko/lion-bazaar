<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "navigation_tree")]
#[ORM\HasLifecycleCallbacks]
class NavigationTree
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    /**
     * @var array<int, array<string, mixed>>
     */
    #[ORM\Column(name: "tree_json", type: "json")]
    private ?array $treeJson = [];

    #[ORM\Column(type: "datetime_immutable")]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: "datetime")]
    private \DateTime $updatedAt;


    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getTreeJson(): array
    {
        return $this->treeJson;
    }

    /**
     * @param array<int, array<string, mixed>> $treeJson
     *
     * @return self
     */
    public function setTreeJson(array $treeJson): self
    {
        $this->treeJson = $treeJson;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTime();
    }
}
