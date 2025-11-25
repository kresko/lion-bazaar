<?php

namespace App\Service\Importer\Cms;

use App\Entity\CmsBlock;
use App\Entity\CmsSlot;
use Doctrine\ORM\EntityManagerInterface;

class CmsBlockImporter implements CmsBlockImporterInterface
{
    /**
     * @var string
     */
    public const BLOCKS = 'blocks';

    public function __construct(
        private EntityManagerInterface $em
    ) {
    }

    /**
     * @param array<string, mixed> $data
     * 
     * @return array<string, mixed>
     */
    public function importCmsBlock(array $data): array
    {
        $cmsBlockRepository = $this->em->getRepository(CmsBlock::class);
        $cmsSlotRepository = $this->em->getRepository(CmsSlot::class);

        $created = [];
        $updated = [];

        foreach ($data[self::BLOCKS] as $cmsBlockData) {
            $cmsBlock = $cmsBlockRepository->findOneBy(['key' => $cmsBlockData['key']]);
            $cmsSlot = $cmsSlotRepository->findOneBy(['key' => $cmsBlockData['parent_key']]);

            if (!$cmsBlock) {
                $cmsBlock = new CmsBlock();
                $cmsBlock->setCreatedAt(new \DateTimeImmutable());
                $created[] = 'CmsBlock: ' . $cmsBlockData['key'];
            } else {
                $updated[] = 'CmsBlock: ' . $cmsBlockData['key'];
            }

            if ($cmsSlot) {
                $cmsBlock->setFkCmsSlot($cmsSlot);
            }

            $cmsBlock
                ->setKey($cmsBlockData['key'])
                ->setName($cmsBlockData['name'])
                ->setUpdatedAt(new \DateTime());

            $this->em->persist($cmsBlock);
            $this->em->persist($cmsSlot);
        }

        $this->em->flush();

        return [
            'created' => $created,
            'updated' => $updated,
        ];
    }
}
