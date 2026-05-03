<?php

namespace Database\Factories;

use App\Models\Clinic;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Clinic>
 */
class ClinicFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->company() . ' Clinic',
            'address' => $this->faker->address(),
            'phone' => $this->faker->phoneNumber(),
            'logo_url' => $this->faker->imageUrl(),
            'manager_id' => \App\Models\User::factory(),
            'working_hours' => ['monday' => '08:00-17:00'],
            'is_active' => true,
        ];
    }
}
