<?php

namespace App\Http\Controllers;

use App\Models\Auction;
use Illuminate\Http\Request;

class Pay extends Controller
{
    public function __invoke(Request $request, Auction $auction)
    {
        // Validate the incoming request data
        $validatedData = $request->validate([
            'payment_method' => 'required|string',
            // Add other necessary validation rules for payment details
        ]);

        $paymentMethod = $validatedData['payment_method'];

        // Check if the auction is active and has a winning bid
        if (!$auction->is_active) {
            return response()->json(['message' => 'Auction is not active.'], 400);
        }

        $winningBid = $auction->bids()->orderBy('amount', 'desc')->first();
        if (!$winningBid) {
            return response()->json(['message' => 'No winning bid found for this auction.'], 400);
        }

        // Process the payment (this is a placeholder, implement actual payment processing logic)
        // For example, you could integrate with a payment gateway like Stripe or PayPal here

        // If payment is successful, mark the auction as paid and notify the winner
        $auction->update(['is_paid' => true]);

        return back()->with('success', "Your payment for the winning bid of {$winningBid->amount} has been processed successfully!");
    }
}
