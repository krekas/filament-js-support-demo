<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Filament admin login: admin@admin.com / password
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'admin@admin.com',
        ]);

        $this->call([
            VenueSeeder::class,
            EventSeeder::class,
            BookingSeeder::class,
        ]);
    }
}
