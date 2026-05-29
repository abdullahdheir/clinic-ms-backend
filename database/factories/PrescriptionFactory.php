<?php

namespace Database\Factories;

use App\Models\Prescription;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Prescription>
 */
class PrescriptionFactory extends Factory
{
    protected $model = Prescription::class;

    public function definition(): array
    {
        return [
            'patient_id' => User::factory()->create(['role' => 'patient']),
            'doctor_id' => Doctor::factory(),
            'visit_id' => null,
            'diagnosis' => $this->faker->sentence(3),
            'notes' => $this->faker->optional()->paragraph(),
            'prescription_date' => $this->faker->date(),
            'status' => $this->faker->randomElement(['active', 'completed', 'cancelled']),
        ];
    }
}
