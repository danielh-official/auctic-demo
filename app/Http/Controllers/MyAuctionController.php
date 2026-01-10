<?php

namespace App\Http\Controllers;

use App\Enums\AuctionState;
use App\Http\Requests\StoreAuctionRequest;
use App\Http\Requests\UpdateAuctionRequest;
use App\Models\Auction;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
// use Inertia\Inertia;
use Inertia\Response;

/**
 * Controller for an auction that I own, as opposed to the public-facing AuctionController.
 */
class MyAuctionController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        // TODO: Implement frontend view
        // $this->authorize('viewAny', Auction::class);
        //
        // $auctions = Auction::query()
        //     ->where('owner_id', auth()->id())
        //     ->with('lots')
        //     ->latest()
        //     ->get();
        //
        // return Inertia::render('Auctions/Index', [
        //     'auctions' => $auctions,
        // ]);

        abort(501, 'Frontend not implemented');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        // TODO: Implement frontend view
        // $this->authorize('create', Auction::class);
        //
        // return Inertia::render('Auctions/Create');

        abort(501, 'Frontend not implemented');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAuctionRequest $request): RedirectResponse
    {
        $auction = Auction::create([
            ...$request->validated(),
            'owner_id' => auth()->id(),
            'state' => $request->input('state', AuctionState::Draft),
        ]);

        return to_route('my.auctions.show', $auction)
            ->with('success', 'Auction created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Auction $auction): Response
    {
        // TODO: Implement frontend view
        // $this->authorize('view', $auction);
        //
        // $auction->load(['lots', 'owner']);
        //
        // return Inertia::render('Auctions/Show', [
        //     'auction' => $auction,
        // ]);

        abort(501, 'Frontend not implemented');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Auction $auction): Response
    {
        // TODO: Implement frontend view
        // $this->authorize('update', $auction);
        //
        // return Inertia::render('Auctions/Edit', [
        //     'auction' => $auction,
        // ]);

        abort(501, 'Frontend not implemented');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAuctionRequest $request, Auction $auction): RedirectResponse
    {
        $auction->update($request->validated());

        return to_route('my.auctions.show', $auction)
            ->with('success', 'Auction updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Auction $auction): RedirectResponse
    {
        $this->authorize('delete', $auction);

        $auction->delete();

        return to_route('my.auctions.index')
            ->with('success', 'Auction deleted successfully.');
    }
}
