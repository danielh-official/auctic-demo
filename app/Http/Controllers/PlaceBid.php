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
            'amount' => 'required|integer|min:1',
        ]);

        $amount = $validatedData['amount'];

        $placedAt = now();

        ProcessBidPlacement::dispatch(
            lot: $lot,
            user: auth()->user(),
            amount: $amount,
            placedAt: $placedAt,
        );

        return back()->with('success', 'Your bid has been placed. Please give it some time to be processed.');
    }
}
