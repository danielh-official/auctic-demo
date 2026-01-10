<?php

use App\Enums\AuctionState;
use App\Enums\BidStatus;
use App\Jobs\SetHighestAcceptedBid;
use App\Models\Auction;
use App\Models\Bid;
use App\Models\Lot;
use App\Models\User;
use Illuminate\Support\Facades\Queue;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->user = User::factory()->create(['email_verified_at' => now()]);
});

it('allows authenticated user to place a bid on an active auction', function () {
    $auction = Auction::factory()->create([
        'state' => AuctionState::Live,
        'live_at' => now()->subHour(),
        'live_ends_at' => now()->addHour(),
    ]);
    $lot = Lot::factory()->create(['auction_id' => $auction->id]);

    actingAs($this->user)
        ->post(route('auctions.lots.bid', $lot), [
            'amount_cents' => 10000, // $100.00
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($lot->bids()->count())->toBe(1);
    expect($lot->bids()->first())
        ->user_id->toBe($this->user->id)
        ->amount_cents->toBe(10000)
        ->status->toBe(BidStatus::Accepted);
});

it('requires authentication to place a bid', function () {
    $auction = Auction::factory()->create([
        'state' => AuctionState::Live,
        'live_at' => now()->subHour(),
        'live_ends_at' => now()->addHour(),
    ]);
    $lot = Lot::factory()->create(['auction_id' => $auction->id]);

    $this->post(route('auctions.lots.bid', $lot), [
        'amount_cents' => 10000, // $100.00
    ])->assertRedirect(route('login'));
});

it('requires amount_cents field', function () {
    $auction = Auction::factory()->create([
        'state' => AuctionState::Live,
        'live_at' => now()->subHour(),
        'live_ends_at' => now()->addHour(),
    ]);
    $lot = Lot::factory()->create(['auction_id' => $auction->id]);

    actingAs($this->user)
        ->post(route('auctions.lots.bid', $lot), [])
        ->assertInvalid(['amount_cents']);
});

it('requires amount_cents to be numeric', function () {
    $auction = Auction::factory()->create([
        'state' => AuctionState::Live,
        'live_at' => now()->subHour(),
        'live_ends_at' => now()->addHour(),
    ]);
    $lot = Lot::factory()->create(['auction_id' => $auction->id]);

    actingAs($this->user)
        ->post(route('auctions.lots.bid', $lot), [
            'amount_cents' => 'not-a-number',
        ])
        ->assertInvalid(['amount_cents']);
});

it('requires amount_cents to be at least 0.01', function () {
    $auction = Auction::factory()->create([
        'state' => AuctionState::Live,
        'live_at' => now()->subHour(),
        'live_ends_at' => now()->addHour(),
    ]);
    $lot = Lot::factory()->create(['auction_id' => $auction->id]);

    actingAs($this->user)
        ->post(route('auctions.lots.bid', $lot), [
            'amount_cents' => 0,
        ])
        ->assertInvalid(['amount_cents']);
});

it('prevents bidding on an inactive auction', function () {
    $auction = Auction::factory()->create(['state' => AuctionState::Draft]);
    $lot = Lot::factory()->create(['auction_id' => $auction->id]);

    actingAs($this->user)
        ->post(route('auctions.lots.bid', $lot), [
            'amount_cents' => 10000, // $100.00
        ])
        ->assertRedirect()
        ->assertSessionHas('error', 'This auction is not active. You cannot place a bid at this time.');

    expect($lot->bids()->count())->toBe(0);
});

it('prevents bidding lower than or equal to current highest bid', function () {
    $auction = Auction::factory()->create([
        'state' => AuctionState::Live,
        'live_at' => now()->subHour(),
        'live_ends_at' => now()->addHour(),
    ]);
    $lot = Lot::factory()->create(['auction_id' => $auction->id]);
    $otherUser = User::factory()->create();

    // Place initial bid
    Bid::factory()->create([
        'lot_id' => $lot->id,
        'user_id' => $otherUser->id,
        'amount_cents' => 10000, // $100.00
        'status' => BidStatus::Accepted,
    ]);

    // Try to bid same amount
    actingAs($this->user)
        ->post(route('auctions.lots.bid', $lot), [
            'amount_cents' => 10000, // $100.00
        ])
        ->assertRedirect()
        ->assertSessionHas('error', 'Bid amount must be higher than the current highest bid.');

    // Try to bid lower amount
    actingAs($this->user)
        ->post(route('auctions.lots.bid', $lot), [
            'amount_cents' => 9999, // $99.99
        ])
        ->assertRedirect()
        ->assertSessionHas('error', 'Bid amount must be higher than the current highest bid.');

    expect($lot->bids()->count())->toBe(1);
});

it('dispatches job to mark previous bids as outbid when new bid is placed', function () {
    Queue::fake();

    $auction = Auction::factory()->create([
        'state' => AuctionState::Live,
        'live_at' => now()->subHour(),
        'live_ends_at' => now()->addHour(),
    ]);
    $lot = Lot::factory()->create(['auction_id' => $auction->id]);
    $otherUser = User::factory()->create();

    // Place initial bid
    $firstBid = Bid::factory()->create([
        'lot_id' => $lot->id,
        'user_id' => $otherUser->id,
        'amount_cents' => 10000, // $100.00
        'status' => BidStatus::Accepted,
    ]);

    // Place higher bid
    actingAs($this->user)
        ->post(route('auctions.lots.bid', $lot), [
            'amount_cents' => 15000, // $150.00
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    Queue::assertPushed(SetHighestAcceptedBid::class);
});

it('allows multiple sequential bids from different users', function () {
    $auction = Auction::factory()->create([
        'state' => AuctionState::Live,
        'live_at' => now()->subHour(),
        'live_ends_at' => now()->addHour(),
    ]);
    $lot = Lot::factory()->create(['auction_id' => $auction->id]);
    $user2 = User::factory()->create(['email_verified_at' => now()]);
    $user3 = User::factory()->create(['email_verified_at' => now()]);

    // First bid
    actingAs($this->user)
        ->post(route('auctions.lots.bid', $lot), [
            'amount_cents' => 10000, // $100.00
        ]);

    // Second bid
    actingAs($user2)
        ->post(route('auctions.lots.bid', $lot), [
            'amount_cents' => 15000, // $150.00
        ]);

    // Third bid
    actingAs($user3)
        ->post(route('auctions.lots.bid', $lot), [
            'amount_cents' => 20000, // $200.00
        ]);

    expect($lot->bids()->count())->toBe(3);

    $lot->refresh();
    $latestBid = $lot->bids()->orderBy('id', 'desc')->first();
    expect($latestBid->user_id)->toBe($user3->id);
    expect($latestBid->amount_cents)->toBe(20000);
    expect($latestBid->status)->toBe(BidStatus::Accepted);
});

it('does not accept decimal bid amounts', function () {
    $auction = Auction::factory()->create([
        'state' => AuctionState::Live,
        'live_at' => now()->subHour(),
        'live_ends_at' => now()->addHour(),
    ]);
    $lot = Lot::factory()->create(['auction_id' => $auction->id]);

    actingAs($this->user)
        ->post(route('auctions.lots.bid', $lot), [
            'amount_cents' => 99.99, // $.9999
        ])
        ->assertRedirect()
        ->assertSessionHasErrors(['amount_cents']);

    expect($lot->bids()->first())->toBeNull();
});

it('prevents user from bidding if they are already the highest bidder', function () {
    $auction = Auction::factory()->create([
        'state' => AuctionState::Live,
        'live_at' => now()->subHour(),
        'live_ends_at' => now()->addHour(),
    ]);
    $lot = Lot::factory()->create(['auction_id' => $auction->id]);

    // Place initial bid from this user
    Bid::factory()->create([
        'lot_id' => $lot->id,
        'user_id' => $this->user->id,
        'amount_cents' => 10000, // $100.00
        'status' => BidStatus::Accepted,
    ]);

    // Try to bid again as same user
    actingAs($this->user)
        ->post(route('auctions.lots.bid', $lot), [
            'amount_cents' => 15000, // $150.00
        ])
        ->assertRedirect()
        ->assertSessionHas('error', 'You are already the highest bidder. You cannot place another bid on this lot at this time.');

    expect($lot->bids()->count())->toBe(1);
});

it('prevents race conditions when two users bid simultaneously', function () {
    $auction = Auction::factory()->create([
        'state' => AuctionState::Live,
        'live_at' => now()->subHour(),
        'live_ends_at' => now()->addHour(),
    ]);
    $lot = Lot::factory()->create(['auction_id' => $auction->id]);
    
    // Create initial bid
    Bid::factory()->create([
        'lot_id' => $lot->id,
        'user_id' => $this->user->id,
        'amount_cents' => 10000, // $100.00
        'status' => BidStatus::Accepted,
    ]);

    $user2 = User::factory()->create(['email_verified_at' => now()]);
    $user3 = User::factory()->create(['email_verified_at' => now()]);

    // Simulate concurrent bids by using database transactions
    // Both users try to bid 15000 at the "same time" (reading the same highest bid of 10000)
    $exceptions = [];
    $successCount = 0;

    try {
        DB::beginTransaction();
        
        // User 2 places bid
        actingAs($user2)
            ->post(route('auctions.lots.bid', $lot), [
                'amount_cents' => 15000,
            ]);
        
        $successCount++;
        DB::commit();
    } catch (\Exception $e) {
        DB::rollBack();
        $exceptions[] = $e;
    }

    try {
        DB::beginTransaction();
        
        // User 3 tries to place same bid amount
        actingAs($user3)
            ->post(route('auctions.lots.bid', $lot), [
                'amount_cents' => 15000,
            ]);
        
        $successCount++;
        DB::commit();
    } catch (\Exception $e) {
        DB::rollBack();
        $exceptions[] = $e;
    }

    // Refresh and verify only valid bids were created
    $lot->refresh();
    $bids = $lot->bids()->orderBy('amount_cents', 'asc')->get();
    
    // We should have exactly 2 bids: the initial 10000 and one 15000
    // The second 15000 should have been rejected
    expect($bids->count())->toBe(2);
    expect($bids->pluck('amount_cents')->toArray())->toBe([10000, 15000]);
    
    // The highest bid should be 15000
    $highestBid = $lot->bids()->orderBy('amount_cents', 'desc')->first();
    expect($highestBid->amount_cents)->toBe(15000);
    expect($highestBid->user_id)->toBeIn([$user2->id, $user3->id]);
});
