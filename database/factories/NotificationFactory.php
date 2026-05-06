<?php

namespace Database\Factories;

use App\Models\Notification;
use App\Models\User;
use App\Enums\NotificationType;
use App\Enums\NotificationPriority;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Notification>
 */
class NotificationFactory extends Factory
{
    protected $model = Notification::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => $this->faker->sentence(4),
            'message' => $this->faker->sentence(10),
            'type' => $this->faker->randomElement(NotificationType::cases()),
            'priority' => $this->faker->randomElement(NotificationPriority::cases()),
            'is_read' => false,
            'link' => $this->faker->optional()->url(),
            'data' => $this->faker->optional()->randomElement([
                ['appointment_id' => $this->faker->numberBetween(1, 100)],
                ['invoice_id' => $this->faker->numberBetween(1, 100)],
                ['visit_id' => $this->faker->numberBetween(1, 100)],
            ]),
        ];
    }

    /**
     * Indicate that the notification is read.
     */
    public function read(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_read' => true,
        ]);
    }

    /**
     * Indicate that the notification is unread.
     */
    public function unread(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_read' => false,
        ]);
    }

    /**
     * Create an appointment reminder notification.
     */
    public function appointmentReminder(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => 'تذكير بموعد',
            'message' => 'لديك موعد قريب',
            'type' => NotificationType::APPOINTMENT_REMINDER,
            'priority' => NotificationPriority::HIGH,
        ]);
    }

    /**
     * Create an appointment confirmed notification.
     */
    public function appointmentConfirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => 'تأكيد موعد',
            'message' => 'تم تأكيد موعدك بنجاح',
            'type' => NotificationType::APPOINTMENT_CONFIRMED,
            'priority' => NotificationPriority::MEDIUM,
        ]);
    }

    /**
     * Create a high priority notification.
     */
    public function highPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => NotificationPriority::HIGH,
        ]);
    }

    /**
     * Create a medium priority notification.
     */
    public function mediumPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => NotificationPriority::MEDIUM,
        ]);
    }

    /**
     * Create a low priority notification.
     */
    public function lowPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => NotificationPriority::LOW,
        ]);
    }
}
