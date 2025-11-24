<?php

namespace App\Command;

use App\Entity\Category;
use App\Entity\Url;
use App\Entity\NavigationTree;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:build-navigation-tree',
    description: 'Builds JSON tree from categories and saves into category_tree table'
)]
class BuildNavigationTreeCommand extends Command
{
    /**
     * @var string
     */
    private const ROOT_CATEGORY = '0000';

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

            if ($category->getParentCategoryKey() === static::ROOT_CATEGORY || $category->isRoot()) {
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
        }

        // Attach children to parents
        $tree = [];
        foreach ($categoriesByKey as $key => &$category) {
            if ($category['parent_category_key'] === static::ROOT_CATEGORY && isset($categoriesByKey[$category['parent_category_key']])) {
                $categoriesByKey[$category['parent_category_key']]['children'][] = &$category;
            } elseif ($category['is_root']) {
                $tree[] = &$category;
            }
        }

        $navigationTree = $this->em->getRepository(NavigationTree::class)->findOneBy([]);
        if (!$navigationTree) {
            $navigationTree = new NavigationTree();
            $navigationTree->setCreatedAtValue(new \DateTimeImmutable());
        }

        $navigationTree->setTreeJson($tree);
        $navigationTree->setUpdatedAtValue(new \DateTime());

        $this->em->persist($navigationTree);
        $this->em->flush();

        $output->writeln('<info>Navigation tree built and saved successfully!</info>');
        return Command::SUCCESS;
    }
}
