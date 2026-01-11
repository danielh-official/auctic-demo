<?php

namespace App\Http\Controllers\AuctionParticipant;

use App\Http\Controllers\Controller;
use App\Models\Ban;
use Illuminate\Http\Request;

class BanParticipantsFromAllMyAuctions extends Controller
{
    public function __invoke(Request $request)
    {
        // Accept user_ids as an array from the request
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'integer|distinct|exists:users,id|not_in:'.auth()->id(),
        ]);

        // Parse user_ids array and get all values that coincides with existing users in the table
        $validUserIds = $request->input('user_ids');

        // If there are no valid user_ids, return an error message indicating that no valid user IDs were provided
        if (empty($validUserIds)) {
            return back()->with('error', 'No valid user IDs provided. Please provide valid user IDs to ban.');
        }

        $validUserIds = collect($validUserIds);

        // Upsert a bans record for each valid user_id with the current user's id as the owner_id and the user_id as the banned_user_id, ignoring any user id that doesn't exist or the owner's own user id
        Ban::upsert(
            $validUserIds->map(function ($userId) {
                return [
                    'owner_id' => auth()->id(),
                    'banned_user_id' => $userId,
                ];
            })->toArray(),
            ['owner_id', 'banned_user_id'], // Unique constraint to prevent duplicate bans
            ['updated_at'] // Update the timestamp if the record already exists
        );

        // Return back with a success message
        return back()->with('success', 'Selected users have been banned from all your auctions successfully.');
    }
}
