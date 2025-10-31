<?php

namespace App\Service\Category;

interface CategoryTreeBuilderInterface
{
    // dodaj kaj treba
    public function buildFilterTree(array $categories): array;
}