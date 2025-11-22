<?php

namespace App\Service\Validator\Cms;

class CmsSlotValidator
{
    /**
     * @var string
     */
    public const SLOTS = 'slots';

    /**
     * @var string
     */
    public const KEY = 'key';

    /**
     * @var string
     */
    public const NAME = 'name';

    /**
     * @var string
     */
    public const MESSAGE = 'message';

    /**
     * @var string
     */
    public const ERRORS = 'errors';

    /**
     * Validates and sanitizes cms slot data.
     *
     * @param array $data
     * 
     * @return array
     */
    public function validate(array $data): array
    {
        foreach ($data[static::SLOTS] as $cmsSlotKey => $cmsSlotData) {
            if (!isset($cmsSlotData['key']) || !is_string($cmsSlotData['key']) || empty($cmsSlotData['key'])) {
                $data[static::ERRORS][] = [
                    static::KEY => $cmsSlotData['key'],
                    static::MESSAGE => 'key is required and must be a non-empty string.'
                ];

                unset($data[static::SLOTS][$cmsSlotKey]);
            }

            if (!isset($cmsSlotData['name']) || !is_string($cmsSlotData['name']) || empty($cmsSlotData['name'])) {
                $data[static::ERRORS][] = [
                    static::KEY => $cmsSlotData['key'],
                    static::MESSAGE => 'name is required and must be a non-empty string.'
                ];

                unset($data[static::SLOTS][$cmsSlotKey]);
            }
        }

        return $data;
    }
}