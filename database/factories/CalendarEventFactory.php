<?php

namespace Database\Factories;

use App\Models\CalendarEvent;
use App\Models\Doctor;
use App\Models\Clinic;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CalendarEvent>
 */
class CalendarEventFactory extends Factory
{
    protected $model = CalendarEvent::class;

    public function definition(): array
    {
        $startTime = $this->faker->dateTimeBetween('now', '+1 week');
        $endTime = (clone $startTime)->modify('+1 hour');

        return [
            'appointment_id' => null,
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->optional()->paragraph(),
            'start_time' => $startTime,
            'end_time' => $endTime,
            'event_type' => $this->faker->randomElement(['appointment', 'reminder', 'block', 'meeting']),
            'doctor_id' => Doctor::factory(),
            'clinic_id' => $this->faker->optional()->randomElement([null, Clinic::factory()]),
            'color' => $this->faker->optional()->hexColor(),
            'is_all_day' => $this->faker->boolean(10),
        ];
    }
}
