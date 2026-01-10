<?php

namespace App\Http\Controllers\AuctionParticipant;

use App\Http\Controllers\Controller;
use App\Models\Auction;
use Illuminate\Http\Request;

class BanParticipantsFromMyAuction extends Controller
{
    public function __invoke(Request $request, Auction $auction)
    {
        // Accept user_ids as an array from the request
        // Parse user_ids array and get all values that coincides with existing users in the table
        // If there are no valid user_ids, return a 403 Forbidden response
        // Upsert an auction_participants record for each valid user_id with the status being equal to banned, ignoring any user id that doesn't exist or the owner's own user id
        // Return back with a success message
    }
}
