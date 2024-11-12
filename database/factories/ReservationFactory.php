<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Reservation;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Reservation>
 */
class ReservationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Reservation::class;

    public function definition()
    {
        return [
            'user_id' => null, // Set in seeder
            'terrain_id' => null, // Set in seeder
            'title' => $this->faker->sentence,
            'class' => 'yellow-event',
            'split' => 'Football 1',
            'clickable' => true,
            'duration' => 60,
            'editable' => true,
            'price' => $this->faker->randomNumber(2),
            'category' => 'Football',
            'terrain' => 'T1',
            'start' => $this->faker->dateTimeBetween('+1 days', '+10 days'),
            'end' => $this->faker->dateTimeBetween('+1 days', '+10 days'),
            'status' => 'confirmed',
        ];
    }
}