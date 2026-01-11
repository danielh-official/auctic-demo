<?php

namespace App\Http\Controllers;

use App\Enums\AuctionBillStatus;
use App\Models\Auction;
use App\Models\AuctionBill;
use Illuminate\Http\Request;

class Pay extends Controller
{
    public function __invoke(Request $request, Auction $auction)
    {
        $request->validate([
            'payment_method' => 'required|string',
            'payment_reference' => 'required|string',
        ]);

        // Get the bill by using the authenticated user and auction ID
        $bill = AuctionBill::where('auction_id', $auction->id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        // Check if the bill is already paid
        if ($bill->status === AuctionBillStatus::Paid) {
            return back()->with('message', 'This bill has already been paid.');
        }

        // Mark the bill as paid
        $bill->status = AuctionBillStatus::Paid;
        $bill->paid_cents = $bill->total_cents; // Assuming full payment
        $bill->payment_method = $request->input('payment_method');
        $bill->payment_reference = $request->input('payment_reference');
        $bill->save();

        return back()->with('message', 'Payment successful. Thank you!');
    }
}
