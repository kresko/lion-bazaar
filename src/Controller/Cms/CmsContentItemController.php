<?php

namespace App\Controller\Cms;

use App\Entity\CmsBlock;
use App\Entity\CmsContentItem;
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
    public function create(Request $request, EntityManagerInterface $em, CmsContentItemValidator $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!$data) {
            return $this->json(['error' => 'Invalid JSON'], 400);
        }

        $data = $validator->validate($data);

        $records = $this->importCmsContentItem($em, $data);

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

    protected function importCmsContentItem(EntityManagerInterface $em, array $data): array
    {
        $cmsContentItemRepository = $em->getRepository(CmsContentItem::class);
        $cmsBlockRepository = $em->getRepository(CmsBlock::class);

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

            $em->persist($cmsContentItem);
            $em->persist($cmsBlock);
        }

        $em->flush();

        return [
            'created' => $created,
            'updated' => $updated,
        ];
    }
}
