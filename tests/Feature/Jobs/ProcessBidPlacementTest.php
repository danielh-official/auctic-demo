<?php

use App\Enums\BidStatus;
use App\Jobs\ProcessBidPlacement;
use App\Models\Bid;
use App\Models\Lot;
use App\Models\User;
use App\Notifications\BidRejected;
use Illuminate\Support\Facades\Notification;

it('fails if first bid is below reserve price', function () {
    Notification::fake();

    $lot = Lot::factory()->open()->create(['reserve_price' => 50000]);
    $user = User::factory()->create();

    $job = new ProcessBidPlacement(
        lot: $lot,
        user: $user,
        amount: 40000,
        placedAt: now()
    );

    $job->handle();

    expect($lot->bids()->count())->toBe(0);
    Notification::assertSentTo($user, BidRejected::class);
});

it('fails if first bid equals reserve price', function () {
    Notification::fake();

    $lot = Lot::factory()->open()->create(['reserve_price' => 50000]);
    $user = User::factory()->create();

    $job = new ProcessBidPlacement(
        lot: $lot,
        user: $user,
        amount: 50000,
        placedAt: now()
    );

    $job->handle();

    expect($lot->bids()->count())->toBe(0);
    Notification::assertSentTo($user, BidRejected::class);
});

it('fails if subsequent bid is below minimum increment', function () {
    Notification::fake();

    $lot = Lot::factory()->open()->create(['reserve_price' => 50000]);
    $previousUser = User::factory()->create();
    $user = User::factory()->create();

    Bid::factory()->for($lot)->for($previousUser)->create([
        'amount' => 100000,
        'status' => BidStatus::Accepted,
    ]);

    $job = new ProcessBidPlacement(
        lot: $lot,
        user: $user,
        amount: 104000,
        placedAt: now()
    );

    $job->handle();

    expect($lot->bids()->count())->toBe(1);
    Notification::assertSentTo($user, BidRejected::class);
});

it('fails if subsequent bid equals minimum required bid', function () {
    Notification::fake();

    $lot = Lot::factory()->open()->create(['reserve_price' => 50000]);
    $previousUser = User::factory()->create();
    $user = User::factory()->create();

    Bid::factory()->for($lot)->for($previousUser)->create([
        'amount' => 100000,
        'status' => BidStatus::Accepted,
    ]);

    $job = new ProcessBidPlacement(
        lot: $lot,
        user: $user,
        amount: 105000,
        placedAt: now()
    );

    $job->handle();

    expect($lot->bids()->count())->toBe(1);
    Notification::assertSentTo($user, BidRejected::class);
});

it('accepts first bid above reserve price and creates accepted bid', function () {
    Notification::fake();

    $lot = Lot::factory()->open()->create(['reserve_price' => 50000]);
    $user = User::factory()->create();

    $job = new ProcessBidPlacement(
        lot: $lot,
        user: $user,
        amount: 60000,
        placedAt: now()
    );

    $job->handle();

    expect($lot->bids()->count())->toBe(1);
    
    $bid = $lot->bids()->first();
    expect($bid->user_id)->toBe($user->id);
    expect($bid->amount)->toBe(60000);
    expect($bid->status)->toBe(BidStatus::Accepted);
    
    Notification::assertNothingSent();
});

it('accepts subsequent valid bid and marks previous bid as outbid', function () {
    Notification::fake();

    $lot = Lot::factory()->open()->create(['reserve_price' => 50000]);
    $previousUser = User::factory()->create();
    $user = User::factory()->create();

    $previousBid = Bid::factory()->for($lot)->for($previousUser)->create([
        'amount' => 100000,
        'status' => BidStatus::Accepted,
    ]);

    $job = new ProcessBidPlacement(
        lot: $lot,
        user: $user,
        amount: 110000,
        placedAt: now()
    );

    $job->handle();

    expect($lot->bids()->count())->toBe(2);
    
    $newBid = $lot->bids()->where('user_id', $user->id)->first();
    expect($newBid->amount)->toBe(110000);
    expect($newBid->status)->toBe(BidStatus::Accepted);
    
    expect($previousBid->fresh()->status)->toBe(BidStatus::Outbid);
    
    Notification::assertNothingSent();
});

it('marks all previous accepted bids as outbid when new bid is placed', function () {
    Notification::fake();

    $lot = Lot::factory()->open()->create(['reserve_price' => 50000]);
    $userA = User::factory()->create();
    $userB = User::factory()->create();
    $userC = User::factory()->create();
    $userD = User::factory()->create();

    $bidA = Bid::factory()->for($lot)->for($userA)->create([
        'amount' => 100000,
        'status' => BidStatus::Accepted,
    ]);
    
    $bidB = Bid::factory()->for($lot)->for($userB)->create([
        'amount' => 120000,
        'status' => BidStatus::Accepted,
    ]);
    
    $bidC = Bid::factory()->for($lot)->for($userC)->create([
        'amount' => 150000,
        'status' => BidStatus::Accepted,
    ]);

    $job = new ProcessBidPlacement(
        lot: $lot,
        user: $userD,
        amount: 200000,
        placedAt: now()
    );

    $job->handle();

    expect($lot->bids()->count())->toBe(4);
    
    $newBid = $lot->bids()->where('user_id', $userD->id)->first();
    expect($newBid->amount)->toBe(200000);
    expect($newBid->status)->toBe(BidStatus::Accepted);
    
    expect($bidA->fresh()->status)->toBe(BidStatus::Outbid);
    expect($bidB->fresh()->status)->toBe(BidStatus::Outbid);
    expect($bidC->fresh()->status)->toBe(BidStatus::Outbid);
    
    Notification::assertNothingSent();
});

it('allows same user to place multiple sequential bids', function () {
    Notification::fake();

    $lot = Lot::factory()->open()->create(['reserve_price' => 50000]);
    $user = User::factory()->create();

    $firstJob = new ProcessBidPlacement(
        lot: $lot,
        user: $user,
        amount: 100000,
        placedAt: now()
    );

    $firstJob->handle();

    $firstBid = $lot->bids()->first();
    expect($firstBid->status)->toBe(BidStatus::Accepted);

    $secondJob = new ProcessBidPlacement(
        lot: $lot,
        user: $user,
        amount: 150000,
        placedAt: now()->addSeconds(1)
    );

    $secondJob->handle();

    expect($lot->bids()->count())->toBe(2);
    
    // Note: The job doesn't outbid same-user bids, only different users
    expect($firstBid->fresh()->status)->toBe(BidStatus::Accepted);
    
    $secondBid = $lot->bids()->where('amount', 150000)->first();
    expect($secondBid->status)->toBe(BidStatus::Accepted);
    expect($secondBid->user_id)->toBe($user->id);
    
    Notification::assertNothingSent();
});

it('does not modify bids already marked as outbid', function () {
    Notification::fake();

    $lot = Lot::factory()->open()->create(['reserve_price' => 50000]);
    $userA = User::factory()->create();
    $userB = User::factory()->create();
    $userC = User::factory()->create();

    $outbidBid = Bid::factory()->for($lot)->for($userA)->outbid()->create([
        'amount' => 80000,
    ]);
    
    $acceptedBid = Bid::factory()->for($lot)->for($userB)->create([
        'amount' => 100000,
        'status' => BidStatus::Accepted,
    ]);

    $job = new ProcessBidPlacement(
        lot: $lot,
        user: $userC,
        amount: 150000,
        placedAt: now()
    );

    $job->handle();

    expect($lot->bids()->count())->toBe(3);
    
    expect($outbidBid->fresh()->status)->toBe(BidStatus::Outbid);
    expect($acceptedBid->fresh()->status)->toBe(BidStatus::Outbid);
    
    $newBid = $lot->bids()->where('user_id', $userC->id)->first();
    expect($newBid->status)->toBe(BidStatus::Accepted);
    
    Notification::assertNothingSent();
});