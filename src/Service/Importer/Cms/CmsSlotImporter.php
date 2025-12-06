<?php

namespace App\Service\Importer\Cms;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\CmsSlot;

class CmsSlotImporter implements CmsSlotImporterInterface
{
    /**
     * @var string
     */
    public const SLOTS = 'slots';

    public function __construct(
        private EntityManagerInterface $em
    ) {
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    public function importCmsSlot(array $data): array
    {
        $cmsSlotRepository = $this->em->getRepository(CmsSlot::class);
        $created = [];
        $updated = [];

        foreach ($data[self::SLOTS] as $cmsSlotData) {
            $cmsSlot = $cmsSlotRepository->findOneBy(['key' => $cmsSlotData['key']]);

            if (!$cmsSlot) {
                $cmsSlot = new CmsSlot();
                $cmsSlot->setCreatedAt(new \DateTimeImmutable());
                $created[] = 'CmsSlot: ' . $cmsSlotData['key'];
            } else {
                $updated[] = 'CmsSlot: ' . $cmsSlotData['key'];
            }

            $cmsSlot
                ->setKey($cmsSlotData['key'])
                ->setName($cmsSlotData['name'])
                ->setUpdatedAt(new \DateTime());

            $this->em->persist($cmsSlot);
        }

        $this->em->flush();

        return [
            'created' => $created,
            'updated' => $updated,
        ];
    }
}
