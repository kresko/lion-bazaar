<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "category_tree")]
class CategoryTree
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(name: "tree_json", type: "json")]
    private array $treeJson = [];

    public function getId(): ?int 
    { 
        return $this->id; 
    }

    public function getTreeJson(): array 
    { 
        return $this->treeJson; 
    }

    public function setTreeJson(array $treeJson): self { 
        $this->treeJson = $treeJson; return $this; 
    }
}
