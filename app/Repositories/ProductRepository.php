<?php

namespace App\Repositories;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as BaseCollection;

class ProductRepository
{
    public function paginate(int $perPage = 20, ?string $query = null, ?int $page = null): LengthAwarePaginator
    {
        if ($query) {
            return Product::search($query)
                ->query(fn ($builder) => $builder
                    ->select(['id', 'name', 'description', 'user_id', 'created_at'])
                    ->with(['user:id,name'])
                )
                ->paginate($perPage, 'page', $page);
        }

        return Product::query()
            ->select(['id', 'name', 'description', 'user_id', 'created_at'])
            ->with(['user:id,name'])
            ->latest('id')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function scoutSearch(string $query, int $limit = 50): Collection
    {
        return Product::search($query)
            ->query(fn ($builder) => $builder->select(['id', 'name', 'user_id']))
            ->take($limit)
            ->get();
    }

    public function forUserIds(array $userIds): BaseCollection
    {
        return Product::query()
            ->select(['id', 'name', 'user_id'])
            ->whereIn('user_id', $userIds)
            ->orderByDesc('id')
            ->limit(100)
            ->get();
    }
}
