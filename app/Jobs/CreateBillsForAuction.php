<?php

namespace App\Jobs;

use App\Enums\AuctionBillStatus;
use App\Models\Auction;
use App\Models\AuctionBill;
use Illuminate\Contracts\Broadcasting\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\SerializesModels;

class CreateBillsForAuction implements ShouldQueue, ShouldBeUnique
{
    use Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected Auction $auction
    )
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Get all lots for the auction along with their winning bids
        $this->auction->lots()
            ->with('winningBid')
            ->get();

        $lotsToBill = collect();

        foreach ($this->auction->lots as $lot) {
            // If there is a winning bid for the lot, create or update the corresponding bill

            if ($lot->winningBid) {
                $lotsToBill->push($lot);
            }
        }

        if ($lotsToBill->isEmpty()) {
            // No winning bids, so no bills to create
            return;
        }

        // Group lots to bill by user ID (the bidder who won the lot)
        $billsData = $lotsToBill->groupBy(fn ($lot) => $lot->winningBid->user_id)
            ->map(function ($lots, $userId) {
                $subtotal = $lots->sum(fn ($lot) => $lot->winningBid->amount_cents);
                $buyersPremiumRate = 0.20; // Example fixed rate of 20%
                $buyersPremium = (int) round($subtotal * $buyersPremiumRate);
                $taxRate = 0.08; // Example fixed tax rate of 8%
                $tax = (int) round(($subtotal + $buyersPremium) * $taxRate);
                $total = $subtotal + $buyersPremium + $tax;

                return [
                    'auction_id' => $this->auction->id,
                    'user_id' => $userId,
                    'subtotal_cents' => $subtotal,
                    'buyer_premium_cents' => $buyersPremium,
                    'tax_cents' => $tax,
                    'total_cents' => $total,
                    'paid_cents' => 0,
                    'status' => AuctionBillStatus::Unpaid,
                    'due_at' => now()->addDays(30),
                ];
            });

        foreach ($billsData as $billData) {
            // Create or update the bill for each user
            AuctionBill::updateOrCreate(
                [
                    'auction_id' => $billData['auction_id'],
                    'user_id' => $billData['user_id'],
                ],
                [
                    'subtotal_cents' => $billData['subtotal_cents'],
                    'buyer_premium_cents' => $billData['buyer_premium_cents'],
                    'tax_cents' => $billData['tax_cents'],
                    'total_cents' => $billData['total_cents'],
                    'paid_cents' => 0,
                    'status' => 'unpaid',
                    'due_at' => $billData['due_at'],
                ]
            );
        }

        // Apply new bill id to each lot that makes up the bill
        foreach ($lotsToBill as $lot) {
            $lot->auction_bill_id = AuctionBill::where('auction_id', $this->auction->id)
                ->where('user_id', $lot->winningBid->user_id)
                ->first()
                ->id;
            $lot->save();
        }
    }

    public function uniqueId(): string
    {
        return "create-bills-for-auction:{$this->auction->id}";
    }
}
