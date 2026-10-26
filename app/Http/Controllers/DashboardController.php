<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\User;
use Carbon\Carbon;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        abort_unless(auth()->user()->role === null || (int) auth()->user()->role !== 0, 403);

        $totalAdmins = User::where('role', 2)
            ->whereNull('archived_at')
            ->count();

        $activeSales = Sale::whereNull('archived_at');
        $totalSalesCount = (clone $activeSales)->count();
        $totalSalesAmount = (float) (clone $activeSales)->sum('amount');
        $pendingSales = (clone $activeSales)->where('status', 'pending')->count();
        $completedSales = (clone $activeSales)->where('status', 'completed')->count();
        $averageOrderValue = $totalSalesCount > 0 ? round($totalSalesAmount / $totalSalesCount, 2) : 0;

        $today = Carbon::today();
        $firstMonth = $today->copy()->startOfMonth()->subMonths(5);
        $salesByMonth = (clone $activeSales)
            ->whereBetween('sale_date', [$firstMonth->toDateString(), $today->toDateString()])
            ->get(['sale_date', 'amount'])
            ->groupBy(fn ($sale) => Carbon::parse($sale->sale_date)->format('Y-m'))
            ->map(fn ($sales, $month) => [
                'label' => Carbon::createFromFormat('Y-m', $month)->format('M Y'),
                'total' => (float) $sales->sum('amount'),
            ]);

        $monthlySales = collect(range(0, 5))
            ->map(fn ($offset) => $firstMonth->copy()->addMonths($offset))
            ->map(fn (Carbon $month) => [
                'label' => $month->format('M Y'),
                'total' => $salesByMonth->get($month->format('Y-m'), ['total' => 0])['total'],
            ])
            ->values()
            ->all();

        $recentSales = (clone $activeSales)
            ->orderByDesc('sale_date')
            ->orderByDesc('id')
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
