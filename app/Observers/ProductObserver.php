<?php

namespace App\Observers;

use App\Models\Product;
use App\Services\SearchCacheService;

class ProductObserver
{
    public function created(Product $product): void
    {
        app(SearchCacheService::class)->bumpProductsVersion();
    }

    public function updated(Product $product): void
    {
        app(SearchCacheService::class)->bumpProductsVersion();
    }

    public function deleted(Product $product): void
    {
        app(SearchCacheService::class)->bumpProductsVersion();
    }
}
