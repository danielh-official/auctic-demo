<?php

use App\Enums\RegistrationStatus;
use App\Models\Auction;
use App\Models\AuctionRegistration;
use App\Models\User;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->owner = User::factory()->create(['email_verified_at' => now()]);
    $this->auction = Auction::factory()->for($this->owner, 'owner')->create();
});

it('successfully invites multiple users to an auction', function () {
    $usersToInvite = User::factory()->count(3)->create(['email_verified_at' => now()]);
    $userIds = $usersToInvite->pluck('id')->toArray();

    actingAs($this->owner)
        ->post(route('my.auctions.invite-users', $this->auction), [
            'user_ids' => $userIds,
        ])
        ->assertRedirect(route('auctions.show', $this->auction))
        ->assertSessionHas('success', 'You have successfully invited users.');

    foreach ($userIds as $userId) {
        $registration = AuctionRegistration::where('auction_id', $this->auction->id)
            ->where('user_id', $userId)
            ->first();

        expect($registration)->not->toBeNull();
        expect($registration->status)->toBe(RegistrationStatus::Invited);
    }
});

it('requires authentication to invite users to an auction', function () {
    $userToInvite = User::factory()->create(['email_verified_at' => now()]);

    $this->post(route('my.auctions.invite-users', $this->auction), [
        'user_ids' => [$userToInvite->id],
    ])->assertRedirect(route('login'));
});

it('prevents non-owners from inviting users to an auction', function () {
    $nonOwner = User::factory()->create(['email_verified_at' => now()]);
    $userToInvite = User::factory()->create(['email_verified_at' => now()]);

    actingAs($nonOwner)
        ->post(route('my.auctions.invite-users', $this->auction), [
            'user_ids' => [$userToInvite->id],
        ])
        ->assertForbidden();
});

it('requires user_ids field', function () {
    actingAs($this->owner)
        ->post(route('my.auctions.invite-users', $this->auction), [])
        ->assertInvalid(['user_ids']);
});

it('requires user_ids to be an array', function () {
    actingAs($this->owner)
        ->post(route('my.auctions.invite-users', $this->auction), [
            'user_ids' => 'not-an-array',
        ])
        ->assertInvalid(['user_ids']);
});

it('requires each user_id to exist in users table', function () {
    $validUser = User::factory()->create(['email_verified_at' => now()]);

    actingAs($this->owner)
        ->post(route('my.auctions.invite-users', $this->auction), [
            'user_ids' => [$validUser->id, 99999], // 99999 doesn't exist
        ])
        ->assertInvalid(['user_ids.1']);
});

it('prevents owner from inviting themselves', function () {
    $otherUser = User::factory()->create(['email_verified_at' => now()]);

    actingAs($this->owner)
        ->post(route('my.auctions.invite-users', $this->auction), [
            'user_ids' => [$otherUser->id, $this->owner->id],
        ])
        ->assertRedirect(route('auctions.show', $this->auction))
        ->assertSessionHas('error', 'You cannot invite yourself to your own auction. One of given user IDs is the owner of the auction.');

    // Verify the other user was not invited either
    expect(AuctionRegistration::where('auction_id', $this->auction->id)
        ->where('user_id', $otherUser->id)
        ->exists())->toBeFalse();
});

it('prevents inviting users who are already registered', function () {
    $existingUser = User::factory()->create(['email_verified_at' => now()]);
    $newUser = User::factory()->create(['email_verified_at' => now()]);

    // Create an existing registration with 'approved' status
    AuctionRegistration::create([
        'auction_id' => $this->auction->id,
        'user_id' => $existingUser->id,
        'status' => RegistrationStatus::Approved,
    ]);

    actingAs($this->owner)
        ->post(route('my.auctions.invite-users', $this->auction), [
            'user_ids' => [$existingUser->id, $newUser->id],
        ])
        ->assertRedirect(route('auctions.show', $this->auction))
        ->assertSessionHas('error', 'One or more users are already registered or invited to this auction.');

    // Verify the existing registration wasn't updated
    $existingRegistration = AuctionRegistration::where('auction_id', $this->auction->id)
        ->where('user_id', $existingUser->id)
        ->first();
    expect($existingRegistration->status)->toBe(RegistrationStatus::Approved);

    // Verify the new user was not invited
    expect(AuctionRegistration::where('auction_id', $this->auction->id)
        ->where('user_id', $newUser->id)
        ->exists())->toBeFalse();
});

it('prevents inviting users who are already invited', function () {
    $existingUser = User::factory()->create(['email_verified_at' => now()]);
    $newUser = User::factory()->create(['email_verified_at' => now()]);

    // Create an existing registration with 'invited' status
    AuctionRegistration::create([
        'auction_id' => $this->auction->id,
        'user_id' => $existingUser->id,
        'status' => RegistrationStatus::Invited,
    ]);

    actingAs($this->owner)
        ->post(route('my.auctions.invite-users', $this->auction), [
            'user_ids' => [$existingUser->id, $newUser->id],
        ])
        ->assertRedirect(route('auctions.show', $this->auction))
        ->assertSessionHas('error', 'One or more users are already registered or invited to this auction.');

    // Verify the new user was not invited
    expect(AuctionRegistration::where('auction_id', $this->auction->id)
        ->where('user_id', $newUser->id)
        ->exists())->toBeFalse();
});

it('prevents inviting users who are banned', function () {
    $bannedUser = User::factory()->create(['email_verified_at' => now()]);
    $newUser = User::factory()->create(['email_verified_at' => now()]);

    // Create an existing registration with 'banned' status
    AuctionRegistration::create([
        'auction_id' => $this->auction->id,
        'user_id' => $bannedUser->id,
        'status' => RegistrationStatus::Banned,
    ]);

    actingAs($this->owner)
        ->post(route('my.auctions.invite-users', $this->auction), [
            'user_ids' => [$bannedUser->id, $newUser->id],
        ])
        ->assertRedirect(route('auctions.show', $this->auction))
        ->assertSessionHas('error', 'One or more users are already registered or invited to this auction.');

    // Verify the banned user status wasn't changed
    $bannedRegistration = AuctionRegistration::where('auction_id', $this->auction->id)
        ->where('user_id', $bannedUser->id)
        ->first();
    expect($bannedRegistration->status)->toBe(RegistrationStatus::Banned);

    // Verify the new user was not invited
    expect(AuctionRegistration::where('auction_id', $this->auction->id)
        ->where('user_id', $newUser->id)
        ->exists())->toBeFalse();
});

it('invites a single user successfully', function () {
    $userToInvite = User::factory()->create(['email_verified_at' => now()]);

    actingAs($this->owner)
        ->post(route('my.auctions.invite-users', $this->auction), [
            'user_ids' => [$userToInvite->id],
        ])
        ->assertRedirect(route('auctions.show', $this->auction))
        ->assertSessionHas('success', 'You have successfully invited users.');

    $registration = AuctionRegistration::where('auction_id', $this->auction->id)
        ->where('user_id', $userToInvite->id)
        ->first();

    expect($registration)->not->toBeNull();
    expect($registration->status)->toBe(RegistrationStatus::Invited);
});

it('does not affect other auction registrations when inviting users', function () {
    $otherAuction = Auction::factory()->for($this->owner, 'owner')->create();
    $userInOtherAuction = User::factory()->create(['email_verified_at' => now()]);
    $userToInvite = User::factory()->create(['email_verified_at' => now()]);

    // Create a registration in another auction
    AuctionRegistration::create([
        'auction_id' => $otherAuction->id,
        'user_id' => $userInOtherAuction->id,
        'status' => RegistrationStatus::Approved,
    ]);

    actingAs($this->owner)
        ->post(route('my.auctions.invite-users', $this->auction), [
            'user_ids' => [$userToInvite->id],
        ])
        ->assertRedirect(route('auctions.show', $this->auction))
        ->assertSessionHas('success');

    // Verify the other auction's registration is unchanged
    $otherRegistration = AuctionRegistration::where('auction_id', $otherAuction->id)
        ->where('user_id', $userInOtherAuction->id)
        ->first();
    expect($otherRegistration->status)->toBe(RegistrationStatus::Approved);
});
