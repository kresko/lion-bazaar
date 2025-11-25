<?php

namespace App\Service\Importer\Cms;

interface CmsContentItemImporterInterface
{
    /**
     * @param array<string, mixed> $data 
     *
     * @return array<string, mixed>
     */
    public function importCmsContentItem(array $data): array;
}
