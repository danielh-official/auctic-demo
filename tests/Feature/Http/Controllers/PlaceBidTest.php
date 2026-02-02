<?php

use App\Enums\AuctionState;
use App\Enums\BidStatus;
use App\Enums\LotStatus;
use App\Jobs\ProcessBidPlacement;
use App\Models\Auction;
use App\Models\Lot;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\postJson;

it('accepts a valid bid and starts cooldown', function () {
    Queue::fake();

    $user = User::factory()->create(['email_verified_at' => now()]);

    $auction = Auction::factory()->create([
        'state' => AuctionState::Live,
        'live_at' => now()->subMinutes(10),
        'live_ends_at' => now()->addMinutes(50),
    ]);

    $lot = Lot::factory()->create([
        'auction_id' => $auction->id,
        'reserve_price' => 50_000,
        'status' => LotStatus::Open,
    ]);

    actingAs($user);

    $bidAmount = 100_000; // $100.00

    postJson(route('auctions.lots.bid', [
        'lot' => $lot->id,
    ]), [
        'amount' => $bidAmount,
    ])
        ->assertRedirect()
        ->assertSessionHas('success');

    assertDatabaseCount('bids', 0);

    Queue::assertPushed(ProcessBidPlacement::class);
});

it('cancels the bid if auction is not active', function () {
    Queue::fake();

    $user = User::factory()->create(['email_verified_at' => now()]);

    $auction = Auction::factory()->create([
        'state' => AuctionState::Scheduled,
        'live_at' => now()->addMinutes(10),
        'live_ends_at' => now()->addMinutes(50),
    ]);

    $lot = Lot::factory()->create([
        'auction_id' => $auction->id,
        'reserve_price' => 50_000,
        'status' => LotStatus::Open,
    ]);

    actingAs($user);

    $bidAmount = 100_000; // $100.00

    postJson(route('auctions.lots.bid', [
        'lot' => $lot->id,
    ]), [
        'amount' => $bidAmount,
    ])
        ->assertStatus(422);

    assertDatabaseCount('bids', 0);

    Queue::assertNothingPushed();
});

it('cancels the bid if the lot is not accepting bids', function () {
    Queue::fake();

    $user = User::factory()->create(['email_verified_at' => now()]);

    $auction = Auction::factory()->create([
        'state' => AuctionState::Live,
        'live_at' => now()->subMinutes(10),
        'live_ends_at' => now()->addMinutes(50),
    ]);

    $lot = Lot::factory()->create([
        'auction_id' => $auction->id,
        'reserve_price' => 50_000,
        'status' => LotStatus::Sold,
    ]);

    actingAs($user);

    $bidAmount = 100_000; // $100.00

    postJson(route('auctions.lots.bid', [
        'lot' => $lot->id,
    ]), [
        'amount' => $bidAmount,
    ])
        ->assertStatus(422);

    assertDatabaseCount('bids', 0);

    Queue::assertNothingPushed();
});

it('prevents the highest bidder from bidding again', function () {
    Queue::fake();

    $user = User::factory()->create(['email_verified_at' => now()]);

    $auction = Auction::factory()->create([
        'state' => AuctionState::Live,
        'live_at' => now()->subMinutes(10),
        'live_ends_at' => now()->addMinutes(50),
    ]);

    $lot = Lot::factory()->create([
        'auction_id' => $auction->id,
        'reserve_price' => 50_000,
        'status' => LotStatus::Open,
    ]);

    $lot->bids()->create([
        'user_id' => $user->id,
        'amount' => 100_000,
        'status' => BidStatus::Accepted,
        'placed_at' => now()->subSeconds(20),
    ]);

    actingAs($user);

    postJson(route('auctions.lots.bid', [
        'lot' => $lot->id,
    ]), [
        'amount' => 120_000,
    ])
        ->assertStatus(409);

    Queue::assertNothingPushed();
});

it('blocks bids while the user is in cooldown', function () {
    Queue::fake();

    $user = User::factory()->create(['email_verified_at' => now()]);

    $auction = Auction::factory()->create([
        'state' => AuctionState::Live,
        'live_at' => now()->subMinutes(10),
        'live_ends_at' => now()->addDay(),
    ]);

    $lot = Lot::factory()->create([
        'auction_id' => $auction->id,
        'reserve_price' => 50_000,
        'status' => LotStatus::Open,
    ]);

    $lot->bids()->create([
        'user_id' => $user->id,
        'amount' => 100_000,
        'status' => BidStatus::Outbid,
        'placed_at' => now()->subSeconds(5),
    ]);

    $otherUser = User::factory()->create(['email_verified_at' => now()]);

    $lot->bids()->create([
        'user_id' => $otherUser->id,
        'amount' => 150_000,
        'status' => BidStatus::Accepted,
        'placed_at' => now()->subSeconds(2),
    ]);

    actingAs($user);

    postJson(route('auctions.lots.bid', [
        'lot' => $lot->id,
    ]), [
        'amount' => 160_000,
    ])
        ->assertStatus(429);

    Queue::assertNothingPushed();
});

it('prevents placing another bid while the original bid is processing', function () {
    Queue::fake();

    $user = User::factory()->create(['email_verified_at' => now()]);

    $auction = Auction::factory()->create([
        'state' => AuctionState::Live,
        'live_at' => now()->subMinutes(10),
        'live_ends_at' => now()->addMinutes(50),
    ]);

    $lot = Lot::factory()->create([
        'auction_id' => $auction->id,
        'reserve_price' => 50_000,
        'status' => LotStatus::Open,
    ]);

    actingAs($user);

    Cache::put(ProcessBidPlacement::bidInProgressCacheKey($lot, $user), true, now()->addSeconds(60));

    postJson(route('auctions.lots.bid', [
        'lot' => $lot->id,
    ]), [
        'amount' => 100_000,
    ])
        ->assertStatus(409);

    Queue::assertNothingPushed();
});
