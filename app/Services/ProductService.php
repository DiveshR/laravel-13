<?php

namespace App\Services;

use App\Actions\Product\SearchProductsAction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProductService
{
    public function __construct(private readonly SearchProductsAction $searchProductsAction)
    {
    }

    public function listProducts(?string $query, int $perPage = 20, ?int $page = null): LengthAwarePaginator
    {
        return $this->searchProductsAction->execute($query, $perPage, $page);
    }
}
