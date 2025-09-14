<?php

namespace App\Validator;

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
        $errors = [];

        foreach ($data[static::CATEGORIES] as $categoryKey => $categoryData) {
            // remove category data from array if category data is invalid
            if (!isset($categoryData['node_order']) || !is_int($categoryData['node_order'])) {
                $data[static::ERRORS][] = [
                    static::CATEGORY_KEY => $categoryData['category_key'],
                    static::MESSAGE => 'node_order is required and must be an integer.'
                ];

                unset($data[static::CATEGORIES][$categoryKey]);
            }

            if (!isset($categoryData['category_key']) || !is_string($categoryData['category_key']) || empty($categoryData['category_key'])) {
                $errors[] = 'category_key is required and must be a non-empty string.';
            }

            if (isset($categoryData['parent_category_key']) && !is_string($categoryData['parent_category_key'])) {
                $errors[] = 'parent_category_key must be a string if provided.';
            }

            if (!isset($categoryData['name']) || !is_string($categoryData['name']) || empty($categoryData['name'])) {
                $errors[] = 'name is required and must be a non-empty string.';
            }

            if (!isset($categoryData['is_root']) || !is_bool($categoryData['is_root'])) {
                $errors[] = 'is_root is required and must be a boolean.';
            }

            // remove category data, and append it to data 
        }
        

        return $data;
    }
}