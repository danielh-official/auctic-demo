<?php

namespace App\Http\Controllers;

use App\Enums\BidStatus;
// use App\Models\Auction;
use App\Jobs\SetHighestAcceptedBid;
use App\Models\Lot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PlaceBid extends Controller
{
    public function __invoke(Request $request, Lot $lot)
    {
        // Validate the incoming request data
        $validatedData = $request->validate([
            'amount_cents' => 'required|integer|min:1',
        ]);

        $amount = $validatedData['amount_cents'];

        // Check if the auction is active
        if (! $lot->auction->is_active) {
            return back()->with('error', 'This auction is not active. You cannot place a bid at this time.');
        }

        return DB::transaction(function () use ($lot, $amount) {
            // Lock the bids table for this lot to prevent race conditions
            $currentHighestBid = $lot->bids()
                ->lockForUpdate()
                ->orderBy('amount_cents', 'desc')
                ->first();

            if ($currentHighestBid && $amount <= $currentHighestBid->amount_cents) {
                return back()->with('error', 'Bid amount must be higher than the current highest bid.');
            }

            // A user cannot place a bid if they are currently the highest bidder
            if ($currentHighestBid && $currentHighestBid->user_id === auth()->id()) {
                return back()->with('error', 'You are already the highest bidder. You cannot place another bid on this lot at this time.');
            }

            // Create a new bid
            $lot->bids()->create([
                'user_id' => auth()->id(),
                'amount_cents' => $amount,
                'status' => BidStatus::Accepted,
            ]);

            // Dispatch job to set previous highest bid to Outbid
            SetHighestAcceptedBid::dispatch($lot);

            return back()->with('success', "Your bid for $amount has been placed successfully!");
        });
    }
}
