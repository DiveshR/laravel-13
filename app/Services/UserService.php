<?php

namespace App\Services;

use App\Actions\User\SearchUsersAction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UserService
{
    public function __construct(private readonly SearchUsersAction $searchUsersAction)
    {
    }

    public function listUsers(?string $query, int $perPage = 20): LengthAwarePaginator
    {
        return $this->searchUsersAction->execute($query, $perPage);
    }
}
