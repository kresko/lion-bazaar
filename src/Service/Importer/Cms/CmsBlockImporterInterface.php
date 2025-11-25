<?php

namespace App\Service\Importer\Cms;

interface CmsBlockImporterInterface
{
    /**
     * @param array<string, mixed> $data
     * 
     * @return array<string, mixed>
     */
    public function importCmsBlock(array $data): array;
}
