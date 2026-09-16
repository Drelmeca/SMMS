<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductController extends Controller
{
    private function ensureAdmin(): void
    {
        abort_unless((int) auth()->user()->role === 2, 403);
    }

    public function index()
    {
        $this->ensureAdmin();
        return Inertia::render('Products', [
            'products' => Product::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $this->ensureAdmin();
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'category' => 'nullable|string|max:80',
            'price' => 'required|numeric|min:0',
        ]);

        Product::create($data + ['is_active' => true]);

        return redirect()->route('products');
    }

    public function update(Request $request, Product $product)
    {
        $this->ensureAdmin();
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'category' => 'nullable|string|max:80',
            'price' => 'required|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $product->update($data);

        return redirect()->route('products');
    }
}
