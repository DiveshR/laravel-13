<?php

namespace App\Services;

use App\Actions\Search\RunCombinedSearchAction;
use Illuminate\Support\Collection;

class SearchService
{
    public function __construct(private readonly RunCombinedSearchAction $runCombinedSearchAction)
    {
    }

    public function combinedSearch(?string $query): Collection
    {
        if (! $query) {
            return collect();
        }

        return $this->runCombinedSearchAction->execute($query);
    }
}
