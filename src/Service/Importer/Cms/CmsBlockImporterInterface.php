<?php

namespace App\Service\Importer\Cms;

interface CmsBlockImporterInterface
{
    public function importCmsBlock(array $data): array;
}
