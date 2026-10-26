<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Services\SaleInventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $isAdmin = (int) auth()->user()->role === 2;
        $search = $isAdmin ? trim((string) $request->query('search', '')) : '';
        $salesQuery = Sale::whereNull('archived_at')
            ->with(['submittedBy:id,name', 'items.product:id,name'])
            ->orderByDesc('sale_date')
            ->orderByDesc('id');

        if ($search !== '') {
            $salesQuery->where(function ($query) use ($search) {
                $query->where('sale_date', 'like', "%{$search}%")
                    ->orWhere('amount', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%")
                    ->orWhereHas('submittedBy', fn ($userQuery) => $userQuery->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('items.product', fn ($productQuery) => $productQuery->where('name', 'like', "%{$search}%"));

                if (ctype_digit($search)) {
                    $query->orWhere('id', (int) $search);
                }
            });
        }

        $sales = $salesQuery->paginate(15)->withQueryString();

        return Inertia::render('Sales', [
            'sales' => $sales,
            'search' => $search,
            'products' => $isAdmin ? [] : Product::where('is_active', true)
                ->where('stock_quantity', '>', 0)
                ->orderBy('name')
                ->get(['id', 'name', 'price', 'stock_quantity']),
            'canManage' => $isAdmin,
            'canEditSales' => in_array((int) auth()->user()->role, [0, 2], true),
            'canDeleteSales' => $isAdmin,
        ]);
    }

    public function create()
    {
        abort_unless((int) auth()->user()->status === 1, 403);

        return Inertia::render('SalesCreate', [
            'products' => Product::where('is_active', true)
                ->where('stock_quantity', '>', 0)
                ->orderBy('name')
                ->get(['id', 'name', 'price', 'stock_quantity']),
            'canManage' => (int) auth()->user()->role === 2,
        ]);
    }

    public function store(Request $request)
    {
        abort_unless((int) auth()->user()->status === 1, 403);

        $data = $request->validate([
            'sale_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|distinct|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1|max:4294967295',
            'status' => 'sometimes|string|in:completed,pending',
            'notes' => 'nullable|string|max:1000',
        ]);

        $data['status'] = (int) auth()->user()->role === 2 ? ($data['status'] ?? 'completed') : 'completed';
        $data['user_id'] = auth()->id();
        DB::transaction(function () use ($data) {
            $items = $data['items'];
            unset($data['items']);
            $sale = Sale::create($data + ['amount' => 0]);
            $sale->amount = app(SaleInventory::class)->replaceItems($sale, $items);
            $sale->save();
        });

        return redirect()->route('sales');
    }

    public function edit($id)
    {
        abort_unless(in_array((int) auth()->user()->role, [0, 2], true), 403);
        $sale = Sale::whereNull('archived_at')
            ->with('items.product')
            ->findOrFail($id);

        return Inertia::render('SalesEdit', [
            'sale' => $sale,
            'products' => Product::where('is_active', true)
                ->where(function ($query) use ($sale) {
                    $query->where('stock_quantity', '>', 0)
                        ->orWhereIn('id', $sale->items->pluck('product_id'));
                })
                ->orderBy('name')
                ->get(['id', 'name', 'price', 'stock_quantity']),
            'canManage' => (int) auth()->user()->role === 2,
        ]);
    }

    public function update(Request $request, $id)
    {
        $isAdmin = (int) auth()->user()->role === 2;
        abort_unless($isAdmin || (int) auth()->user()->role === 0, 403);
        DB::transaction(function () use ($request, $id, $isAdmin) {
            $sale = Sale::whereNull('archived_at')
                ->with('items')
                ->lockForUpdate()
                ->findOrFail($id);

            $rules = [
                'sale_date' => 'required|date',
                'status' => 'sometimes|string|in:completed,pending',
                'notes' => 'nullable|string|max:1000',
            ];

            if ($sale->items->isEmpty()) {
                $rules['amount'] = 'required|numeric|min:0';
            } else {
                $rules['items'] = 'required|array|min:1';
                $rules['items.*.product_id'] = 'required|integer|distinct|exists:products,id';
                $rules['items.*.quantity'] = 'required|integer|min:1|max:4294967295';
            }

            $data = $request->validate($rules);
            if (! $isAdmin) {
                $data['status'] = $sale->status;
                $data['notes'] = $sale->notes;
            }

            if ($sale->items->isNotEmpty()) {
                $sale->amount = app(SaleInventory::class)->replaceItems($sale, $data['items']);
                unset($data['items']);
            }

            $sale->update($data);
        });

        return redirect()->route('sales');
    }

    public function archive($id)
    {
        abort_unless((int) auth()->user()->role === 2, 403);
        DB::transaction(function () use ($id) {
            $sale = Sale::with('items')->lockForUpdate()->findOrFail($id);
            if ($sale->archived_at === null) {
                app(SaleInventory::class)->restoreStock($sale);
                $sale->archived_at = now();
                $sale->save();
            }
        });

        return redirect()->route('sales');
    }
}
