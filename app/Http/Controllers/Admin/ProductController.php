<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminListingService;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductService $productService,
        private readonly AdminListingService $adminListingService,
    )
    {
    }

    public function index(Request $request): View|Response
    {
        $query = $request->string('q')->toString();

        if ($request->ajax()) {
            $page = max(1, (int) $request->integer('page', 1));
            $perPage = 20;
            $useCache = ! $request->boolean('bypass_cache');

            $html = $this->adminListingService->productsRowsHtml($query, $page, $perPage, $useCache);

            return response($html);
        }

        $products = $this->productService->listProducts($query ?: null);
        return view('admin.products.index', compact('products', 'query'));
    }
}
