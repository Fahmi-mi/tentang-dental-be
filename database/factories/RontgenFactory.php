<?php

namespace Database\Factories;

use App\Models\Rontgen;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

class RontgenFactory extends Factory
{
    protected $model = Rontgen::class;

    public function definition(): array
    {
        return [
            'patient_id' => Patient::factory(),
            'xray_image' => null,
            'detail' => fake()->sentence(15),
        ];
    }
}
