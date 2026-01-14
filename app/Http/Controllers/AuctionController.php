<?php

namespace App\Http\Controllers;

// use App\Enums\AuctionState;
use App\Models\Auction;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
// use Inertia\Inertia;
use Inertia\Response;

/**
 * Controller for public-facing auction interactions.
 * Users can view auctions owned by others and participate in them.
 * This is distinct from MyAuctionController which handles auctions a user owns.
 */
class AuctionController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of published auctions.
     * Accessible to all users (guests and authenticated).
     * Shows scheduled and live auctions, ordered by relevance.
     */
    public function index(): Response
    {
        // TODO: Implement frontend view
        // $auctions = Auction::query()
        //     ->whereIn('state', [AuctionState::Scheduled, AuctionState::Live])
        //     ->with(['owner', 'lots', 'participants'])
        //     ->orderByDesc('scheduled_at')
        //     ->paginate(20);
        //
        // return Inertia::render('Auctions/Browse', [
        //     'auctions' => $auctions,
        // ]);

        abort(501, 'Frontend not implemented');
    }

    /**
     * Display the specified auction with all its details.
     * Accessible to all users (guests and authenticated).
     * Shows auction details, lots, current bids, and participant info.
     *
     * Authorization: Users can view published auctions. Owners and admins
     * can also view draft/unpublished auctions.
     */
    public function show(Auction $auction): Response
    {
        // TODO: Implement frontend view
        // $auction->load([
        //     'owner',
        //     'lots.bids',
        //     'participants',
        // ]);
        //
        // return Inertia::render('Auctions/Show', [
        //     'auction' => $auction,
        //     'isOwner' => auth()?->id() === $auction->owner_id,
        //     'canBid' => auth()?->can('create', [Bid::class, $auction]),
        // ]);

        abort(501, 'Frontend not implemented');
    }
}
