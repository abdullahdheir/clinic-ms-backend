<?php

namespace Database\Factories;

use App\Models\Doctor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Doctor>
 */
class DoctorFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'department_id' => \App\Models\Department::factory(),
            'bio' => $this->faker->paragraph(),
            'specialization' => $this->faker->jobTitle(),
            'session_duration_minutes' => 30,
            'consultation_fee' => 50.00,
        ];
    }
}
