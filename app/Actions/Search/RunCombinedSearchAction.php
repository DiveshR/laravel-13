<?php

namespace App\Actions\Search;

use App\Repositories\ProductRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\Collection;

class RunCombinedSearchAction
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly ProductRepository $productRepository
    ) {
    }

    public function execute(string $query): Collection
    {
        $users = $this->userRepository->scoutSearch($query, 50)->keyBy('id');
        $products = $this->productRepository->scoutSearch($query, 50);
        $productsByUsers = $this->productRepository->forUserIds($users->keys()->all())->groupBy('user_id');

        $rowsFromProducts = $products->toBase()->map(function ($product) use ($users) {
            return [
                'user_name' => $users[$product->user_id]->name ?? 'Unknown User',
                'product_name' => $product->name,
            ];
        });

        $rowsFromUserProducts = $productsByUsers
            ->map(function ($userProducts, $userId) use ($users) {
                return $userProducts->map(fn ($product) => [
                    'user_name' => $users[$userId]->name ?? 'Unknown User',
                    'product_name' => $product->name,
                ]);
            })
            ->flatten(1);

        return $rowsFromProducts
            ->merge($rowsFromUserProducts)
            ->unique(fn ($row) => $row['user_name'].'|'.$row['product_name'])
            ->values();
    }
}
