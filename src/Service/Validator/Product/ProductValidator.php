<?php

namespace App\Service\Validator\Category;

class ProductValidator
{
    /**
     * @var string
     */
    public const PRODUCTS = 'products';

    /**
     * @var string
     */
    public const CATEGORY_KEY = 'category_key';

    /**
     * @var string
     */
    public const PRODUCT_KEY = 'product_key';

    /**
     * @var string
     */
    public const MESSAGE = 'message';

    /**
     * @var string
     */
    public const ERRORS = 'errors';

    /**
     * Validates and sanitizes product data.
     *
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    public function validate(array $data): array
    {
        foreach ($data[static::PRODUCTS] as $productKey => $productData) {
            if (!isset($productData['category_key']) || !is_string($productData['category_key']) || empty($productData['category_key'])) {
                $data[static::ERRORS][] = [
                    static::PRODUCT_KEY => $productData['name'],
                    static::MESSAGE => 'category_key is required and must be a non-empty string.'
                ];

                unset($data[static::PRODUCTS][$productKey]);
            }

            if (!isset($productData['product_key']) || !is_string($productData['product_key'])) {
                $data[static::ERRORS][] = [
                    static::PRODUCT_KEY => $productData['product_key'],
                    static::MESSAGE => 'product_key is required and must be a non-empty string.'
                ];

                unset($data[static::PRODUCTS][$productKey]);
            }

            if (!isset($productData['sku']) || !is_string($productData['sku']) || empty($productData['sku'])) {
                $data[static::ERRORS][] = [
                    static::PRODUCT_KEY => $productData['category_key'],
                    static::MESSAGE => 'sku is required and must be a non-empty string.'
                ];

                unset($data[static::PRODUCTS][$productKey]);
            }

            if (!isset($productData['name']) || !is_string($productData['name']) || empty($productData['name'])) {
                $data[static::ERRORS][] = [
                    static::PRODUCT_KEY => $productData['category_key'],
                    static::MESSAGE => 'name is required and must be a non-empty string.'
                ];

                unset($data[static::PRODUCTS][$productKey]);
            }
        }

        return $data;
    }
}
