<?php

namespace App\Entity;

use App\Repository\ProductCategoryRepository;
use Doctrine\ORM\Mapping as ORM;
use phpDocumentor\Reflection\Types\Nullable;

#[ORM\Entity(repositoryClass: ProductCategoryRepository::class)]
class ProductCategory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\ManyToOne()]
    #[ORM\JoinColumn(nullable: false)]
    private Product $fk_product;

    #[ORM\ManyToOne()]
    #[ORM\JoinColumn(nullable: false)]
    private Category $fk_category;

    #[ORM\Column]
    private \DateTimeImmutable $created_at;

    #[ORM\Column]
    private \DateTime $updated_at;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFkProduct(): Product
    {
        return $this->fk_product;
    }

    public function setFkProduct(Product $fk_product): static
    {

        $this->fk_product = $fk_product;

        return $this;
    }

    public function getFkCategory(): Category
    {
        return $this->fk_category;
    }

    public function setFkCategory(Category $fk_category): static
    {
        $this->fk_category = $fk_category;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTime $updated_at): static
    {
        $this->updated_at = $updated_at;

        return $this;
    }
}
