<?php

namespace App\Entity;

use App\Repository\CmsContentItemRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CmsContentItemRepository::class)]
class CmsContentItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    private ?CmsBlock $fk_cms_block = null;

    #[ORM\Column(length: 255)]
    private string $key;

    #[ORM\Column(length: 255)]
    private string $name;

    /**
     * @var array<string, mixed>|null
     */
    #[ORM\Column(nullable: true)]
    private ?array $data = null;

    #[ORM\Column]
    private \DateTimeImmutable $created_at;

    #[ORM\Column(type: "datetime")]
    private \DateTime $updated_at;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFkCmsBlock(): ?CmsBlock
    {
        return $this->fk_cms_block;
    }

    public function setFkCmsBlock(?CmsBlock $fk_cms_block): static
    {
        $this->fk_cms_block = $fk_cms_block;

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

    /**
     * @return array<string, mixed>|null
     */
    public function getData(): ?array
    {
        return $this->data;
    }

    /**
     * @param array<string, mixed>|null $data
     *
     * @return static
     */
    public function setData(?array $data): static
    {
        $this->data = $data;

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
