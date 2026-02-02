<?php

use App\Enums\AuctionState;
use App\Enums\LotStatus;
use App\Jobs\ProcessBidPlacement;
use App\Models\Auction;
use App\Models\Lot;
use App\Models\User;
use Illuminate\Support\Facades\Queue;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\postJson;

beforeEach(function () {
    $this->user = User::factory()->create(['email_verified_at' => now()]);
});

it('accepts a valid bid and starts cooldown', function () {
    Queue::fake();

    $auction = Auction::factory()->create([
        'state' => AuctionState::Live,
        'live_at' => now()->subMinutes(10),
        'live_ends_at' => now()->addMinutes(50),
    ]);

    $lot = Lot::factory()->create([
        'auction_id' => $auction->id,
        'reserve_price' => 50000,
        'status' => LotStatus::Open,
    ]);

    actingAs($this->user);

    $bidAmount = 100000; // $1,000.00

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

    $auction = Auction::factory()->create([
        'state' => AuctionState::Scheduled,
        'live_at' => now()->addMinutes(10),
        'live_ends_at' => now()->addMinutes(50),
    ]);

    $lot = Lot::factory()->create([
        'auction_id' => $auction->id,
        'reserve_price' => 50000,
        'status' => LotStatus::Open,
    ]);

    actingAs($this->user);

    $bidAmount = 100000; // $1,000.00

    postJson(route('auctions.lots.bid', [
        'lot' => $lot->id,
    ]), [
        'amount' => $bidAmount,
    ])
        ->assertRedirect()
        ->assertSessionHas('error', 'This auction is not active. You cannot place a bid at this time.');

    assertDatabaseCount('bids', 0);

    Queue::assertNothingPushed();
});

it('cancels the bid if the lot is not accepting bids', function () {
    Queue::fake();
    
    $auction = Auction::factory()->create([
        'state' => AuctionState::Live,
        'live_at' => now()->subMinutes(10),
        'live_ends_at' => now()->addMinutes(50),
    ]);

    $lot = Lot::factory()->create([
        'auction_id' => $auction->id,
        'reserve_price' => 50000,
        'status' => LotStatus::Sold,
    ]);

    actingAs($this->user);

    $bidAmount = 100000; // $1,000.00

    postJson(route('auctions.lots.bid', [
        'lot' => $lot->id,
    ]), [
        'amount' => $bidAmount,
    ])
        ->assertRedirect()
        ->assertSessionHas('error', 'This lot is not accepting bids at this time.');

    assertDatabaseCount('bids', 0);

    Queue::assertNothingPushed();
});