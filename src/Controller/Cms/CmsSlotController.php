<?php

namespace App\Controller\Cms;

use App\Entity\CmsSlot;
use App\Service\Importer\Cms\CmsSlotImporterInterface;
use App\Service\Validator\Cms\CmsSlotValidator;
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
    public function create(Request $request, EntityManagerInterface $em, CmsSlotValidator $validator, CmsSlotImporterInterface $cmsSlotImporter): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!$data) {
            return $this->json(['error' => 'Invalid JSON'], 400);
        }

            $data = $validator->validate($data);

            $records = $cmsSlotImporter->importCmsSlot($data);

        return $this->json([
            'status' => 'Cms slot created',
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
}
