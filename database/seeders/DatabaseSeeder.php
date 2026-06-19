<?php

namespace Database\Seeders;

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
        User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => 'password',
                'user_group' => 'owner',
            ]
        );

        $this->call([
            ChartOfAccountSeeder::class,
            UmkmFoodCatalogSeeder::class,
            WeeklySalesDemoSeeder::class,
        ]);
    }
}
