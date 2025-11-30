<?php

namespace App\Twig\Components\Footer;

use App\Repository\CmsStorageRepository;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class FooterNavigation
{
    /**
     * @param CmsStorageRepository $cmsStorageRepository
     */
    public function __construct(
        protected CmsStorageRepository $cmsStorageRepository
    ) {
    }

    /**
     * @return array
     */
    public function getFooterCmsNavigation(): array
    {
        $footerNavigationSlot = $this->cmsStorageRepository->findOneBy(['key' => 'footer_navigation_slot']);

        if (!$footerNavigationSlot) {
            return [];
        }

        $slotData = $footerNavigationSlot->getData();

        $navigation = $this->extractNavigationData($slotData);

        return $navigation;
    }

    /**
     * @param array $slotData
     *
     * @return array
     */
    private function extractNavigationData(array $slotData): array
    {
        $navigation = [];

        if (!isset($slotData['children']) || !is_array($slotData['children'])) {
            return [];
        }

        foreach ($slotData['children'] as $block) {
            if ($block['type'] !== 'block' || !isset($block['children'])) {
                continue;
            }

            foreach ($block['children'] as $contentItem) {
                if ($contentItem['type'] !== 'content_item' || !isset($contentItem['data'])) {
                    continue;
                }

                $navigationColumn = array_map(function ($item) {
                    return [
                        'label' => $item['name'] ?? '',
                        'url' => $item['url'] ?? '#'
                    ];
                }, $contentItem['data']);

                $navigation[] = $navigationColumn;
            }
        }

        return $navigation;
    }
}
