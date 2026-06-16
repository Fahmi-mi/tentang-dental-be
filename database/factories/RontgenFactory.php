<?php

namespace Database\Factories;

use App\Models\Rontgen;
use App\Models\Patient;
use App\Models\Doctor;
use Illuminate\Database\Eloquent\Factories\Factory;

class RontgenFactory extends Factory
{
    protected $model = Rontgen::class;

    public function definition(): array
    {
        return [
            'patient_id' => Patient::factory(),
            'doctor_id' => Doctor::factory(),
            'detail' => fake()->sentence(15),
        ];
    }
}
