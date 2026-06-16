<?php

namespace Database\Factories;

use App\Models\Reservation;
use App\Models\Patient;
use App\Models\Doctor;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReservationFactory extends Factory
{
    protected $model = Reservation::class;

    public function definition(): array
    {
        return [
            'patient_id' => Patient::factory(),
            'doctor_id' => Doctor::factory(),
            'complain' => fake()->sentence(10),
            'reservation_date' => fake()->dateTimeBetween('now', '+30 days')->format('Y-m-d'),
            'appointment_time' => fake()->randomElement(['08:00:00', '10:00:00', '14:00:00', '16:00:00', '19:00:00']),
            'status' => fake()->randomElement(['pending', 'validated', 'completed', 'cancelled']),
        ];
    }
}
