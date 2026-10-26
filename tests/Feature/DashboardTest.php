<?php

namespace Tests\Feature;

use App\Models\Sale;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page()
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_authenticated_users_can_visit_the_dashboard()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/dashboard');
        $response->assertStatus(200);
    }

    public function test_dashboard_metrics_exclude_archived_sales_and_users()
    {
        $this->actingAs(User::factory()->create());

        User::factory()->create(['role' => 2]);
        $archivedAdmin = User::factory()->create(['role' => 2]);
        $archivedAdmin->archived_at = now();
        $archivedAdmin->save();

        Sale::factory()->create([
            'sale_date' => Carbon::today()->toDateString(),
            'amount' => 100,
            'status' => 'completed',
        ]);
        Sale::factory()->create([
            'sale_date' => Carbon::today()->subMonth()->toDateString(),
            'amount' => 50,
            'status' => 'pending',
        ]);
        Sale::factory()->create([
            'sale_date' => Carbon::today()->subMonths(7)->toDateString(),
            'amount' => 25,
            'status' => 'completed',
        ]);
        $archivedSale = Sale::factory()->create([
            'sale_date' => Carbon::today()->toDateString(),
            'amount' => 1000,
            'status' => 'completed',
        ]);
        $archivedSale->archived_at = now();
        $archivedSale->save();

        $this->get('/dashboard')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard', false)
                ->where('totalAdmins', 1)
                ->where('totalSalesCount', 3)
                ->where('totalSalesAmount', 175)
                ->where('pendingSales', 1)
                ->where('completedSales', 2)
                ->where('averageOrderValue', 58.33)
                ->has('monthlySales', 6)
                ->where('monthlySales.0.label', Carbon::today()->startOfMonth()->subMonths(5)->format('M Y'))
                ->where('monthlySales.0.total', 0)
                ->where('monthlySales.4.total', 50)
                ->where('monthlySales.5.total', 100)
                ->has('recentSales', 3)
                ->where('recentSales.0.amount', 100)
            );
    }
}
