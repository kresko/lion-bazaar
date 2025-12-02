<?php

namespace App\Controller\Cms;

use App\Entity\CmsBlock;
use App\Service\Importer\Cms\CmsBlockImporterInterface;
use App\Service\Validator\Cms\CmsBlockValidator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CmsBlockController extends AbstractController
{
    /**
     * @var string
     */
    public const BLOCKS = 'blocks';

    #[Route('/cms/block', name: 'cms_block_create', methods: ['POST'])]
    public function create(Request $request, CmsBlockValidator $validator, CmsBlockImporterInterface $cmsBlockImporter): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!$data) {
            return $this->json(['error' => 'Invalid JSON'], 400);
        }

        $data = $validator->validate($data);

        $records = $cmsBlockImporter->importCmsBlock($data);

        return $this->json([
            'status' => 'Cms block created',
            'created' => $records['created'],
            'updated' => $records['updated'],
            'errors' => $data[CmsBlockValidator::ERRORS] ?? []
        ]);
    }

    #[Route('/cms/block/{key}', name: 'cms_block_delete', methods: ['DELETE'])]
    public function delete(string $key, EntityManagerInterface $em): JsonResponse
    {
        $cmsBlockRepository = $em->getRepository(CmsBlock::class);
        $cmsBlock = $cmsBlockRepository->findOneBy(['key' => $key]);

        if (!$cmsBlock) {
            return $this->json(['error' => 'Cms block not found'], 404);
        }

        $em->remove($cmsBlock);
        $em->flush();

        return $this->json([
            'status' => 'Cms block deleted',
            'key' => $key,
        ]);
    }
}
