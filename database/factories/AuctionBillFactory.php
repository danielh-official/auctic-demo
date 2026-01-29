<?php

namespace Database\Factories;

use App\Models\Auction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AuctionBill>
 */
class AuctionBillFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = fake()->numberBetween(10000, 5000000); // $100 to $50,000 in cents
        $buyersPremiumRate = fake()->randomFloat(4, 0.10, 0.25); // 10-25%
        $buyersPremium = (int) round($subtotal * $buyersPremiumRate);
        $taxRate = fake()->randomFloat(4, 0.05, 0.10); // 5-10%
        $tax = (int) round(($subtotal + $buyersPremium) * $taxRate);
        $total = $subtotal + $buyersPremium + $tax;

        return [
            'auction_id' => Auction::factory(),
            'user_id' => User::factory(),
            'subtotal_amount' => $subtotal,
            'buyer_premium_amount' => $buyersPremium,
            'tax_amount' => $tax,
            'total_amount' => $total,
            'paid_amount' => 0,
            'status' => 'unpaid',
            'due_at' => fake()->dateTimeBetween('now', '+30 days'),
            'payment_method' => null,
            'payment_reference' => null,
        ];
    }

    /**
     * Indicate that the bill has been paid.
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'paid',
            'paid_amount' => $attributes['total_amount'],
        ]);
    }

    /**
     * Indicate that the bill is unpaid.
     */
    public function unpaid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'unpaid',
            'paid_amount' => 0,
        ]);
    }
}
