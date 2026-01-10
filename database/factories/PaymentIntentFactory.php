<?php

namespace Database\Factories;

use App\Enums\PaymentIntentStatus;
use App\Models\Settlement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PaymentIntent>
 */
class PaymentIntentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'settlement_id' => Settlement::factory(),
            'amount_cents' => $this->faker->numberBetween(10_000, 120_000),
            'status' => PaymentIntentStatus::Initiated,
            'reference' => $this->faker->uuid(),
        ];
    }

    public function succeeded(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PaymentIntentStatus::Succeeded,
        ]);
    }
}
