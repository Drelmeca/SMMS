<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Sale;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        abort_unless(auth()->user()->role === null || (int) auth()->user()->role !== 0, 403);

        $totalAdmins = User::where('role', 2)->count();

        $totalSalesCount = Sale::count();
        $totalSalesAmount = (float) Sale::sum('amount');
        $pendingSales = Sale::where('status', 'pending')->count();
        $completedSales = Sale::where('status', 'completed')->count();
        $averageOrderValue = $totalSalesCount > 0 ? round($totalSalesAmount / $totalSalesCount, 2) : 0;

        $monthlySales = Sale::orderBy('sale_date')
            ->get()
            ->groupBy(fn ($sale) => \Carbon\Carbon::parse($sale->sale_date)->format('Y-m'))
            ->map(fn ($sales, $month) => [
                'label' => \Carbon\Carbon::createFromFormat('Y-m', $month)->format('M'),
                'total' => (float) $sales->sum('amount'),
            ])
            ->sortKeys()
            ->take(-6)
            ->values()
            ->toArray();

        $recentSales = Sale::orderByDesc('sale_date')
            ->take(5)
            ->get()
            ->map(fn ($sale) => [
                'id' => $sale->id,
                'sale_date' => $sale->sale_date,
                'amount' => (float) $sale->amount,
                'status' => $sale->status,
                'notes' => $sale->notes,
            ])
            ->toArray();

        return Inertia::render('Dashboard', [
            'totalAdmins' => $totalAdmins,
            'totalSalesCount' => $totalSalesCount,
            'totalSalesAmount' => $totalSalesAmount,
            'pendingSales' => $pendingSales,
            'completedSales' => $completedSales,
            'averageOrderValue' => $averageOrderValue,
            'recentSales' => $recentSales,
            'monthlySales' => $monthlySales,
        ]);
    }
}