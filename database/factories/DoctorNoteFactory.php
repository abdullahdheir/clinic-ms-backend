<?php

namespace Database\Factories;

use App\Models\DoctorNote;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DoctorNote>
 */
class DoctorNoteFactory extends Factory
{
    protected $model = DoctorNote::class;

    public function definition(): array
    {
        return [
            'patient_id' => User::factory()->create(['role' => 'patient']),
            'doctor_id' => Doctor::factory(),
            'visit_id' => null,
            'content' => $this->faker->paragraph(),
            'note_type' => $this->faker->randomElement(['general', 'follow_up', 'urgent', 'routine']),
            'is_pinned' => $this->faker->boolean(20), // 20% chance of being pinned
        ];
    }
}
