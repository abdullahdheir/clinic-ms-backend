<?php

namespace Database\Factories;

use App\Models\XrayImage;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<XrayImage>
 */
class XrayImageFactory extends Factory
{
    protected $model = XrayImage::class;

    public function definition(): array
    {
        return [
            'patient_id' => User::factory()->create(['role' => 'patient']),
            'doctor_id' => Doctor::factory(),
            'visit_id' => null,
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->optional()->paragraph(),
            'image_type' => $this->faker->randomElement(['chest', 'head', 'teeth', 'spine', 'hand', 'foot', 'joint', 'other']),
            'file_path' => 'xray_images/' . $this->faker->uuid() . '.jpg',
            'thumbnail_path' => 'xray_images/thumbs/' . $this->faker->uuid() . '.jpg',
            'xray_date' => $this->faker->date(),
            'findings' => $this->faker->optional()->paragraph(),
            'impression' => $this->faker->optional()->paragraph(),
        ];
    }
}
