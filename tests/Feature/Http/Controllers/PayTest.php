<?php

use App\Enums\AuctionBillStatus;
use App\Models\Auction;
use App\Models\AuctionBill;
use App\Models\User;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->auction = Auction::factory()->create();
});

it('successfully processes payment for an unpaid bill', function () {
    $bill = AuctionBill::factory()->create([
        'auction_id' => $this->auction->id,
        'user_id' => $this->user->id,
        'status' => AuctionBillStatus::Unpaid,
        'total_amount' => 10000,
        'paid_amount' => 0,
    ]);

    $response = actingAs($this->user)
        ->post(route('auctions.pay', $this->auction), [
            'payment_method' => 'credit_card',
            'payment_reference' => 'REF123456',
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('message', 'Payment successful. Thank you!');

    $bill->refresh();
    expect($bill->status)->toBe(AuctionBillStatus::Paid)
        ->and($bill->paid_amount)->toBe(10000)
        ->and($bill->payment_method)->toBe('credit_card')
        ->and($bill->payment_reference)->toBe('REF123456');
});

it('prevents payment of already paid bill', function () {
    $bill = AuctionBill::factory()->create([
        'auction_id' => $this->auction->id,
        'user_id' => $this->user->id,
        'status' => AuctionBillStatus::Paid,
        'total_amount' => 10000,
        'paid_amount' => 10000,
        'payment_method' => 'credit_card',
        'payment_reference' => 'OLD_REF',
    ]);

    $response = actingAs($this->user)
        ->post(route('auctions.pay', $this->auction), [
            'payment_method' => 'paypal',
            'payment_reference' => 'NEW_REF',
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('message', 'This bill has already been paid.');

    $bill->refresh();
    expect($bill->payment_method)->toBe('credit_card')
        ->and($bill->payment_reference)->toBe('OLD_REF');
});

it('requires payment_method field', function () {
    AuctionBill::factory()->create([
        'auction_id' => $this->auction->id,
        'user_id' => $this->user->id,
        'status' => AuctionBillStatus::Unpaid,
    ]);

    $response = actingAs($this->user)
        ->post(route('auctions.pay', $this->auction), [
            'payment_reference' => 'REF123456',
        ]);

    $response->assertSessionHasErrors('payment_method');
});

it('requires payment_reference field', function () {
    AuctionBill::factory()->create([
        'auction_id' => $this->auction->id,
        'user_id' => $this->user->id,
        'status' => AuctionBillStatus::Unpaid,
    ]);

    $response = actingAs($this->user)
        ->post(route('auctions.pay', $this->auction), [
            'payment_method' => 'credit_card',
        ]);

    $response->assertSessionHasErrors('payment_reference');
});

it('returns 404 when bill does not exist for user', function () {
    $otherUser = User::factory()->create();

    AuctionBill::factory()->create([
        'auction_id' => $this->auction->id,
        'user_id' => $otherUser->id,
        'status' => AuctionBillStatus::Unpaid,
    ]);

    $response = actingAs($this->user)
        ->post(route('auctions.pay', $this->auction), [
            'payment_method' => 'credit_card',
            'payment_reference' => 'REF123456',
        ]);

    $response->assertNotFound();
});

it('requires authentication', function () {
    AuctionBill::factory()->create([
        'auction_id' => $this->auction->id,
        'user_id' => $this->user->id,
        'status' => AuctionBillStatus::Unpaid,
    ]);

    $response = $this->post(route('auctions.pay', $this->auction), [
        'payment_method' => 'credit_card',
        'payment_reference' => 'REF123456',
    ]);

    $response->assertRedirect(route('login'));
});

it('handles different payment methods correctly', function (string $method) {
    $bill = AuctionBill::factory()->create([
        'auction_id' => $this->auction->id,
        'user_id' => $this->user->id,
        'status' => AuctionBillStatus::Unpaid,
        'total_amount' => 5000,
    ]);

    $response = actingAs($this->user)
        ->post(route('auctions.pay', $this->auction), [
            'payment_method' => $method,
            'payment_reference' => 'REF_'.strtoupper($method),
        ]);

    $response->assertRedirect();

    $bill->refresh();
    expect($bill->status)->toBe(AuctionBillStatus::Paid)
        ->and($bill->payment_method)->toBe($method);
})->with([
    'credit_card',
    'paypal',
    'bank_transfer',
    'check',
]);
