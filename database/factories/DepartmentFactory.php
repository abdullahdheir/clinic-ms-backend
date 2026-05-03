<?php

namespace Database\Factories;

use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Department>
 */
class DepartmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'clinic_id' => \App\Models\Clinic::factory(),
            'name' => $this->faker->word() . ' Department',
            'specialty' => $this->faker->word(),
            'max_capacity' => $this->faker->numberBetween(5, 20),
            'description' => $this->faker->sentence(),
        ];
    }
}
