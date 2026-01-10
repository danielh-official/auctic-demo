<?php

namespace App\Http\Controllers;

use App\Models\Lot;
use Illuminate\Http\Request;

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
    public function store(Request $request)
    {
        // TODO: Create a new lot for a specific auction owned by the authenticated user using the data from the request
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
    public function update(Request $request, Lot $lot)
    {
        // TODO: Update an existing lot
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Lot $lot)
    {
        // TODO: Delete an existing lot
    }
}
