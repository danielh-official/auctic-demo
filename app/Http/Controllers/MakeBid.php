<?php

namespace App\Http\Controllers;

use App\Models\Auction;
use Illuminate\Http\Request;

class MakeBid extends Controller
{
    public function __invoke(Request $request, Auction $auction)
    {
        // Validate the incoming request data
        $validatedData = $request->validate([
            'bid_amount' => 'required|numeric|min:0.01',
        ]);

        $bidAmount = $validatedData['bid_amount'];

        // Check if the auction is active
        if (!$auction->is_active) {
            return response()->json(['message' => 'Auction is not active.'], 400);
        }

        // Check if the bid amount is higher than the current highest bid
        $currentHighestBid = $auction->bids()->orderBy('amount', 'desc')->first();
        if ($currentHighestBid && $bidAmount <= $currentHighestBid->amount) {
            return response()->json(['message' => 'Bid amount must be higher than the current highest bid.'], 400);
        }

        // Create a new bid
        $auction->bids()->create([
            'user_id' => auth()->id(),
            'amount' => $bidAmount,
        ]);

        return back()->with('success', "Your bid for $bidAmount has been placed successfully!");
    }
}
