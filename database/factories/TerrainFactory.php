<?php

namespace Database\Factories;

use App\Models\Terrain;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Terrain>
 */
class TerrainFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Terrain::class;

    public function definition()
    {
        return [
            'label' => $this->faker->word,
            'prices' => [220],
            'sport_id' => null, // Set in seeder
        ];
    }
}