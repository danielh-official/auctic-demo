<?php

namespace App\Policies;

use App\Models\Bid;
use App\Models\Lot;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class BidPolicy
{
    /**
     * Determine whether the user can create a bid.
     * - Auction owner cannot bid on their own auction
     * - Bid amount must be higher than current maximum bid
     */
    public function create(User $user, Lot $lot, int $amountCents = 0): Response
    {
        // Check if user is the auction owner
        if ($user->id === $lot->auction->owner_id) {
            return Response::deny('Auction owners cannot bid on their own auctions.');
        }

        // Check if bid amount meets minimum requirement
        $currentMaxBid = $lot->bids()->max('amount_cents') ?? 0;
        if ($amountCents > 0 && $amountCents <= $currentMaxBid) {
            return Response::deny('Bid must be higher than the current maximum bid.');
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can update the bid.
     * - Regular users cannot update bids once placed
     * - Admins can only update the status field
     */
    public function update(User $user, Bid $bid): Response
    {
        // Only admins can update
        if (! $user->isAdmin()) {
            return Response::deny('Bids cannot be updated once placed.');
        }

        // Admins can only update the status field
        $dirtyFields = array_keys($bid->getDirty());
        $allowedFields = ['status'];
        $disallowedFields = array_diff($dirtyFields, $allowedFields);

        if (! empty($disallowedFields)) {
            return Response::deny('Only bid status can be updated.');
        }

        return Response::allow();
    }
}
