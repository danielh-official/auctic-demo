<?php

use App\Models\Auction;
use App\Models\User;

test('any user can view any auctions', function () {
    $user = User::factory()->create();

    expect($user->can('viewAny', Auction::class))->toBeTrue();
});

test('owner can view their auction', function () {
    $owner = User::factory()->create();
    $auction = Auction::factory()->for($owner, 'owner')->create();

    expect($owner->can('view', $auction))->toBeTrue();
});

test('admin can view any auction', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $otherUser = User::factory()->create();
    $auction = Auction::factory()->for($otherUser, 'owner')->create();

    expect($admin->can('view', $auction))->toBeTrue();
});

test('non-owner cannot view another users auction', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $auction = Auction::factory()->for($owner, 'owner')->create();

    expect($otherUser->can('view', $auction))->toBeFalse();
});

test('any authenticated user can create auction', function () {
    $user = User::factory()->create();

    expect($user->can('create', Auction::class))->toBeTrue();
});

test('owner can update their auction', function () {
    $owner = User::factory()->create();
    $auction = Auction::factory()->for($owner, 'owner')->create();

    expect($owner->can('update', $auction))->toBeTrue();
});

test('admin can update any auction', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $otherUser = User::factory()->create();
    $auction = Auction::factory()->for($otherUser, 'owner')->create();

    expect($admin->can('update', $auction))->toBeTrue();
});

test('non-owner cannot update another users auction', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $auction = Auction::factory()->for($owner, 'owner')->create();

    expect($otherUser->can('update', $auction))->toBeFalse();
});

test('owner can delete their auction', function () {
    $owner = User::factory()->create();
    $auction = Auction::factory()->for($owner, 'owner')->create();

    expect($owner->can('delete', $auction))->toBeTrue();
});

test('admin can delete any auction', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $otherUser = User::factory()->create();
    $auction = Auction::factory()->for($otherUser, 'owner')->create();

    expect($admin->can('delete', $auction))->toBeTrue();
});

test('non-owner cannot delete another users auction', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $auction = Auction::factory()->for($owner, 'owner')->create();

    expect($otherUser->can('delete', $auction))->toBeFalse();
});

test('owner can restore their auction', function () {
    $owner = User::factory()->create();
    $auction = Auction::factory()->for($owner, 'owner')->create();

    expect($owner->can('restore', $auction))->toBeTrue();
});

test('admin can restore any auction', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $otherUser = User::factory()->create();
    $auction = Auction::factory()->for($otherUser, 'owner')->create();

    expect($admin->can('restore', $auction))->toBeTrue();
});

test('non-owner cannot restore another users auction', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $auction = Auction::factory()->for($owner, 'owner')->create();

    expect($otherUser->can('restore', $auction))->toBeFalse();
});

test('only admin can force delete auction', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $owner = User::factory()->create();
    $auction = Auction::factory()->for($owner, 'owner')->create();

    expect($admin->can('forceDelete', $auction))->toBeTrue();
    expect($owner->can('forceDelete', $auction))->toBeFalse();
});
