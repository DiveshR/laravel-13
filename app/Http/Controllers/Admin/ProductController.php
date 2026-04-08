<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(private readonly ProductService $productService)
    {
    }

    public function index(Request $request): View
    {
        $query = $request->string('q')->toString();
        $products = $this->productService->listProducts($query ?: null);

        if ($request->ajax()) {
            return view('admin.products.partials.rows', compact('products'));
        }

        return view('admin.products.index', compact('products', 'query'));
    }
}
