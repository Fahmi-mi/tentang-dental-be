<?php

namespace Database\Factories;

use App\Models\Doctor;
use Illuminate\Database\Eloquent\Factories\Factory;

class DoctorFactory extends Factory
{
    protected $model = Doctor::class;

    public function definition(): array
    {
        $schedule = config('doctor_schedule.default', []);

        return [
            'name' => 'Dr. ' . fake()->name(),
            'specialization' => fake()->randomElement(['Orthodontist', 'Periodontist', 'General Dentist']),
            'photo' => null,
            'schedule' => $schedule,
            'statement' => fake()->sentence(20),
        ];
    }
}
