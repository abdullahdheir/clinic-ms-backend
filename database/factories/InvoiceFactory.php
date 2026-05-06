<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'invoice_number' => 'INV-' . str_pad($this->faker->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'patient_id' => User::factory()->create(['role' => 'patient']),
            'visit_id' => null,
            'total_amount' => $this->faker->randomFloat(2, 50, 1000),
            'tax_amount' => 0,
            'tax_rate' => 0,
            'status' => $this->faker->randomElement(['pending', 'paid', 'cancelled']),
            'due_date' => $this->faker->optional()->date(),
            'paid_at' => $this->faker->optional()->dateTime(),
            'payment_method' => $this->faker->optional()->randomElement(['cash', 'card', 'bank_transfer', 'insurance']),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
