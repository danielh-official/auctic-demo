<?php

namespace App\Http\Controllers\AuctionParticipant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BanParticipantsFromAllMyAuctions extends Controller
{
    public function __invoke(Request $request)
    {
        // Accept user_ids as an array from the request
        // Parse user_ids array and get all values that coincides with existing users in the table
        // If there are no valid user_ids, return a 403 Forbidden response
        // Upsert a bans record for each valid user_id with the current user's id as the owner_id and the user_id as the banned_user_id, ignoring any user id that doesn't exist or the owner's own user id
        // Return back with a success message
    }
}
