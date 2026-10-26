<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SaleController extends Controller
{
    public function index()
    {
        $isAdmin = (int) auth()->user()->role === 2;
        $search = $isAdmin ? trim((string) request()->query('search', '')) : '';
        $salesQuery = Sale::whereNull('archived_at')
            ->with('submittedBy:id,name')
            ->orderByDesc('sale_date')
            ->orderByDesc('id');

        if ($search !== '') {
            $salesQuery->where(function ($query) use ($search) {
                $query->where('sale_date', 'like', "%{$search}%")
                    ->orWhere('amount', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%")
                    ->orWhereHas('submittedBy', fn ($userQuery) => $userQuery->where('name', 'like', "%{$search}%"));

                if (ctype_digit($search)) {
                    $query->orWhere('id', (int) $search);
                }
            });
        }

        $sales = $salesQuery->paginate(15)->withQueryString();

        return Inertia::render('Sales', [
            'sales' => $sales,
            'search' => $search,
            'canManage' => $isAdmin,
            'canEditSales' => in_array((int) auth()->user()->role, [0, 2], true),
            'canDeleteSales' => $isAdmin,
        ]);
    }

    public function create()
    {
        abort_unless((int) auth()->user()->status === 1, 403);

        return Inertia::render('SalesCreate');
    }

    public function store(Request $request)
    {
        abort_unless((int) auth()->user()->status === 1, 403);

        $data = $request->validate([
            'sale_date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'status' => 'sometimes|string|in:completed,pending',
            'notes' => 'nullable|string|max:1000',
        ]);

        $data['status'] = (int) auth()->user()->role === 2 ? ($data['status'] ?? 'completed') : 'completed';
        $data['user_id'] = auth()->id();
        Sale::create($data);

        return redirect()->route('sales');
    }

    public function edit($id)
    {
        abort_unless(in_array((int) auth()->user()->role, [0, 2], true), 403);
        $sale = Sale::findOrFail($id);

        return Inertia::render('SalesEdit', [
            'sale' => $sale,
            'canManage' => (int) auth()->user()->role === 2,
        ]);
    }

    public function update(Request $request, $id)
    {
        $isAdmin = (int) auth()->user()->role === 2;
        abort_unless($isAdmin || (int) auth()->user()->role === 0, 403);
        $sale = Sale::findOrFail($id);

        $data = $request->validate([
            'sale_date' => 'required|date',
            'amount' => 'required|numeric',
            'status' => 'sometimes|string|in:completed,pending',
            'notes' => 'nullable|string|max:1000',
        ]);

        if (! $isAdmin) {
            $data['status'] = $sale->status;
            $data['notes'] = $sale->notes;
        }

        $sale->update($data);

        return redirect()->route('sales');
    }

    public function archive($id)
    {
        abort_unless((int) auth()->user()->role === 2, 403);
        $sale = Sale::findOrFail($id);
        $sale->archived_at = now();
        $sale->save();

        return redirect()->route('sales');
    }
}
