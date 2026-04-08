<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SearchService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CombinedSearchController extends Controller
{
    public function __construct(private readonly SearchService $searchService)
    {
    }

    public function index(Request $request): View
    {
        $query = $request->string('q')->toString();
        $results = $this->searchService->combinedSearch($query ?: null);

        return view('admin.search.index', compact('results', 'query'));
    }
}
