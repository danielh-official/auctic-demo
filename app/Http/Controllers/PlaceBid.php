<?php

namespace App\Http\Controllers;

use App\Enums\BidStatus;
use App\Enums\LotStatus;
use App\Exceptions\BidCooldownException;
use App\Jobs\ProcessBidPlacement;
use App\Models\Lot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PlaceBid extends Controller
{
    public function __invoke(Request $request, Lot $lot)
    {
        // Validate the incoming request data
        $request->validate([
            'amount' => 'required|integer|min:1',
        ]);

        $user = $request->user();

        // Check if the auction is active
        if (! $lot->auction->is_active) {
            abort(422, 'This auction is not active. You cannot place a bid at this time.');
        }

        // Check if lot is accepting bids
        if ($lot->status !== LotStatus::Open) {
            abort(422, 'This lot is not accepting bids at this time.');
        }

        $highestBid = $lot->bids()
            ->where('status', BidStatus::Accepted)
            ->orderByDesc('amount')
            ->first();

        if ($highestBid && $highestBid->user_id === $user->id) {
            abort(409, 'You are already the highest bidder and cannot bid at this time.');
        }

        $cacheKey = ProcessBidPlacement::bidInProgressCacheKey($lot, $user);

        if (Cache::has($cacheKey)) {
            abort(409, 'Still processing original bid.');
        }

        $latestBid = $lot->bids()
            ->where('user_id', $user->id)
            ->whereIn('status', [BidStatus::Accepted, BidStatus::Outbid])
            ->latest('placed_at')
            ->first();

        if ($latestBid) {
            $cooldownExpiresAt = $latestBid->placed_at->copy()->addSeconds($lot->cooldown_phase_in_seconds);

            if (now()->lessThan($cooldownExpiresAt)) {
                $cooldownExpiresAtUtc = $cooldownExpiresAt->copy()->utc();
                $formattedDate = $cooldownExpiresAtUtc->format('F j, Y g:i A T');
                $message = "You can't bid on this lot until {$formattedDate}.";

                throw new BidCooldownException($message, $cooldownExpiresAtUtc->toIso8601String());
            }
        }

        $amount = $request->input('amount');

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
