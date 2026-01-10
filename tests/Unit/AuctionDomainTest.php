<?php

use App\Models\Bid;
use App\Models\Lot;
use App\Models\Settlement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('creates settlement with winning bid and totals', function () {
    $settlement = Settlement::factory()->create();

    $settlement->load('winningBid');

    expect($settlement->winningBid)->not->toBeNull()
        ->and($settlement->buyer_premium_cents)->toBeInt()
        ->and($settlement->total_cents)->toBe($settlement->winningBid->amount_cents + $settlement->buyer_premium_cents);
});

it('returns highest bid as winning bid', function () {
    $lot = Lot::factory()->open()->create();

    Bid::factory()->for($lot)->create(['amount_cents' => 25_000]);
    Bid::factory()->for($lot)->create(['amount_cents' => 40_000]);

    $lot->load('winningBid');

    expect($lot->winningBid->amount_cents)->toBe(40_000);
});
