<?php

namespace Tests\Feature;

use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminSalesSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_admins_can_search_sales_across_all_pages(): void
    {
        $this->actingAs(User::factory()->create(['role' => 2]));
        Sale::factory()->count(15)->create();
        $matchingSale = Sale::factory()->create([
            'sale_date' => '2020-01-01',
            'notes' => 'Searchable note',
        ]);

        $this->get('/sales?search=Searchable')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Sales', false)
                ->where('search', 'Searchable')
                ->where('canManage', true)
                ->has('sales.data', 1)
                ->where('sales.data.0.id', $matchingSale->id)
            );
    }

    public function test_non_admin_sales_listing_ignores_search_query(): void
    {
        $this->actingAs(User::factory()->create(['role' => 1]));
        Sale::factory()->create(['notes' => 'Searchable note']);
        Sale::factory()->create(['notes' => 'Another note']);

        $this->get('/sales?search=Searchable')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Sales', false)
                ->where('search', '')
                ->where('canManage', false)
                ->has('sales.data', 2)
            );
    }
}
