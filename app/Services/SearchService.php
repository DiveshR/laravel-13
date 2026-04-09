<?php

namespace App\Services;

use App\Actions\Search\RunCombinedSearchAction;
use Illuminate\Support\Collection;

class SearchService
{
    public function __construct(
        private readonly RunCombinedSearchAction $runCombinedSearchAction,
        private readonly SearchCacheService $searchCacheService,
    ) {
    }

    public function combinedSearch(?string $query): Collection
    {
        if (! $query) {
            return collect();
        }

        $query = trim($query);

        if (! $this->searchCacheService->enabled() || ! $this->searchCacheService->shouldCacheQuery($query)) {
            return $this->runCombinedSearchAction->execute($query);
        }

        $key = $this->searchCacheService->combinedKey($query);
        $cached = $this->searchCacheService->remember($key, ['search', 'search:combined'], function () use ($query): array {
            return $this->runCombinedSearchAction->execute($query)->values()->all();
        });

        return collect($cached['value']);
    }
}
