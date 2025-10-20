<?php

namespace App\Command;

use App\Entity\CmsBlock;
use App\Entity\CmsContentItem;
use App\Entity\CmsSlot;
use App\Entity\CmsStorage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:build-cms',
    description: 'Builds JSON cms from slots, block, content items and saves into cms_storage table'
)]
class BuildCmsStructureCommand extends Command
{
    /**
     * @param EntityManagerInterface $em
     */
    public function __construct(
        protected EntityManagerInterface $em
    )
    {
        parent::__construct();
    }

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->addArgument('slot', InputArgument::REQUIRED, 'The slot to build CMS structure for');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * 
     * @return integer
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $slotArgument = $input->getArgument('slot');

        $cmsSlotRepostory = $this->em->getRepository(CmsSlot::class);
        $cmsBlockRepository = $this->em->getRepository(CmsBlock::class);
        $cmsContentItemRepository = $this->em->getRepository(CmsContentItem::class);
        $cmsStorageRepository = $this->em->getRepository(CmsStorage::class);        

        $slot = $cmsSlotRepostory->findOneBy(['key' => $slotArgument]);

        if (!$slot) {
            $output->writeln('Slot not found.');

            return Command::FAILURE;
        }

        // Build tree root node (slot)
        $tree = [
            'id' => $slot->getId(),
            'type' => 'slot',
            'children' => [],
        ];
        
        $blocks = $cmsBlockRepository->findAll($slot->getId());

        foreach ($blocks as $block) {
            if (!$block) {
                continue;
            }

            $blockNode = [
                'id' => $block->getId(),
                'type' => 'block',
                'identifier' => $this->tryGetLabel($block),
                'children' => [],
            ];

            $contentItems = $cmsContentItemRepository->findAll($block->getId());

            foreach ($contentItems as $contentItem) {
                if (!$contentItem) {
                    continue;
                }

                $contentNode = [
                    'id' => $contentItem->getId(),
                    'type' => 'content_item',
                    'label' => $this->tryGetLabel($contentItem),
                    'data' => $this->tryGetData($contentItem),
                ];

                $blockNode['children'][] = $contentNode;
            }

            $tree['children'][] = $blockNode;
        }

        $output->writeln(json_encode($tree, JSON_PRETTY_PRINT));

        $cmsStorage = $cmsStorageRepository->findOneBy(['key' => $slot->getKey()]);

        if (!$cmsStorage) {
            $cmsStorage = new CmsStorage();
            $cmsStorage->setKey($slot->getKey());
            $cmsStorage->setData($tree);
            $cmsStorage->setCreatedAt(new \DateTimeImmutable());
            $cmsStorage->setUpdatedAt(new \DateTime());
        } else {
            $cmsStorage->setKey($slot->getKey());
            $cmsStorage->setData($tree);
            $cmsStorage->setUpdatedAt(new \DateTime());
        }

        $this->em->persist($cmsStorage);
        $this->em->flush();
        
        $output->writeln('CMS structure built successfully.');
        return Command::SUCCESS;
    }

    /**
     * @param object $entity
     * 
     * @return string|null
     */
    private function tryGetLabel(object $entity): ?string
    {
        foreach (['getName', 'getTitle', 'getIdentifier'] as $method) {
            if (method_exists($entity, $method)) {
                return (string) $entity->$method();
            }
        }

        return null;
    }

    /**
     * @param object $entity
     * 
     * @return mixed|null
     */
    private function tryGetData(object $entity): mixed
    {
        if (method_exists($entity, 'getData')) {
            return $entity->getData();
        }

        return null;
    }
}