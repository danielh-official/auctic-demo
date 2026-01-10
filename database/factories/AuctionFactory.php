<?php

namespace Database\Factories;

use App\Enums\AuctionState;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Auction>
 */
class AuctionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $scheduledAt = $this->faker->dateTimeBetween('+1 day', '+3 days');

        return [
            'owner_id' => User::factory(),
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'state' => AuctionState::Draft,
            'scheduled_at' => $scheduledAt,
            'live_at' => null,
            'live_ends_at' => null,
            'closed_at' => null,
        ];
    }

    public function live(): static
    {
        return $this->state(function (array $attributes) {
            $liveAt = now()->subMinutes(5);

            return [
                'state' => AuctionState::Live,
                'owner_id' => $attributes['owner_id'] ?? \App\Models\User::factory(),
                'scheduled_at' => $attributes['scheduled_at'] ?? now()->subHour(),
                'live_at' => $liveAt,
                'live_ends_at' => now()->addHour(),
            ];
        });
    }
}
