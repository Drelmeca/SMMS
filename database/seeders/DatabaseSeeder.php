<?php

namespace Database\Seeders;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::firstOrCreate([
            'email' => 'admin@gmail.com',
        ], [
            'name' => 'System Admin',
            'password' => Hash::make('@admin101'),
            'status' => '1',
            'role' => '2',
        ]);

        // Seed demo sales
        $this->call([
            SaleSeeder::class,
        ]);
    }
}
