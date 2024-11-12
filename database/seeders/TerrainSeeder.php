<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Terrain;
use App\Models\Sport;

class TerrainSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create terrains for each sport
        Sport::all()->each(function ($sport) {
            Terrain::factory()->count(3)->create(['sport_id' => $sport->id]);
        });
    }
}