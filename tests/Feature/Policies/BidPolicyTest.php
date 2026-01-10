<?php

use App\Enums\BidStatus;
use App\Models\Auction;
use App\Models\Bid;
use App\Models\Lot;
use App\Models\User;

test('bid cannot be updated once placed', function () {
    $user = User::factory()->create();
    $bid = Bid::factory()->for($user)->create();

    expect($user->can('update', $bid))->toBeFalse();
});

test('admin can update a bid status', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $bidder = User::factory()->create();
    $bid = Bid::factory()->for($bidder)->create();

    // Simulate updating only the status field
    $bid->fill(['status' => BidStatus::Outbid]);

    expect($admin->can('update', $bid))->toBeTrue();
});

test('admin cannot update bid amount', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $bidder = User::factory()->create();
    $bid = Bid::factory()->for($bidder)->create(['amount_cents' => 50_000]);

    // Simulate updating amount_cents
    $bid->fill(['amount_cents' => 60_000]);

    expect($admin->can('update', $bid))->toBeFalse();
});

test('admin cannot update multiple fields including status', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $bidder = User::factory()->create();
    $bid = Bid::factory()->for($bidder)->create(['amount_cents' => 50_000]);

    // Simulate updating both amount and status
    $bid->fill(['amount_cents' => 60_000, 'status' => BidStatus::Outbid]);

    expect($admin->can('update', $bid))->toBeFalse();
});

test('regular user cannot update a bid', function () {
    $user = User::factory()->create(['is_admin' => false]);
    $bid = Bid::factory()->for($user)->create();

    // Simulate updating status
    $bid->fill(['status' => BidStatus::Outbid]);

    expect($user->can('update', $bid))->toBeFalse();
});

test('auction owner cannot bid on their own auction', function () {
    $auctionOwner = User::factory()->create();
    $auction = Auction::factory()->create(['owner_id' => $auctionOwner->id]);
    $lot = Lot::factory()->for($auction)->create();

    expect($auctionOwner->can('create', [Bid::class, $lot]))->toBeFalse();
});

test('non-owner can bid on auction', function () {
    $auctionOwner = User::factory()->create();
    $bidder = User::factory()->create();
    $auction = Auction::factory()->create(['owner_id' => $auctionOwner->id]);
    $lot = Lot::factory()->for($auction)->create();

    expect($bidder->can('create', [Bid::class, $lot]))->toBeTrue();
});

test('bid below maximum is rejected', function () {
    $owner = User::factory()->create();
    $bidder = User::factory()->create();
    $auction = Auction::factory()->create(['owner_id' => $owner->id]);
    $lot = Lot::factory()->for($auction)->create();
    Bid::factory()->for($lot)->create(['amount_cents' => 50_000]);

    $lowBid = 40_000; // $400

    expect($bidder->can('create', [Bid::class, $lot, $lowBid]))->toBeFalse();
});

test('bid equal to maximum is rejected', function () {
    $owner = User::factory()->create();
    $bidder = User::factory()->create();
    $auction = Auction::factory()->create(['owner_id' => $owner->id]);
    $lot = Lot::factory()->for($auction)->create();
    Bid::factory()->for($lot)->create(['amount_cents' => 50_000]);

    expect($bidder->can('create', [Bid::class, $lot, 50_000]))->toBeFalse();
});

test('bid above maximum is accepted', function () {
    $owner = User::factory()->create();
    $bidder = User::factory()->create();
    $auction = Auction::factory()->create(['owner_id' => $owner->id]);
    $lot = Lot::factory()->for($auction)->create();
    Bid::factory()->for($lot)->create(['amount_cents' => 50_000]);

    $higherBid = 60_000;

    expect($bidder->can('create', [Bid::class, $lot, $higherBid]))->toBeTrue();
});
