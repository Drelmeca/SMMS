<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaleInventoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_sales_with_multiple_products_calculate_total_and_deduct_stock_even_when_pending(): void
    {
        $this->actingAs(User::factory()->create(['role' => 2, 'status' => 1]));
        $firstProduct = $this->createProduct('Tea', '12.50', 10);
        $secondProduct = $this->createProduct('Bread', '4.25', 8);

        $this->post('/sales/store', [
            'sale_date' => today()->toDateString(),
            'status' => 'pending',
            'items' => [
                ['product_id' => $firstProduct->id, 'quantity' => 2],
                ['product_id' => $secondProduct->id, 'quantity' => 3],
            ],
        ])->assertRedirect('/sales');

        $sale = Sale::with('items')->firstOrFail();
        $this->assertSame('pending', $sale->status);
        $this->assertEquals(37.75, $sale->amount);
        $this->assertCount(2, $sale->items);
        $this->assertSame(8, $firstProduct->fresh()->stock_quantity);
        $this->assertSame(5, $secondProduct->fresh()->stock_quantity);
    }

    public function test_failed_sale_does_not_create_a_record_or_change_stock(): void
    {
        $this->actingAs(User::factory()->create(['role' => 1, 'status' => 1]));
        $product = $this->createProduct('Tea', '12.50', 2);

        $this->from('/sales')
            ->post('/sales/store', [
                'sale_date' => today()->toDateString(),
                'items' => [
                    ['product_id' => $product->id, 'quantity' => 3],
                ],
            ])
            ->assertRedirect('/sales')
            ->assertSessionHasErrors('items');

        $this->assertDatabaseCount('sales', 0);
        $this->assertSame(2, $product->fresh()->stock_quantity);
    }

    public function test_edit_archive_and_restore_reconcile_product_stock(): void
    {
        $this->actingAs(User::factory()->create(['role' => 2, 'status' => 1]));
        $product = $this->createProduct('Tea', '10.00', 5);

        $this->post('/sales/store', [
            'sale_date' => today()->toDateString(),
            'items' => [['product_id' => $product->id, 'quantity' => 2]],
        ]);
        $sale = Sale::firstOrFail();
        $this->assertSame(3, $product->fresh()->stock_quantity);

        $this->post("/sales/update/{$sale->id}", [
            'sale_date' => today()->toDateString(),
            'items' => [['product_id' => $product->id, 'quantity' => 4]],
        ])->assertRedirect('/sales');
        $this->assertSame(1, $product->fresh()->stock_quantity);
        $this->assertEquals(40, $sale->fresh()->amount);

        $this->post("/sales/archive/{$sale->id}")->assertRedirect('/sales');
        $this->assertSame(5, $product->fresh()->stock_quantity);

        $this->patch("/archive/sales/{$sale->id}/restore")->assertRedirect();
        $this->assertSame(1, $product->fresh()->stock_quantity);
    }

    private function createProduct(string $name, string $price, int $stock): Product
    {
        return Product::create([
            'name' => $name,
            'price' => $price,
            'stock_quantity' => $stock,
            'is_active' => true,
        ]);
    }
}
