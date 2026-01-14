<?php

namespace App\Http\Controllers\AuctionRegistration;

use App\Enums\RegistrationStatus;
use App\Http\Controllers\Controller;
use App\Models\Auction;
use App\Models\AuctionRegistration;
use Illuminate\Http\Request;

class InviteUsersToMyAuction extends Controller
{
    public function __invoke(Request $request, Auction $auction)
    {
        // If the current user is not the owner of the auction, return a 403 Forbidden response
        if ($auction->owner_id !== auth()->id()) {
            abort(403, 'You are not authorized to invite users to this auction.');
        }

        $request->validate([
            'user_ids' => ['required', 'array'],
            'user_ids.*' => ['exists:users,id'],
        ]);

        // Cannot invite already registered or invited users
        if (AuctionRegistration::query()
            ->where('auction_id', $auction->id)
            ->whereIn('user_id', $request->input('user_ids'))
            ->exists()) {
            return to_route('auctions.show', $auction)
                ->with('error', 'One or more users are already registered or invited to this auction.');
        }

        // Owner cannot invite themselves
        if (in_array($auction->owner_id, $request->input('user_ids'))) {
            return to_route('auctions.show', $auction)
                ->with('error', 'You cannot invite yourself to your own auction. One of given user IDs is the owner of the auction.');
        }

        // Upsert a auction_registration record for each valid user_id with the auction->id as the aucton_id and each user_id as the user_id, with status set to 'invited', ignoring any user id that doesn't exist or the owner's own user id
        AuctionRegistration::query()->upsert(
            collect($request->input('user_ids'))->map(function ($userId) use ($auction) {
                return [
                    'auction_id' => $auction->id,
                    'user_id' => $userId,
                    'status' => RegistrationStatus::Invited,
                ];
            })->toArray(),
            ['auction_id', 'user_id'], // Unique constraint to prevent duplicate registrations
            ['status', 'updated_at'] // Update the status and timestamp if the record already exists
        );

        return to_route('auctions.show', $auction)
            ->with('success', 'You have successfully invited users.');
    }
}
