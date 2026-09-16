<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Sale>
 */
class SaleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'sale_date' => $this->faker->date(),
            'amount' => $this->faker->randomFloat(2, 10, 5000),
            'status' => $this->faker->randomElement(['completed', 'pending']),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
