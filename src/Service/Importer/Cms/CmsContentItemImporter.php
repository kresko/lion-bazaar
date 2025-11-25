<?php

namespace App\Service\Importer\Cms;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\CmsContentItem;
use App\Entity\CmsBlock;

class CmsContentItemImporter implements CmsContentItemImporterInterface
{
    /**
     * @var string
     */
    public const CONTENT_ITEM = 'content-item';

    public function __construct(
        private EntityManagerInterface $em
    ) {
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    public function importCmsContentItem(array $data): array
    {
        $cmsContentItemRepository = $this->em->getRepository(CmsContentItem::class);
        $cmsBlockRepository = $this->em->getRepository(CmsBlock::class);

        $created = [];
        $updated = [];

        foreach ($data[self::CONTENT_ITEM] as $cmsContentItemData) {
            $cmsContentItem = $cmsContentItemRepository->findOneBy(['key' => $cmsContentItemData['key']]);
            $cmsBlock = $cmsBlockRepository->findOneBy(['key' => $cmsContentItemData['parent_key']]);

            if (!$cmsContentItem) {
                $cmsContentItem = new CmsContentItem();
                $cmsContentItem->setCreatedAt(new \DateTimeImmutable());
                $created[] = 'CmsContentItem: ' . $cmsContentItemData['key'];
            } else {
                $updated[] = 'CmsContentItem: ' . $cmsContentItemData['key'];
            }

            if ($cmsBlock) {
                $cmsContentItem->setFkCmsBlock($cmsBlock);
            }

            $cmsContentItem
                ->setKey($cmsContentItemData['key'])
                ->setName($cmsContentItemData['name'])
                ->setData($cmsContentItemData['data'])
                ->setUpdatedAt(new \DateTime());

            $this->em->persist($cmsContentItem);
            $this->em->persist($cmsBlock);
        }

        $this->em->flush();

        return [
            'created' => $created,
            'updated' => $updated,
        ];
    }
}
