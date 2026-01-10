<?php

namespace App\Http\Controllers\AuctionParticipant;

use App\Http\Controllers\Controller;
use App\Enums\ParticipantStatus;
use App\Models\Auction;
use App\Models\AuctionParticipant;
use Illuminate\Support\Facades\Request;

class InviteParticipantsToMyAuction extends Controller
{
    public function __invoke(Request $request, Auction $auction)
    {
        $request->validate([
            'user_id' => ['required', 'exists:users,id'],
        ]);

        // Cannot invite already registered or invited users
        if (AuctionParticipant::query()
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

        $participant = AuctionParticipant::firstOrCreate(
            [
                'auction_id' => $auction->id,
                'user_id' => $request->input('user_id'),
            ],
            [
                'status' => ParticipantStatus::Invited,
            ]
        );

        return to_route('auctions.show', $auction)
            ->with('success', "You have successfully invited a participant: {$participant->user->name}.");
    }
}
