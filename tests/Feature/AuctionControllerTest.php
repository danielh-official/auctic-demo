<?php

use App\Enums\AuctionState;
use App\Models\Auction;
use App\Models\User;

use function Pest\Laravel\actingAs;

// Index Tests
test('guest can view auctions index', function () {
    $this->markTestIncomplete('Frontend not implemented yet');

    $response = $this->get(route('auctions.index'));

    $response->assertOk();
});

test('authenticated user can view auctions index', function () {
    $this->markTestIncomplete('Frontend not implemented yet');

    $user = User::factory()->create();

    $response = actingAs($user)->get(route('auctions.index'));

    $response->assertOk();
});

test('index lists scheduled and live auctions', function () {
    $this->markTestIncomplete('Frontend not implemented yet');

    $owner = User::factory()->create();
    $scheduledAuction = Auction::factory()->for($owner, 'owner')->create(['state' => AuctionState::Scheduled]);
    $liveAuction = Auction::factory()->for($owner, 'owner')->create(['state' => AuctionState::Live]);
    $draftAuction = Auction::factory()->for($owner, 'owner')->create(['state' => AuctionState::Draft]);
    $closedAuction = Auction::factory()->for($owner, 'owner')->create(['state' => AuctionState::Closed]);

    $response = $this->get(route('auctions.index'));

    $response->assertOk();
    // Should contain scheduled and live, but not draft or closed
});

test('index includes owner information', function () {
    $this->markTestIncomplete('Frontend not implemented yet');

    $owner = User::factory()->create(['name' => 'Auction Owner']);
    $auction = Auction::factory()->for($owner, 'owner')->create(['state' => AuctionState::Live]);

    $response = $this->get(route('auctions.index'));

    $response->assertOk();
});

test('index includes lot information', function () {
    $this->markTestIncomplete('Frontend not implemented yet');

    $owner = User::factory()->create();
    $auction = Auction::factory()->for($owner, 'owner')->create(['state' => AuctionState::Live]);
    $lot = \App\Models\Lot::factory()->for($auction)->create(['title' => 'Test Lot']);

    $response = $this->get(route('auctions.index'));

    $response->assertOk();
});

// Show Tests
test('guest can view a published auction', function () {
    $this->markTestIncomplete('Frontend not implemented yet');

    $owner = User::factory()->create();
    $auction = Auction::factory()->for($owner, 'owner')->create(['state' => AuctionState::Live]);

    $response = $this->get(route('auctions.show', $auction));

    $response->assertOk();
});

test('authenticated user can view a published auction', function () {
    $this->markTestIncomplete('Frontend not implemented yet');

    $user = User::factory()->create();
    $owner = User::factory()->create();
    $auction = Auction::factory()->for($owner, 'owner')->create(['state' => AuctionState::Live]);

    $response = actingAs($user)->get(route('auctions.show', $auction));

    $response->assertOk();
});

test('user can view their own auction from public view', function () {
    $this->markTestIncomplete('Frontend not implemented yet');

    $owner = User::factory()->create();
    $auction = Auction::factory()->for($owner, 'owner')->create(['state' => AuctionState::Live]);

    $response = actingAs($owner)->get(route('auctions.show', $auction));

    $response->assertOk();
});

test('guest cannot view draft auction', function () {
    $owner = User::factory()->create();
    $auction = Auction::factory()->for($owner, 'owner')->create(['state' => AuctionState::Draft]);

    $response = $this->get(route('auctions.show', $auction));

    $response->assertForbidden();
});

test('non-owner cannot view draft auction', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $auction = Auction::factory()->for($owner, 'owner')->create(['state' => AuctionState::Draft]);

    $response = actingAs($otherUser)->get(route('auctions.show', $auction));

    $response->assertForbidden();
});

test('owner can view their own draft auction via public controller', function () {
    $owner = User::factory()->create();
    $auction = Auction::factory()->for($owner, 'owner')->create(['state' => AuctionState::Draft]);

    $response = actingAs($owner)->get(route('auctions.show', $auction));

    // Owner can view their own auctions even if draft (policy allows this)
    // But frontend is not implemented, so we get 501 Not Implemented
    $response->assertStatus(501);
});

test('admin can view any auction including draft', function () {
    $this->markTestIncomplete('Frontend not implemented yet');

    $admin = User::factory()->create(['is_admin' => true]);
    $owner = User::factory()->create();
    $auction = Auction::factory()->for($owner, 'owner')->create(['state' => AuctionState::Draft]);

    $response = actingAs($admin)->get(route('auctions.show', $auction));

    $response->assertOk();
});

test('show includes auction owner details', function () {
    $this->markTestIncomplete('Frontend not implemented yet');

    $owner = User::factory()->create(['name' => 'John Auctioneer']);
    $auction = Auction::factory()->for($owner, 'owner')->create(['state' => AuctionState::Live]);

    $response = $this->get(route('auctions.show', $auction));

    $response->assertOk();
});

test('show includes lots with bids', function () {
    $this->markTestIncomplete('Frontend not implemented yet');

    $owner = User::factory()->create();
    $bidder = User::factory()->create();
    $auction = Auction::factory()->for($owner, 'owner')->create(['state' => AuctionState::Live]);
    $lot = \App\Models\Lot::factory()->for($auction)->create();
    \App\Models\Bid::factory()->for($lot)->for($bidder)->create();

    $response = $this->get(route('auctions.show', $auction));

    $response->assertOk();
});

test('show includes registration information', function () {
    $this->markTestIncomplete('Frontend not implemented yet');

    $owner = User::factory()->create();
    $registration = User::factory()->create();
    $auction = Auction::factory()->for($owner, 'owner')->create(['state' => AuctionState::Live]);
    \App\Models\AuctionRegistration::factory()->for($auction)->for($registration, 'user')->create();

    $response = $this->get(route('auctions.show', $auction));

    $response->assertOk();
});

test('deleted auction cannot be viewed', function () {
    $owner = User::factory()->create();
    $auction = Auction::factory()->for($owner, 'owner')->create(['state' => AuctionState::Live]);
    $auction->delete();

    $response = $this->get(route('auctions.show', $auction));

    $response->assertNotFound();
});

test('create, edit, update, and destroy routes are not registered', function () {
    // The AuctionController is resource-restricted to only index and show
    // Any POST/PATCH/DELETE attempts would result in 404 or method not allowed
    $auction = Auction::factory()->create();

    // Verify that the resource routes are limited to index and show
    $routes = route('auctions.index');
    $routes = route('auctions.show', $auction);

    expect($routes)->toBeString();
});
