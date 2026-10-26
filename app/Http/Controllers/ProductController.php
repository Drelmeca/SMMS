<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

    public function inventory()
    {
        $this->ensureAdmin();

        return Inertia::render('Inventory', [
            'products' => Product::orderBy('name')->get([
                'id',
                'name',
                'category',
                'price',
                'stock_quantity',
                'is_active',
            ]),
        ]);
    }

    public function store(Request $request)
    {
        $this->ensureAdmin();
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'category' => 'nullable|string|max:80',
            'price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0|max:4294967295',
        ]);

        Product::create($data + ['is_active' => true]);

        return redirect()->route('products');
    }

    public function update(Request $request, Product $product)
    {
        $this->ensureAdmin();
        $data = $request->validate([
            'name' => 'sometimes|required|string|max:120',
            'category' => 'sometimes|nullable|string|max:80',
            'price' => 'sometimes|required|numeric|min:0',
            'stock_quantity' => 'sometimes|required|integer|min:0|max:4294967295',
            'is_active' => 'boolean',
        ]);

        DB::transaction(function () use ($product, $data) {
            $lockedProduct = Product::whereKey($product->getKey())
                ->lockForUpdate()
                ->firstOrFail();
            $lockedProduct->update($data);
        });

        return redirect()->route('products');
    }
}
