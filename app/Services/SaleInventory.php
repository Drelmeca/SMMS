<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Sale;
use Illuminate\Validation\ValidationException;

class SaleInventory
{
    public function replaceItems(Sale $sale, array $items): string
    {
        $existingItems = $sale->items()->get();
        $oldQuantities = $existingItems->pluck('quantity', 'product_id');
        $oldItems = $existingItems->keyBy('product_id');
        $newItems = collect($items)->keyBy(fn (array $item) => (int) $item['product_id']);
        $productIds = $oldQuantities->keys()
            ->merge($newItems->keys())
            ->unique()
            ->sort()
            ->values();

        $products = Product::whereIn('id', $productIds)
            ->orderBy('id')
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        if ($products->count() !== $productIds->count()) {
            throw ValidationException::withMessages([
                'items' => 'One or more selected products are no longer available.',
            ]);
        }

        $totalCents = 0;
        foreach ($newItems as $productId => $item) {
            $product = $products->get($productId);
            $quantity = (int) $item['quantity'];
            $available = $product->stock_quantity + (int) $oldQuantities->get($productId, 0);

            if (! $product->is_active || $available < $quantity) {
                throw ValidationException::withMessages([
                    'items' => "Not enough stock available for {$product->name}.",
                ]);
            }

            $oldItem = $oldItems->get($productId);
            $unitPrice = $oldItem?->unit_price ?? $product->price;
            $totalCents += (int) round((float) $unitPrice * 100) * $quantity;
        }

        if ($totalCents > 9_999_999_999) {
            throw ValidationException::withMessages([
                'items' => 'The sale total exceeds the supported amount.',
            ]);
        }

        foreach ($products as $productId => $product) {
            $product->stock_quantity += (int) $oldQuantities->get($productId, 0);
            $product->stock_quantity -= (int) $newItems->get($productId, ['quantity' => 0])['quantity'];
            $product->save();
        }

        $sale->items()->delete();
        foreach ($newItems as $productId => $item) {
            $oldItem = $oldItems->get($productId);
            $sale->items()->create([
                'product_id' => $productId,
                'quantity' => $item['quantity'],
                'unit_price' => $oldItem?->unit_price ?? $products->get($productId)->price,
            ]);
        }

        return number_format($totalCents / 100, 2, '.', '');
    }

    public function restoreStock(Sale $sale): void
    {
        $this->adjustItemsStock($sale, restore: true);
    }

    public function deductStock(Sale $sale): void
    {
        $this->adjustItemsStock($sale, restore: false);
    }

    private function adjustItemsStock(Sale $sale, bool $restore): void
    {
        $items = $sale->items()->get();
        $products = Product::whereIn('id', $items->pluck('product_id'))
            ->orderBy('id')
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        foreach ($items as $item) {
            $product = $products->get($item->product_id);
            if (! $product) {
                throw ValidationException::withMessages([
                    'items' => 'A product on this sale no longer exists.',
                ]);
            }

            if (! $restore && $product->stock_quantity < $item->quantity) {
                throw ValidationException::withMessages([
                    'items' => "Not enough stock available for {$product->name} to restore this sale.",
                ]);
            }
        }

        foreach ($items as $item) {
            $product = $products->get($item->product_id);
            $product->stock_quantity += $restore ? $item->quantity : -$item->quantity;
            $product->save();
        }
    }
}
