<?php

namespace App\Http\Controllers;

use App\Enums\LotStatus;
use App\Jobs\ProcessBidPlacement;
use App\Models\Lot;
use Illuminate\Http\Request;

class PlaceBid extends Controller
{
    public function __invoke(Request $request, Lot $lot)
    {
        // Check if the auction is active
        if (! $lot->auction->is_active) {
            return back()->with('error', 'This auction is not active. You cannot place a bid at this time.');
        }

        // Check if lot is accepting bids
        if ($lot->status !== LotStatus::Open) {
            return back()->with('error', 'This lot is not accepting bids at this time.');
        }

        // Validate the incoming request data
        $validatedData = $request->validate([
            'amount_cents' => 'required|integer|min:1',
        ]);

        $amount = $validatedData['amount_cents'];

        // TODO: Input into job queue for processing
        // $isFirstBid = $lot->bids()->count() === 0;

        // if (! $isFirstBid && $amount <= $lot->reserve_price_cents) {
        //     return back()->with('error', 'Your bid must be higher than the reserve price.');
        // }

        // // Check if bid is higher than the current highest bid
        // $currentHighestBid = $lot->bids()->orderByDesc('amount_cents')->first();

        // $minimumIncrement = 5000; // e.g., $50.00 in cents

        // $minimumBid = $currentHighestBid->amount_cents + $minimumIncrement;

        // if ($currentHighestBid && $amount <= $minimumBid) {
        //     $minimumBidInDollars = number_format($minimumBid / 100, 2);

        //     return back()->with('error', "Your bid must be higher than \${$minimumBidInDollars}.");
        // }

        $placedAt = now();
        $userId = auth()->id();

        ProcessBidPlacement::dispatch(
            lotId: $lot->id,
            userId: $userId,
            amountCents: $amount,
            placedAt: $placedAt,
        );

        return back()->with('success', 'Your bid has been placed. Please give it some time to be processed.');
    }
}
