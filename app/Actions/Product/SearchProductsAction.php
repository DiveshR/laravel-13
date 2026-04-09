<?php

namespace App\Actions\Product;

use App\Repositories\ProductRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SearchProductsAction
{
    public function __construct(private readonly ProductRepository $productRepository)
    {
    }

    public function execute(?string $query, int $perPage = 20, ?int $page = null): LengthAwarePaginator
    {
        return $this->productRepository->paginate($perPage, $query, $page);
    }
}
