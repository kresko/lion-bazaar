<?php

namespace App\Command;

use App\Entity\Category;
use App\Entity\CategoryTree;
use App\Entity\Url;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:build-category-tree',
    description: 'Builds JSON tree from categories and saves into category_tree table'
)]
class BuildCategoryTreeCommand extends Command
{
    public function __construct(private EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $categoryRepository = $this->em->getRepository(Category::class);
        $categories = $categoryRepository->findAll();

        $urlRepository = $this->em->getRepository(Url::class);

        // Index categories by category_key for easy lookup
        $categoriesByKey = [];
        foreach ($categories as $category) {
            $url = $urlRepository->findOneBy(['category' => $category]);

            $categoriesByKey[$category->getCategoryKey()] = [
                'id_category' => $category->getId(),
                'node_order' => $category->getNodeOrder(),
                'category_key' => $category->getCategoryKey(),
                'parent_category_key' => $category->getParentCategoryKey(),
                'name' => $category->getName(),
                'url' => $url ? $url->getUrl() : null,
                'is_root' => $category->isRoot(),
                'children' => [],
            ];
        }

        // Attach children to parents
        $tree = [];
        foreach ($categoriesByKey as $key => &$category) {
            if ($category['parent_category_key'] && isset($categoriesByKey[$category['parent_category_key']])) {
                $categoriesByKey[$category['parent_category_key']]['children'][] = &$category;
            } elseif ($category['is_root']) {
                $tree[] = &$category;
            }
        }

        // Sort children by node_order
        $sortChildren = function (&$nodes) use (&$sortChildren) {
            usort($nodes, fn($a, $b) => $a['node_order'] <=> $b['node_order']);
            foreach ($nodes as &$node) {
                if (!empty($node['children'])) {
                    $sortChildren($node['children']);
                }
            }
        };

        $sortChildren($tree);

        $categoryTree = $this->em->getRepository(CategoryTree::class)->findOneBy([]);
        if (!$categoryTree) {
            $categoryTree = new CategoryTree();
            $categoryTree->setCreatedAtValue(new \DateTimeImmutable());
        }
        
        $categoryTree->setTreeJson($tree);
        $categoryTree->setUpdatedAtValue(new \DateTime());

        $this->em->persist($categoryTree);
        $this->em->flush();

        $output->writeln('<info>Category tree built and saved successfully!</info>');
        return Command::SUCCESS;
    }
}
