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

it('successfully bans multiple users from a specific auction', function () {
    $usersToBan = User::factory()->count(3)->create(['email_verified_at' => now()]);
    $userIds = $usersToBan->pluck('id')->toArray();

    actingAs($this->owner)
        ->post(route('my.auctions.ban-users', $this->auction), [
            'user_ids' => $userIds,
        ])
        ->assertRedirect()
        ->assertSessionHas('success', "Selected users have been banned from your auction {$this->auction->title} successfully.");

    foreach ($userIds as $userId) {
        $registration = AuctionRegistration::where('auction_id', $this->auction->id)
            ->where('user_id', $userId)
            ->first();

        expect($registration)->not->toBeNull();
        expect($registration->status)->toBe(RegistrationStatus::Banned);
    }
});

it('requires authentication to ban users from an auction', function () {
    $userToBan = User::factory()->create(['email_verified_at' => now()]);

    $this->post(route('my.auctions.ban-users', $this->auction), [
        'user_ids' => [$userToBan->id],
    ])->assertRedirect(route('login'));
});

it('prevents non-owners from banning users from an auction', function () {
    $nonOwner = User::factory()->create(['email_verified_at' => now()]);
    $userToBan = User::factory()->create(['email_verified_at' => now()]);

    actingAs($nonOwner)
        ->post(route('my.auctions.ban-users', $this->auction), [
            'user_ids' => [$userToBan->id],
        ])
        ->assertForbidden();
});

it('requires user_ids field', function () {
    actingAs($this->owner)
        ->post(route('my.auctions.ban-users', $this->auction), [])
        ->assertInvalid(['user_ids']);
});

it('requires user_ids to be an array', function () {
    actingAs($this->owner)
        ->post(route('my.auctions.ban-users', $this->auction), [
            'user_ids' => 'not-an-array',
        ])
        ->assertInvalid(['user_ids']);
});

it('requires each user_id to be an integer', function () {
    actingAs($this->owner)
        ->post(route('my.auctions.ban-users', $this->auction), [
            'user_ids' => ['not-an-integer', 123],
        ])
        ->assertInvalid(['user_ids.0']);
});

it('requires each user_id to exist in users table', function () {
    $validUser = User::factory()->create(['email_verified_at' => now()]);

    actingAs($this->owner)
        ->post(route('my.auctions.ban-users', $this->auction), [
            'user_ids' => [$validUser->id, 99999], // 99999 doesn't exist
        ])
        ->assertInvalid(['user_ids.1']);
});

it('prevents user from banning themselves', function () {
    $otherUser = User::factory()->create(['email_verified_at' => now()]);

    actingAs($this->owner)
        ->post(route('my.auctions.ban-users', $this->auction), [
            'user_ids' => [$otherUser->id, $this->owner->id],
        ])
        ->assertInvalid(['user_ids.1']);
});

it('requires user_ids to be distinct', function () {
    $userToBan = User::factory()->create(['email_verified_at' => now()]);

    actingAs($this->owner)
        ->post(route('my.auctions.ban-users', $this->auction), [
            'user_ids' => [$userToBan->id, $userToBan->id], // Duplicate
        ])
        ->assertInvalid(['user_ids.1']);
});

it('updates existing registration status to banned instead of creating duplicates', function () {
    $userToBan = User::factory()->create(['email_verified_at' => now()]);

    // Create an initial registration with 'invited' status
    $registration = AuctionRegistration::create([
        'auction_id' => $this->auction->id,
        'user_id' => $userToBan->id,
        'status' => RegistrationStatus::Invited,
    ]);

    actingAs($this->owner)
        ->post(route('my.auctions.ban-users', $this->auction), [
            'user_ids' => [$userToBan->id],
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    // Should still be only one registration record
    expect(AuctionRegistration::where('auction_id', $this->auction->id)
        ->where('user_id', $userToBan->id)
        ->count())->toBe(1);

    // Status should be updated to banned
    $registration->refresh();
    expect($registration->status)->toBe(RegistrationStatus::Banned);
});

it('does not remove existing registrations when banning new users', function () {
    $existingUser = User::factory()->create(['email_verified_at' => now()]);
    $newUserToBan = User::factory()->create(['email_verified_at' => now()]);

    // Create an existing registration
    AuctionRegistration::create([
        'auction_id' => $this->auction->id,
        'user_id' => $existingUser->id,
        'status' => RegistrationStatus::Approved,
    ]);

    actingAs($this->owner)
        ->post(route('my.auctions.ban-users', $this->auction), [
            'user_ids' => [$newUserToBan->id],
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    // Existing registration should still exist
    $existingRegistration = AuctionRegistration::where('auction_id', $this->auction->id)
        ->where('user_id', $existingUser->id)
        ->first();

    expect($existingRegistration)->not->toBeNull();
    expect($existingRegistration->status)->toBe(RegistrationStatus::Approved);

    // New banned user should be added
    $newRegistration = AuctionRegistration::where('auction_id', $this->auction->id)
        ->where('user_id', $newUserToBan->id)
        ->first();

    expect($newRegistration)->not->toBeNull();
    expect($newRegistration->status)->toBe(RegistrationStatus::Banned);
});

it('returns error when no valid user IDs are provided after validation', function () {
    // This test verifies the empty check in the controller
    // However, due to validation rules, this scenario is hard to reach
    // The validation ensures at least one user_id is provided and valid

    actingAs($this->owner)
        ->post(route('my.auctions.ban-users', $this->auction), [
            'user_ids' => [],
        ])
        ->assertInvalid(['user_ids']); // Empty array fails 'required' validation
});

it('can ban multiple users including existing registrations with different statuses', function () {
    $invitedUser = User::factory()->create(['email_verified_at' => now()]);
    $approvedUser = User::factory()->create(['email_verified_at' => now()]);
    $newUser = User::factory()->create(['email_verified_at' => now()]);

    // Create existing registrations with different statuses
    AuctionRegistration::create([
        'auction_id' => $this->auction->id,
        'user_id' => $invitedUser->id,
        'status' => RegistrationStatus::Invited,
    ]);

    AuctionRegistration::create([
        'auction_id' => $this->auction->id,
        'user_id' => $approvedUser->id,
        'status' => RegistrationStatus::Approved,
    ]);

    actingAs($this->owner)
        ->post(route('my.auctions.ban-users', $this->auction), [
            'user_ids' => [$invitedUser->id, $approvedUser->id, $newUser->id],
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    // All users should now be banned
    $registrations = AuctionRegistration::where('auction_id', $this->auction->id)
        ->whereIn('user_id', [$invitedUser->id, $approvedUser->id, $newUser->id])
        ->get();

    expect($registrations)->toHaveCount(3);

    foreach ($registrations as $registration) {
        expect($registration->status)->toBe(RegistrationStatus::Banned);
    }
});
