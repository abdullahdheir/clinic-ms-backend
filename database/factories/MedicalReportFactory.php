<?php

namespace Database\Factories;

use App\Models\MedicalReport;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MedicalReport>
 */
class MedicalReportFactory extends Factory
{
    protected $model = MedicalReport::class;

    public function definition(): array
    {
        return [
            'patient_id' => User::factory()->create(['role' => 'patient']),
            'doctor_id' => Doctor::factory(),
            'visit_id' => null,
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'report_type' => $this->faker->randomElement(['blood_test', 'xray', 'mri', 'ultrasound', 'ct_scan']),
            'report_date' => $this->faker->date(),
            'results' => $this->faker->optional()->paragraph(),
            'recommendations' => $this->faker->optional()->paragraph(),
            'status' => $this->faker->randomElement(['pending', 'completed', 'reviewed']),
            'file_path' => null,
            'file_type' => null,
        ];
    }
}
