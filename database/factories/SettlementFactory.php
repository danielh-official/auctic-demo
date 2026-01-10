<?php

namespace Database\Factories;

use App\Enums\BidStatus;
use App\Enums\SettlementStatus;
use App\Models\Bid;
use App\Models\Lot;
use App\Models\Settlement;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Settlement>
 */
class SettlementFactory extends Factory
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
            'winning_bid_id' => null,
            'buyer_premium_cents' => 0,
            'total_cents' => 0,
            'status' => SettlementStatus::Pending,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Settlement $settlement) {
            if ($settlement->winning_bid_id !== null) {
                return;
            }

            $amount = fake()->numberBetween(20_000, 80_000);

            $winningBid = Bid::factory()
                ->for($settlement->lot)
                ->for(User::factory())
                ->create([
                    'amount_cents' => $amount,
                    'status' => BidStatus::Accepted,
                ]);

            $buyerPremium = (int) round($amount * 0.1);

            $settlement->update([
                'winning_bid_id' => $winningBid->id,
                'buyer_premium_cents' => $buyerPremium,
                'total_cents' => $amount + $buyerPremium,
            ]);
        });
    }
}
