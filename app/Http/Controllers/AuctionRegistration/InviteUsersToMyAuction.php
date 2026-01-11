<?php

namespace App\Http\Controllers\AuctionRegistration;

use App\Enums\RegistrationStatus;
use App\Http\Controllers\Controller;
use App\Models\Auction;
use App\Models\AuctionRegistration;
use Illuminate\Support\Facades\Request;

class InviteUsersToMyAuction extends Controller
{
    public function __invoke(Request $request, Auction $auction)
    {
        $request->validate([
            'user_id' => ['required', 'exists:users,id'],
        ]);

        // Cannot invite already registered or invited users
        if (AuctionRegistration::query()
            ->where('auction_id', $auction->id)
            ->where('user_id', $request->input('user_id'))
            ->exists()) {
            return to_route('auctions.show', $auction)
                ->with('error', 'This user is already registered or invited to this auction.');
        }

        // Owner cannot invite themselves
        if ($auction->owner_id === $request->input('user_id')) {
            return to_route('auctions.show', $auction)
                ->with('error', 'You cannot invite yourself to your own auction.');
        }

        $registration = AuctionRegistration::firstOrCreate(
            [
                'auction_id' => $auction->id,
                'user_id' => $request->input('user_id'),
            ],
            [
                'status' => RegistrationStatus::Invited,
            ]
        );

        return to_route('auctions.show', $auction)
            ->with('success', "You have successfully invited a user: {$registration->user->name}.");
    }
}
