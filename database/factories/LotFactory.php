<?php

namespace Database\Factories;

use App\Enums\LotStatus;
use App\Models\Auction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Lot>
 */
class LotFactory extends Factory
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
            'title' => $this->faker->sentence(2),
            'sku' => $this->faker->optional()->bothify('LOT-####'),
            'reserve_price' => $this->faker->numberBetween(5_000, 50_000),
            'status' => LotStatus::Pending,
            'auction_bill_id' => null,
        ];
    }

    public function open(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => LotStatus::Open,
        ]);
    }
}
