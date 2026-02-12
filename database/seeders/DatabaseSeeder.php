<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Panggil SmkpSeeder untuk generate user dan folder
        $this->call([
            SmkpSeeder::class,
        ]);
    }
}