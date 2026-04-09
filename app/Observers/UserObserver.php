<?php

namespace App\Observers;

use App\Models\User;
use App\Services\SearchCacheService;

class UserObserver
{
    public function created(User $user): void
    {
        app(SearchCacheService::class)->bumpUsersVersion();
    }

    public function updated(User $user): void
    {
        app(SearchCacheService::class)->bumpUsersVersion();
    }

    public function deleted(User $user): void
    {
        app(SearchCacheService::class)->bumpUsersVersion();
    }
}
