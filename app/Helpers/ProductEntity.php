<?php

namespace App\Helpers;

class ProductEntity
{
    public function __construct(
        public string $sellableId,
        public string $sku,
        public string $title,
        public string $imagePath
    ) {}
}
