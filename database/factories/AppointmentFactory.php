<?php

namespace Database\Factories;

use App\Models\Appointment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Appointment>
 */
class AppointmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'patient_id' => \App\Models\User::factory(),
            'doctor_id' => \App\Models\Doctor::factory(),
            'clinic_id' => \App\Models\Clinic::factory(),
            'department_id' => \App\Models\Department::factory(),
            'scheduled_at' => $this->faker->dateTimeBetween('now', '+1 month'),
            'status' => 'pending',
            'reason' => $this->faker->sentence(),
            'notes' => $this->faker->text(),
        ];
    }
}
