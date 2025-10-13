<?php

namespace App\Controller\Cms;

use App\Entity\CmsSlot;
use App\Validator\CmsSlotValidator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CmsSlotController extends AbstractController
{
    /**
     * @var string
     */
    public const SLOTS = 'slots';

    #[Route('/cms/slot', name: 'cms_slot_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em, CmsSlotValidator $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!$data) {
            return $this->json(['error' => 'Invalid JSON'], 400);
        }

            $data = $validator->validate($data);
    
            $records = $this->importCmsSlot($em, $data);

        return $this->json([
            'status' => 'Category created',
            'created' => $records['created'],
            'updated' => $records['updated'],
            'errors' => $data[CmsSlotValidator::ERRORS] ?? []
        ]);
    }


    #[Route('/cms/slot/{key}', name: 'cms_slot_delete', methods: ['DELETE'])]
    public function delete(string $key, EntityManagerInterface $em): JsonResponse
    {
        $cmsSlotRepository = $em->getRepository(CmsSlot::class);
        $cmsSlot = $cmsSlotRepository->findOneBy(['key' => $key]);

        if (!$cmsSlot) {
            return $this->json(['error' => 'Cms slot not found'], 404);
        }

        $em->remove($cmsSlot);
        $em->flush();

        return $this->json([
            'status' => 'Cms slot deleted',
            'key' => $key,
        ]);
    }

    protected function importCmsSlot(EntityManagerInterface $em, array $data): array
    {
        $cmsSlotRepository = $em->getRepository(CmsSlot::class);
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

            $em->persist($cmsSlot);
        }

        $em->flush();

        return [
            'created' => $created,
            'updated' => $updated,
        ];
    }
}
