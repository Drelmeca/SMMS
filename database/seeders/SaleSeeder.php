<?php

namespace Database\Seeders;

use App\Models\Sale;
use Illuminate\Database\Seeder;
class SaleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create 50 demo sales
        Sale::factory()->count(50)->create();
    }
}
