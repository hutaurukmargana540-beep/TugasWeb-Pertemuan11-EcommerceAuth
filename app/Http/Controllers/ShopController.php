<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Katalog publik (tanpa login). */
class ShopController extends Controller
{
    public function index(Request $request): View
    {
        $products = Product::query()
            ->with('category')                       // eager loading: hindari N+1
            ->withAvg('reviews', 'rating')
            ->active()
            ->inStock()
            ->search($request->query('q'))
            ->when($request->query('kategori'), fn ($q, $slug) => $q->whereHas(
                'category', fn ($c) => $c->where('slug', $slug)
            ))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $categories = Category::withCount('products')->orderBy('name')->get();

        return view('shop.index', compact('products', 'categories'));
    }

    public function show(Product $product): View
    {
        abort_unless($product->is_active, 404);

        $product->load(['category', 'owner:id,name', 'reviews.user:id,name'])
            ->loadAvg('reviews', 'rating');

        return view('shop.show', compact('product'));
    }
}
