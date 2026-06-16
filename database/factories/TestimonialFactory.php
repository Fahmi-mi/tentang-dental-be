<?php

namespace Database\Factories;

use App\Models\Testimonial;
use Illuminate\Database\Eloquent\Factories\Factory;

class TestimonialFactory extends Factory
{
    protected $model = Testimonial::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'rating' => fake()->numberBetween(1, 5),
            'testimoni' => fake()->paragraph(3),
            'photo' => null,
        ];
    }
}
