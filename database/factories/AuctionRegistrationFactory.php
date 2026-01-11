<?php

namespace Database\Factories;

use App\Enums\RegistrationStatus;
use App\Models\Auction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AuctionRegistration>
 */
class AuctionRegistrationFactory extends Factory
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
            'status' => RegistrationStatus::Approved,
        ];
    }

    public function invited(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => RegistrationStatus::Invited,
        ]);
    }

    public function banned(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => RegistrationStatus::Banned,
        ]);
    }
}
