<?php

namespace App\Http\Controllers;

use App\Enums\LotStatus;
use App\Models\Auction;
use App\Models\Lot;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;

class MyAuctionLotController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // TODO: Implement frontend to list all lots for a specific auction owned by the authenticated user
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // TODO: Implement frontend to show form for creating a new lot for a specific auction owned by the authenticated user
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Auction $auction)
    {
        // TODO: Create a new lot for a specific auction owned by the authenticated user using the data from the request
        $request->validate([
            'title' => 'required|string|max:255',
            'sku' => 'nullable|string|max:255',
            'reserve_price' => 'required|integer|min:0',
            'status' => ['required', 'string', new Enum(LotStatus::class)],
        ]);

        if (auth()->id() !== $auction->owner_id) {
            abort(403, 'User does not own this auction');
        }

        Lot::create([
            'auction_id' => $auction->id,
            'title' => $request->input('title'),
            'sku' => $request->input('sku'),
            'reserve_price' => $request->input('reserve_price'),
            'status' => $request->input('status'),
        ]);

        return redirect()->route('my.auctions.show', $auction->id)->with('success', 'Lot created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(Lot $lot)
    {
        // TODO: Implement frontend to show details of a specific lot for a specific auction owned by the authenticated user
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Lot $lot)
    {
        // TODO: Implement frontend to show form for editing an existing lot for a specific auction owned by the authenticated user
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Auction $auction, Lot $lot)
    {
        // TODO: Update an existing lot
        $request->validate([
            'title' => 'required|string|max:255',
            'sku' => 'nullable|string|max:255',
            'reserve_price' => 'required|integer|min:0',
            'status' => ['required', 'string', new Enum(LotStatus::class)],
        ]);

        if (auth()->id() !== $auction->owner_id) {
            abort(403, 'User does not own this auction');
        }

        if ($lot->auction_id !== $auction->id) {
            abort(404, 'Lot does not belong to this auction');
        }

        $lot->update([
            'title' => $request->input('title'),
            'sku' => $request->input('sku'),
            'reserve_price' => $request->input('reserve_price'),
            'status' => $request->input('status'),
        ]);

        return redirect()->route('my.auctions.show', $auction->id)->with('success', 'Lot created successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Auction $auction, Lot $lot)
    {
        // TODO: Delete an existing lot

        if (auth()->id() !== $auction->owner_id) {
            abort(403, 'User does not own this auction');
        }

        if ($lot->auction_id !== $auction->id) {
            abort(404, 'Lot does not belong to this auction');
        }

        $lot->delete();

        return redirect()->route('my.auctions.show', $auction->id)->with('success', 'Lot deleted successfully');
    }
}
