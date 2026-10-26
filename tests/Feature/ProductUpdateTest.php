<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ProductUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_product_details_and_stock(): void
    {
        $this->actingAs(User::factory()->create(['role' => 2]));
        $product = Product::create([
            'name' => 'Tea',
            'category' => 'Drinks',
            'price' => 20,
            'stock_quantity' => 10,
            'is_active' => true,
        ]);

        $this->put("/products/{$product->id}", [
            'name' => 'Green tea',
            'category' => 'Beverages',
            'price' => 25.50,
            'stock_quantity' => 15,
            'is_active' => false,
        ])->assertRedirect('/products');

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Green tea',
            'category' => 'Beverages',
            'price' => 25.50,
            'stock_quantity' => 15,
            'is_active' => false,
        ]);
    }

    public function test_non_admin_cannot_update_product(): void
    {
        $this->actingAs(User::factory()->create(['role' => 1]));
        $product = Product::create([
            'name' => 'Tea',
            'price' => 20,
            'stock_quantity' => 10,
            'is_active' => true,
        ]);

        $this->put("/products/{$product->id}", ['name' => 'Changed'])
            ->assertForbidden();

        $this->assertDatabaseHas('products', ['id' => $product->id, 'name' => 'Tea']);
    }

    public function test_admin_can_view_inventory_and_non_admins_are_forbidden(): void
    {
        $product = Product::create([
            'name' => 'Tea',
            'category' => 'Drinks',
            'price' => 20,
            'stock_quantity' => 7,
            'is_active' => true,
        ]);

        $this->actingAs(User::factory()->create(['role' => 2]))
            ->get('/inventory')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Inventory', false)
                ->has('products', 1)
                ->where('products.0.id', $product->id)
                ->where('products.0.stock_quantity', 7)
            );

        $this->actingAs(User::factory()->create(['role' => 1]))
            ->get('/inventory')
            ->assertForbidden();
    }
}
