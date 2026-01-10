<?php

namespace App\Http\Controllers\AuctionParticipant;

use App\Http\Controllers\Controller;
use App\Models\Auction;
use App\Models\AuctionParticipant;

/**
 * A user accepts an invitation from an auction owner to join the auction
 */
class AcceptInvitationToAuctionFromOwner extends Controller
{
    public function __invoke(Auction $auction)
    {
        // Check if there is an AuctionParticipant record for the current user and the given auction with status 'invited'; if not, return a 404 Forbidden response
        /**
         * @var AuctionParticipant $invite
         */
        $invite = $auction->participants()
            ->where('user_id', auth()->id())
            ->where('status', 'invited')
            ->firstOrFail();

        // Check if the user is the owner of the auction; if the user is the owner, return a 403 Forbidden response
        if ($auction->owner_id === auth()->id()) {
            $invite->delete();

            abort(403, 'You cannot accept an invitation to your own auction.');
        }

        // Update the AuctionParticipant record for the current user and the given auction to set status to 'approved'
        $invite->update(['status' => 'approved']);

        // Return back with a success message
        return back()->with('success', 'You have successfully joined the auction.');
    }
}
