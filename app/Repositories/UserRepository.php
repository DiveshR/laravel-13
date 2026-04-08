<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class UserRepository
{
    public function paginate(int $perPage = 20, ?string $query = null): LengthAwarePaginator
    {
        if ($query) {
            return User::search($query)
                ->query(fn ($builder) => $builder->select(['id', 'name', 'email', 'role', 'created_at']))
                ->paginate($perPage);
        }

        return User::query()
            ->select(['id', 'name', 'email', 'role', 'created_at'])
            ->latest('id')
            ->paginate($perPage);
    }

    public function scoutSearch(string $query, int $limit = 20): Collection
    {
        return User::search($query)
            ->query(fn ($builder) => $builder->select(['id', 'name']))
            ->take($limit)
            ->get();
    }

    public function likeSearch(string $query, int $limit = 20): Collection
    {
        return User::query()
            ->select(['id', 'name'])
            ->where('name', 'like', "%{$query}%")
            ->limit($limit)
            ->get();
    }
}
