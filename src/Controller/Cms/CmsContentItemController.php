<?php

namespace App\Controller\Cms;

use App\Entity\CmsContentItem;
use App\Service\Importer\Cms\CmsContentItemImporterInterface;
use App\Service\Validator\Cms\CmsContentItemValidator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CmsContentItemController extends AbstractController
{
    /**
     * @var string
     */
    public const CONTENT_ITEM = 'content-item';

    #[Route('/cms/content-item', name: 'cms_content_item_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em, CmsContentItemValidator $validator, CmsContentItemImporterInterface $cmsContentItemImporter): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!$data) {
            return $this->json(['error' => 'Invalid JSON'], 400);
        }

        $data = $validator->validate($data);

        $records = $cmsContentItemImporter->importCmsContentItem($data);

        return $this->json([
            'status' => 'Cms content item created',
            'created' => $records['created'],
            'updated' => $records['updated'],
            'errors' => $data[CmsContentItemValidator::ERRORS] ?? []
        ]);
    }

    #[Route('/cms/content-item/{key}', name: 'cms_content_item_delete', methods: ['DELETE'])]
    public function delete(string $key, EntityManagerInterface $em): JsonResponse
    {
        $cmsContentItemRepository = $em->getRepository(CmsContentItem::class);
        $cmsContentItem = $cmsContentItemRepository->findOneBy(['key' => $key]);

        if (!$cmsContentItem) {
            return $this->json(['error' => 'Content item not found'], 404);
        }

        $em->remove($cmsContentItem);
        $em->flush();

        return $this->json([
            'status' => 'Cms content item deleted',
            'category_key' => $key,
        ]);
    }
}
