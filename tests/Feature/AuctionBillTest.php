<?php

use App\Enums\AuctionBillStatus;
use App\Models\Auction;
use App\Models\AuctionBill;
use App\Models\User;

test('auction bill can be created with correct attributes', function () {
    $auction = Auction::factory()->create();
    $user = User::factory()->create();

    $bill = AuctionBill::factory()->create([
        'auction_id' => $auction->id,
        'user_id' => $user->id,
        'subtotal_cents' => 100000,
        'buyer_premium_cents' => 15000,
        'tax_cents' => 9200,
        'total_cents' => 124200,
        'paid_cents' => 0,
        'status' => AuctionBillStatus::Unpaid,
    ]);

    expect($bill->subtotal_cents)->toBe(100000)
        ->and($bill->buyer_premium_cents)->toBe(15000)
        ->and($bill->tax_cents)->toBe(9200)
        ->and($bill->total_cents)->toBe(124200)
        ->and($bill->paid_cents)->toBe(0)
        ->and($bill->status)->toBe(AuctionBillStatus::Unpaid);
});

test('auction bill belongs to an auction', function () {
    $auction = Auction::factory()->create();
    $bill = AuctionBill::factory()->create(['auction_id' => $auction->id]);

    expect($bill->auction)->toBeInstanceOf(Auction::class)
        ->and($bill->auction->id)->toBe($auction->id);
});

test('auction bill belongs to a user', function () {
    $user = User::factory()->create();
    $bill = AuctionBill::factory()->create(['user_id' => $user->id]);

    expect($bill->user)->toBeInstanceOf(User::class)
        ->and($bill->user->id)->toBe($user->id);
});

test('remaining balance is calculated correctly', function () {
    $bill = AuctionBill::factory()->create([
        'total_cents' => 100000,
        'paid_cents' => 30000,
    ]);

    expect($bill->remainingBalanceCents())->toBe(70000);
});

test('remaining balance is zero when fully paid', function () {
    $bill = AuctionBill::factory()->create([
        'total_cents' => 100000,
        'paid_cents' => 100000,
    ]);

    expect($bill->remainingBalanceCents())->toBe(0);
});

test('remaining balance cannot be negative', function () {
    $bill = AuctionBill::factory()->create([
        'total_cents' => 100000,
        'paid_cents' => 120000,
    ]);

    expect($bill->remainingBalanceCents())->toBe(0);
});

test('is fully paid returns true when paid amount equals total', function () {
    $bill = AuctionBill::factory()->create([
        'total_cents' => 100000,
        'paid_cents' => 100000,
    ]);

    expect($bill->isFullyPaid())->toBeTrue();
});

test('is fully paid returns true when paid amount exceeds total', function () {
    $bill = AuctionBill::factory()->create([
        'total_cents' => 100000,
        'paid_cents' => 110000,
    ]);

    expect($bill->isFullyPaid())->toBeTrue();
});

test('is fully paid returns false when partially paid', function () {
    $bill = AuctionBill::factory()->create([
        'total_cents' => 100000,
        'paid_cents' => 50000,
    ]);

    expect($bill->isFullyPaid())->toBeFalse();
});

test('is overdue returns true when past due date and not fully paid', function () {
    $bill = AuctionBill::factory()->create([
        'total_cents' => 100000,
        'paid_cents' => 50000,
        'due_at' => now()->subDays(5),
    ]);

    expect($bill->isOverdue())->toBeTrue();
});

test('is overdue returns false when past due date but fully paid', function () {
    $bill = AuctionBill::factory()->create([
        'total_cents' => 100000,
        'paid_cents' => 100000,
        'due_at' => now()->subDays(5),
    ]);

    expect($bill->isOverdue())->toBeFalse();
});

test('is overdue returns false when not yet due', function () {
    $bill = AuctionBill::factory()->create([
        'total_cents' => 100000,
        'paid_cents' => 50000,
        'due_at' => now()->addDays(5),
    ]);

    expect($bill->isOverdue())->toBeFalse();
});

test('is overdue returns false when no due date set', function () {
    $bill = AuctionBill::factory()->create([
        'total_cents' => 100000,
        'paid_cents' => 50000,
        'due_at' => null,
    ]);

    expect($bill->isOverdue())->toBeFalse();
});

test('auction bill enforces unique constraint on auction and user', function () {
    $auction = Auction::factory()->create();
    $user = User::factory()->create();

    AuctionBill::factory()->create([
        'auction_id' => $auction->id,
        'user_id' => $user->id,
    ]);

    expect(fn () => AuctionBill::factory()->create([
        'auction_id' => $auction->id,
        'user_id' => $user->id,
    ]))->toThrow(\Exception::class);
});

test('user can have multiple bills from different auctions', function () {
    $user = User::factory()->create();
    $auction1 = Auction::factory()->create();
    $auction2 = Auction::factory()->create();

    $bill1 = AuctionBill::factory()->create([
        'auction_id' => $auction1->id,
        'user_id' => $user->id,
    ]);

    $bill2 = AuctionBill::factory()->create([
        'auction_id' => $auction2->id,
        'user_id' => $user->id,
    ]);

    expect($user->bills)->toHaveCount(2);
});

test('auction can have multiple bills from different users', function () {
    $auction = Auction::factory()->create();
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $bill1 = AuctionBill::factory()->create([
        'auction_id' => $auction->id,
        'user_id' => $user1->id,
    ]);

    $bill2 = AuctionBill::factory()->create([
        'auction_id' => $auction->id,
        'user_id' => $user2->id,
    ]);

    expect($auction->bills)->toHaveCount(2);
});
