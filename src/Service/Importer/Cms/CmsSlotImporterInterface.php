<?php

namespace App\Service\Importer\Cms;

interface CmsSlotImporterInterface
{
    /**
     * @param array<string mixed> $data
     *
     * @return array<string, mixed>
     */
    public function importCmsSlot(array $data): array;
}
