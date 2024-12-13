<?php

namespace Database\Factories;

use App\Models\Sport;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Sport>
 */
class SportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Sport::class;

    public function definition()
    {

        $type = $this->faker->randomElement(['Football', 'Padel']);
        return [
            'type' => $this->faker->randomElement(['Football', 'Padel']),
            'company_id' => null, // Set in seeder
        ];
    }
}