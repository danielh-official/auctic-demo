<?php

namespace App\Policies;

use App\Models\Auction;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AuctionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): Response
    {
        return Response::allow();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Auction $auction): Response
    {
        // Users can view their own auctions
        // Admins can view any auction
        if ($user->id === $auction->owner_id || $user->isAdmin()) {
            return Response::allow();
        }

        return Response::deny('You do not have permission to view this auction.');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): Response
    {
        // Any authenticated user can create an auction
        return Response::allow();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Auction $auction): Response
    {
        // Only the owner can update their auction
        // Admins can update any auction
        if ($user->id === $auction->owner_id || $user->isAdmin()) {
            return Response::allow();
        }

        return Response::deny('You do not have permission to update this auction.');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Auction $auction): Response
    {
        // Only the owner can delete their auction
        // Admins can delete any auction
        if ($user->id === $auction->owner_id || $user->isAdmin()) {
            return Response::allow();
        }

        return Response::deny('You do not have permission to delete this auction.');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Auction $auction): Response
    {
        // Only the owner can restore their auction
        // Admins can restore any auction
        if ($user->id === $auction->owner_id || $user->isAdmin()) {
            return Response::allow();
        }

        return Response::deny('You do not have permission to restore this auction.');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Auction $auction): Response
    {
        // Only admins can force delete
        if ($user->isAdmin()) {
            return Response::allow();
        }

        return Response::deny('You do not have permission to permanently delete this auction.');
    }
}
