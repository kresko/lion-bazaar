<?php

namespace App\Service\Validator\Category;

class CategoryValidator
{
    /**
     * @var string
     */
    public const CATEGORIES = 'categories';

    /**
     * @var string
     */
    public const CATEGORY_KEY = 'category_key';

    /**
     * @var string
     */
    public const MESSAGE = 'message';

    /**
     * @var string
     */
    public const ERRORS = 'errors';

    /**
     * Validates and sanitizes category data.
     *
     * @param array $data
     *
     * @return array
     */
    public function validate(array $data): array
    {
        foreach ($data[static::CATEGORIES] as $categoryKey => $categoryData) {
            if (!isset($categoryData['node_order']) || !is_int($categoryData['node_order'])) {
                $data[static::ERRORS][] = [
                    static::CATEGORY_KEY => $categoryData['category_key'],
                    static::MESSAGE => 'node_order is required and must be an integer.'
                ];

                unset($data[static::CATEGORIES][$categoryKey]);
            }

            if (!isset($categoryData['category_key']) || !is_string($categoryData['category_key']) || empty($categoryData['category_key'])) {
                $data[static::ERRORS][] = [
                    static::CATEGORY_KEY => $categoryData['name'],
                    static::MESSAGE => 'category_key is required and must be a non-empty string.'
                ];

                unset($data[static::CATEGORIES][$categoryKey]);
            }

            if (!isset($categoryData['is_root']) || !is_bool($categoryData['is_root'])) {
                $data[static::ERRORS][] = [
                    static::CATEGORY_KEY => $categoryData['category_key'],
                    static::MESSAGE => 'is_root is required and must be a boolean.'
                ];

                unset($data[static::CATEGORIES][$categoryKey]);
            }

            if (!$categoryData['is_root'] && !isset($categoryData['parent_category_key']) && !is_string($categoryData['parent_category_key'])) {
                $data[static::ERRORS][] = [
                    static::CATEGORY_KEY => $categoryData['category_key'],
                    static::MESSAGE => 'parent_category_key must be a string if provided.'
                ];

                unset($data[static::CATEGORIES][$categoryKey]);
            }

            if (!isset($categoryData['name']) || !is_string($categoryData['name']) || empty($categoryData['name'])) {
                $data[static::ERRORS][] = [
                    static::CATEGORY_KEY => $categoryData['category_key'],
                    static::MESSAGE => 'name is required and must be a non-empty string.'
                ];

                unset($data[static::CATEGORIES][$categoryKey]);
            }
        }

        return $data;
    }
}
