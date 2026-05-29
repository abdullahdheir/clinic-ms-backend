<?php

namespace Database\Factories;

use App\Models\PrescriptionMedication;
use App\Models\Prescription;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PrescriptionMedication>
 */
class PrescriptionMedicationFactory extends Factory
{
    protected $model = PrescriptionMedication::class;

    public function definition(): array
    {
        return [
            'prescription_id' => Prescription::factory(),
            'medication_name' => $this->faker->word() . ' ' . $this->faker->randomNumber(3),
            'dosage' => $this->faker->randomElement(['5mg', '10mg', '25mg', '50mg', '100mg']),
            'frequency' => $this->faker->randomElement(['Once daily', 'Twice daily', 'Three times daily', 'As needed']),
            'duration' => $this->faker->randomElement(['7 days', '14 days', '30 days', '60 days']),
            'instructions' => $this->faker->optional()->sentence(),
        ];
    }
}
