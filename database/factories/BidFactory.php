<?php

namespace Database\Factories;

use App\Enums\BidStatus;
use App\Models\Lot;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Bid>
 */
class BidFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'lot_id' => Lot::factory(),
            'user_id' => User::factory(),
            'amount_cents' => $this->faker->numberBetween(10_000, 100_000),
            'status' => BidStatus::Accepted,
            'placed_at' => now(),
        ];
    }

    public function outbid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => BidStatus::Outbid,
        ]);
    }
}
