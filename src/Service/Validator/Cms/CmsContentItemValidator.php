<?php

namespace App\Service\Validator\Cms;

class CmsContentItemValidator
{
    /**
     * @var string
     */
    public const CONTENT_ITEM = 'content-item';

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
     * Validates and sanitizes cms content item data.
     *
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    public function validate(array $data): array
    {
        foreach ($data[static::CONTENT_ITEM] as $cmsContentItemKey => $cmsContentItemData) {
            if (!isset($cmsContentItemData['key']) || !is_string($cmsContentItemData['key']) || empty($cmsContentItemData['key'])) {
                $data[static::ERRORS][] = [
                    static::KEY => $cmsContentItemData['key'],
                    static::MESSAGE => 'key is required and must be a non-empty string.'
                ];

                unset($data[static::CONTENT_ITEM][$cmsContentItemKey]);
            }

            if (!isset($cmsContentItemData['name']) || !is_string($cmsContentItemData['name']) || empty($cmsContentItemData['name'])) {
                $data[static::ERRORS][] = [
                    static::KEY => $cmsContentItemData['key'],
                    static::MESSAGE => 'name is required and must be a non-empty string.'
                ];

                unset($data[static::CONTENT_ITEM][$cmsContentItemKey]);
            }
        }

        return $data;
    }
}
