<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\User;
use App\Services\SaleInventory;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ArchiveController extends Controller
{
    private function ensureAdmin(): void
    {
        abort_unless((int) auth()->user()->role === 2, 403);
    }

    public function index()
    {
        $this->ensureAdmin();

        return Inertia::render('Archive', [
            'users' => User::whereNotNull('archived_at')->latest('archived_at')->get(),
            'sales' => Sale::whereNotNull('archived_at')
                ->with(['submittedBy:id,name', 'items.product:id,name'])
                ->latest('archived_at')
                ->get(),
        ]);
    }

    public function restoreUser($id)
    {
        $this->ensureAdmin();

        $user = User::findOrFail($id);
        $user->archived_at = null;
        $user->save();

        return back();
    }

    public function restoreSale($id)
    {
        $this->ensureAdmin();

        DB::transaction(function () use ($id) {
            $sale = Sale::with('items')->lockForUpdate()->findOrFail($id);
            if ($sale->archived_at !== null) {
                app(SaleInventory::class)->deductStock($sale);
                $sale->archived_at = null;
                $sale->save();
            }
        });

        return back();
    }
}
