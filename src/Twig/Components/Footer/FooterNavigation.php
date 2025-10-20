<?php

namespace App\Twig\Components\Footer;

use App\Repository\CmsStorageRepository;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class FooterNavigation
{
    public function __construct(
        protected CmsStorageRepository $cmsStorageRepository
    )
    {
    }

    public function getFooterCmsNavigation(): array
    {
        // napravi json
        $footerNavigationSlot = $this->cmsStorageRepository->findOneBy(['key' => 'footer_navigation_slot']);

        if (!$footerNavigationSlot) {
            return [];
        }

        $slotData = $footerNavigationSlot->getData();

        $navigation = $this->extractNavigationData($slotData);

        return $navigation;
    }

    private function extractNavigationData(array $slotData): array
    {
        $navigation = [];

        // Check if children exist in the slot
        if (!isset($slotData['children']) || !is_array($slotData['children'])) {
            return [];
        }

        // Iterate through blocks
        foreach ($slotData['children'] as $block) {
            if ($block['type'] !== 'block' || !isset($block['children'])) {
                continue;
            }

            // Iterate through content items within each block
            foreach ($block['children'] as $contentItem) {
                if ($contentItem['type'] !== 'content_item' || !isset($contentItem['data'])) {
                    continue;
                }

                // Transform the data format from [{name, url}] to [{label, url}]
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
