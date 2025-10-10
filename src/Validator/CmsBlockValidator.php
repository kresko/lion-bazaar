<?php

namespace App\Validator;

class CmsBlockValidator
{
    /**
     * @var string
     */
    public const BLOCKS = 'blocks';

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
     * Validates and sanitizes cms block data.
     *
     * @param array $data
     * 
     * @return array
     */
    public function validate(array $data): array
    {
        foreach ($data[static::BLOCKS] as $cmsBlockKey => $cmsBlockData) {
            if (!isset($cmsBlockData['key']) || !is_string($cmsBlockData['key']) || empty($cmsBlockData['key'])) {
                $data[static::ERRORS][] = [
                    static::KEY => $cmsBlockData['key'],
                    static::MESSAGE => 'key is required and must be a non-empty string.'
                ];

                unset($data[static::BLOCKS][$cmsBlockKey]);
            }

            if (!isset($cmsBlockData['name']) || !is_string($cmsBlockData['name']) || empty($cmsBlockData['name'])) {
                $data[static::ERRORS][] = [
                    static::KEY => $cmsBlockData['key'],
                    static::MESSAGE => 'name is required and must be a non-empty string.'
                ];

                unset($data[static::BLOCKS][$cmsBlockKey]);
            }
        }

        return $data;
    }
}