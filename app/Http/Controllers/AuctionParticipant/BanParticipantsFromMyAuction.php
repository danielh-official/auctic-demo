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
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'integer|distinct|exists:users,id|not_in:'.auth()->id(),
        ]);

        // If the current user is not the owner of the auction, return a 403 Forbidden response
        if ($auction->owner_id !== auth()->id()) {
            abort(403, 'You are not authorized to ban participants from this auction.');
        }

        // Parse user_ids array and get all values that coincides with existing users in the table
        $validUserIds = $request->input('user_ids');

        // If there are no valid user_ids, return an error message indicating that no valid user IDs were provided
        if (empty($validUserIds)) {
            return back()->with('error', 'No valid user IDs provided. Please provide valid user IDs to ban.');
        }

        // Sync participants with status 'banned' using the pivot relationship
        $auction->participants()->syncWithPivotValues(
            $validUserIds,
            ['status' => 'banned'],
            detaching: false
        );

        // Return back with a success message
        return back()->with('success', "Selected users have been banned from your auction {$auction->title} successfully.");
    }
}
