<?php

namespace App\Http\Controllers\AuctionRegistration;

use App\Enums\RegistrationStatus;
use App\Http\Controllers\Controller;
use App\Models\Auction;
use App\Models\AuctionRegistration;
use Illuminate\Http\Request;

class JoinAuction extends Controller
{
    public function __invoke(Request $request, Auction $auction)
    {
        // An auction owner cannot join their own auction as a participant
        if ($request->user()->id === $auction->owner_id) {
            return to_route('auctions.show', $auction)
                ->with('error', 'Auction owners cannot register as participants.');
        }

        // A user cannot join if they are banned
        if (AuctionRegistration::query()
            ->where('auction_id', $auction->id)
            ->where('user_id', $request->user()->id)
            ->where('status', RegistrationStatus::Banned)
            ->exists()) {
            return to_route('auctions.show', $auction)
                ->with('error', 'You are banned from participating in this auction.');
        }

        // A user cannot join if they are already a participant
        if (AuctionRegistration::query()
            ->where('auction_id', $auction->id)
            ->where('user_id', $request->user()->id)
            ->exists()) {
            return to_route('auctions.show', $auction)
                ->with('error', 'You are already registered as a participant for this auction.');
        }

        AuctionRegistration::firstOrCreate(
            [
                'auction_id' => $auction->id,
                'user_id' => $request->user()->id,
            ],
            [
                'status' => RegistrationStatus::Approved,
            ]
        );

        return to_route('auctions.show', $auction)
            ->with('success', 'You have successfully registered as a participant.');
    }
}
