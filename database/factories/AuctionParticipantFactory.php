<?php

namespace Database\Factories;

use App\Enums\ParticipantStatus;
use App\Models\Auction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AuctionParticipant>
 */
class AuctionParticipantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'auction_id' => Auction::factory(),
            'user_id' => User::factory(),
            'status' => ParticipantStatus::Approved,
        ];
    }

    public function invited(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ParticipantStatus::Invited,
        ]);
    }

    public function banned(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ParticipantStatus::Banned,
        ]);
    }
}
