<?php

namespace App\Http\Controllers\AuctionParticipant;

use App\Http\Controllers\Controller;
use App\Models\Auction;
use Illuminate\Http\Request;

/**
 * A user accepts an invitation from an auction owner to join the auction
 */
class AcceptInvitationToAuctionFromOwner extends Controller
{
    public function __invoke(Request $request, Auction $auction)
    {
        // Check if there is an AuctionParticipant record for the current user and the given auction with status 'invited'; if not, return a 403 Forbidden response
        // Check if the user is the owner of the auction; if the user is the owner, return a 403 Forbidden response
        // Update the AuctionParticipant record for the current user and the given auction to set status to 'joined'
        // Return back with a success message
    }
}
