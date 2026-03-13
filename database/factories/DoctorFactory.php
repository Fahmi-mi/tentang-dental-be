<?php

namespace Database\Factories;

use App\Models\Doctor;
use Illuminate\Database\Eloquent\Factories\Factory;

class DoctorFactory extends Factory
{
    protected $model = Doctor::class;

    public function definition(): array
    {
        // Fixed schedule as per BACKEND_PLAN
        $schedule = [
            'senin' => ['08:00-14:00', '14:00-21:00'],
            'selasa' => [], // Libur
            'rabu' => ['14:00-17:00', '17:00-21:00'],
            'kamis' => ['08:00-14:00', '14:00-21:00'],
            'jumat' => ['14:00-17:00', '17:00-21:00'],
            'sabtu' => ['08:00-14:00', '14:00-21:00'],
            'minggu' => ['08:00-14:00', '14:00-21:00'],
        ];

        return [
            'name' => 'Dr. ' . fake()->name(),
            'specialization' => fake()->randomElement(['Orthodontist', 'Periodontist', 'General Dentist']),
            'photo' => null,
            'schedule' => $schedule,
            'statement' => fake()->sentence(20),
        ];
    }
}
