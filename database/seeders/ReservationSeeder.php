<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Reservation;
use App\Models\Terrain;
use App\Models\User;

class ReservationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create reservations for each user and terrain
        User::all()->each(function ($user) {
            $terrains = Terrain::inRandomOrder()->take(2)->get();

            $terrains->each(function ($terrain) use ($user) {
                Reservation::factory()->create([
                    'user_id' => $user->id,
                    'terrain_id' => $terrain->id,
                    'title' => 'Sample Reservation',
                    'class' => 'yellow-event',
                    'split' => "{$terrain->sport->type} {$terrain->label}",
                    'clickable' => true,
                    'duration' => 60,
                    'editable' => true,
                    'price' => rand(50, 150),
                    'category' => $terrain->sport->type,
                    'terrain' => $terrain->label,
                    'start' => now()->addDays(rand(1, 10))->setTime(rand(9, 17), 0),
                    'end' => now()->addDays(rand(1, 10))->setTime(rand(10, 18), 0),
                    'status' => 'confirmed',
                ]);
            });
        });
    }
}