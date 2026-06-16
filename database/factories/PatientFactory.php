<?php

namespace Database\Factories;

use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

class PatientFactory extends Factory
{
    protected $model = Patient::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'nickname' => fake()->firstName(),
            'gender' => fake()->randomElement(['male', 'female']),
            'age' => fake()->numberBetween(5, 80),
            'birth_place' => fake()->city(),
            'birth_date' => fake()->date('Y-m-d', '-10 years'),
            'address' => fake()->address(),
            'village' => fake()->streetName(),
            'district' => fake()->citySuffix(),
            'city' => fake()->city(),
            'phone' => fake()->unique()->numerify('08##########'),
            'occupation' => fake()->jobTitle(),
            'parent_name' => fake()->name(),
            'height' => fake()->randomFloat(2, 140, 190),
            'weight' => fake()->randomFloat(2, 40, 100),
        ];
    }
}
