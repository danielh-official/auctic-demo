<?php

use App\Models\Ban;
use App\Models\User;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->owner = User::factory()->create(['email_verified_at' => now()]);
});

it('successfully bans multiple users from all auctions', function () {
    $usersToBan = User::factory()->count(3)->create(['email_verified_at' => now()]);
    $userIds = $usersToBan->pluck('id')->toArray();

    actingAs($this->owner)
        ->post(route('my.auctions.all.ban-participants'), [
            'user_ids' => $userIds,
        ])
        ->assertRedirect()
        ->assertSessionHas('success', 'Selected users have been banned from all your auctions successfully.');

    foreach ($userIds as $userId) {
        expect(Ban::where('owner_id', $this->owner->id)
            ->where('banned_user_id', $userId)
            ->exists())->toBeTrue();
    }
});

it('requires authentication to ban users', function () {
    $userToBan = User::factory()->create(['email_verified_at' => now()]);

    $this->post(route('my.auctions.all.ban-participants'), [
        'user_ids' => [$userToBan->id],
    ])->assertRedirect(route('login'));
});

it('requires user_ids field', function () {
    actingAs($this->owner)
        ->post(route('my.auctions.all.ban-participants'), [])
        ->assertInvalid(['user_ids']);
});

it('requires user_ids to be an array', function () {
    actingAs($this->owner)
        ->post(route('my.auctions.all.ban-participants'), [
            'user_ids' => 'not-an-array',
        ])
        ->assertInvalid(['user_ids']);
});

it('requires each user_id to be an integer', function () {
    actingAs($this->owner)
        ->post(route('my.auctions.all.ban-participants'), [
            'user_ids' => ['not-an-integer', 123],
        ])
        ->assertInvalid(['user_ids.0']);
});

it('requires each user_id to exist in users table', function () {
    $validUser = User::factory()->create(['email_verified_at' => now()]);

    actingAs($this->owner)
        ->post(route('my.auctions.all.ban-participants'), [
            'user_ids' => [$validUser->id, 99999], // 99999 doesn't exist
        ])
        ->assertInvalid(['user_ids.1']);
});

it('prevents user from banning themselves', function () {
    $otherUser = User::factory()->create(['email_verified_at' => now()]);

    actingAs($this->owner)
        ->post(route('my.auctions.all.ban-participants'), [
            'user_ids' => [$otherUser->id, $this->owner->id],
        ])
        ->assertInvalid(['user_ids.1']);
});

it('requires user_ids to be distinct', function () {
    $userToBan = User::factory()->create(['email_verified_at' => now()]);

    actingAs($this->owner)
        ->post(route('my.auctions.all.ban-participants'), [
            'user_ids' => [$userToBan->id, $userToBan->id], // Duplicate
        ])
        ->assertInvalid(['user_ids.1']);
});

it('updates existing ban records instead of creating duplicates', function () {
    $userToBan = User::factory()->create(['email_verified_at' => now()]);

    // Create an initial ban
    $ban = Ban::create([
        'owner_id' => $this->owner->id,
        'banned_user_id' => $userToBan->id,
    ]);

    $originalUpdatedAt = $ban->updated_at;

    // Travel forward in time to ensure updated_at changes
    $this->travel(1)->second();

    // Ban the same user again
    actingAs($this->owner)
        ->post(route('my.auctions.all.ban-participants'), [
            'user_ids' => [$userToBan->id],
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    // Should still be only one ban record
    expect(Ban::where('owner_id', $this->owner->id)
        ->where('banned_user_id', $userToBan->id)
        ->count())->toBe(1);

    // But updated_at should be different
    expect($ban->fresh()->updated_at->greaterThan($originalUpdatedAt))->toBeTrue();
});

it('only creates bans for the authenticated owner', function () {
    $anotherOwner = User::factory()->create(['email_verified_at' => now()]);
    $userToBan = User::factory()->create(['email_verified_at' => now()]);

    actingAs($this->owner)
        ->post(route('my.auctions.all.ban-participants'), [
            'user_ids' => [$userToBan->id],
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    // Ban should belong to the authenticated owner
    expect(Ban::where('owner_id', $this->owner->id)
        ->where('banned_user_id', $userToBan->id)
        ->exists())->toBeTrue();

    // Ban should NOT belong to another owner
    expect(Ban::where('owner_id', $anotherOwner->id)
        ->where('banned_user_id', $userToBan->id)
        ->exists())->toBeFalse();
});

it('handles banning a single user', function () {
    $userToBan = User::factory()->create(['email_verified_at' => now()]);

    actingAs($this->owner)
        ->post(route('my.auctions.all.ban-participants'), [
            'user_ids' => [$userToBan->id],
        ])
        ->assertRedirect()
        ->assertSessionHas('success', 'Selected users have been banned from all your auctions successfully.');

    expect(Ban::where('owner_id', $this->owner->id)
        ->where('banned_user_id', $userToBan->id)
        ->exists())->toBeTrue();
});

it('rejects empty user_ids array', function () {
    actingAs($this->owner)
        ->post(route('my.auctions.all.ban-participants'), [
            'user_ids' => [],
        ])
        ->assertInvalid(['user_ids']);

    expect(Ban::where('owner_id', $this->owner->id)->count())->toBe(0);
});
