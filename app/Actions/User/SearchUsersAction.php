<?php

namespace App\Actions\User;

use App\Repositories\UserRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SearchUsersAction
{
    public function __construct(private readonly UserRepository $userRepository)
    {
    }

    public function execute(?string $query, int $perPage = 20): LengthAwarePaginator
    {
        return $this->userRepository->paginate($perPage, $query);
    }
}
