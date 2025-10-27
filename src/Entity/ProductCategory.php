<?php

namespace App\Entity;

use App\Repository\ProductCategoryRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductCategoryRepository::class)]
class ProductCategory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne()]
    private Product $fk_product;

    #[ORM\ManyToOne()]
    private Category $fk_category;

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
}
