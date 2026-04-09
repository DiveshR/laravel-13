<?php

namespace App\Services\Admin;

use App\Services\ProductService;
use App\Services\SearchCacheService;
use App\Services\UserService;

class AdminListingService
{
    public function __construct(
        private readonly UserService $userService,
        private readonly ProductService $productService,
        private readonly SearchCacheService $searchCacheService,
    ) {
    }

    public function usersRowsHtml(string $query, int $page, int $perPage, bool $useCache = true): string
    {
        $query = trim($query);

        $compute = function () use ($query, $page, $perPage): string {
            $users = $this->userService->listUsers($query ?: null, $perPage, $page);

            return view('admin.users.partials.rows', compact('users'))->render();
        };

        if (! $useCache || ! $this->searchCacheService->enabled()) {
            return $compute();
        }

        if ($query !== '' && ! $this->searchCacheService->shouldCacheQuery($query)) {
            return $compute();
        }

        $key = $this->searchCacheService->adminUsersRowsKey($query, $page, $perPage);
        $cached = $this->searchCacheService->remember($key, ['search', 'search:users'], $compute);

        return $cached['value'];
    }

    public function productsRowsHtml(string $query, int $page, int $perPage, bool $useCache = true): string
    {
        $query = trim($query);

        $compute = function () use ($query, $page, $perPage): string {
            $products = $this->productService->listProducts($query ?: null, $perPage, $page);

            return view('admin.products.partials.rows', compact('products'))->render();
        };

        if (! $useCache || ! $this->searchCacheService->enabled()) {
            return $compute();
        }

        if ($query !== '' && ! $this->searchCacheService->shouldCacheQuery($query)) {
            return $compute();
        }

        $key = $this->searchCacheService->adminProductsRowsKey($query, $page, $perPage);
        $cached = $this->searchCacheService->remember($key, ['search', 'search:products'], $compute);

        return $cached['value'];
    }
}

