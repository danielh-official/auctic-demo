<?php

use App\Enums\RegistrationStatus;
use App\Models\Auction;
use App\Models\AuctionRegistration;
use App\Models\User;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->user = User::factory()->create(['email_verified_at' => now()]);
});

it('allows invited user to accept invitation successfully', function () {
    $owner = User::factory()->create(['email_verified_at' => now()]);
    $auction = Auction::factory()->create(['owner_id' => $owner->id]);

    // Create an invitation
    $invitation = AuctionRegistration::factory()->create([
        'auction_id' => $auction->id,
        'user_id' => $this->user->id,
        'status' => RegistrationStatus::Invited,
    ]);

    actingAs($this->user)
        ->patch(route('auctions.accept-invitation', $auction))
        ->assertRedirect()
        ->assertSessionHas('success', 'You have successfully joined the auction.');

    expect($invitation->fresh())
        ->status->toBe(RegistrationStatus::Approved);
});

it('requires authentication to accept invitation', function () {
    $owner = User::factory()->create(['email_verified_at' => now()]);
    $auction = Auction::factory()->create(['owner_id' => $owner->id]);

    $this->patch(route('auctions.accept-invitation', $auction))
        ->assertRedirect(route('login'));
});

it('returns 404 if user does not have an invitation', function () {
    $owner = User::factory()->create(['email_verified_at' => now()]);
    $auction = Auction::factory()->create(['owner_id' => $owner->id]);

    actingAs($this->user)
        ->patch(route('auctions.accept-invitation', $auction))
        ->assertNotFound();
});

it('returns 404 if invitation status is not invited', function () {
    $owner = User::factory()->create(['email_verified_at' => now()]);
    $auction = Auction::factory()->create(['owner_id' => $owner->id]);

    // Create an already approved registration
    AuctionRegistration::factory()->create([
        'auction_id' => $auction->id,
        'user_id' => $this->user->id,
        'status' => RegistrationStatus::Approved,
    ]);

    actingAs($this->user)
        ->patch(route('auctions.accept-invitation', $auction))
        ->assertNotFound();
});

it('prevents auction owner from accepting invitation to their own auction', function () {
    $auction = Auction::factory()->create(['owner_id' => $this->user->id]);

    // Create an invitation (even though this shouldn't happen in practice)
    $invitation = AuctionRegistration::factory()->create([
        'auction_id' => $auction->id,
        'user_id' => $this->user->id,
        'status' => RegistrationStatus::Invited,
    ]);

    actingAs($this->user)
        ->patch(route('auctions.accept-invitation', $auction))
        ->assertForbidden();

    // Verify the invitation was deleted
    expect(AuctionRegistration::find($invitation->id))->toBeNull();
});

it('returns 404 if invitation belongs to different user', function () {
    $owner = User::factory()->create(['email_verified_at' => now()]);
    $auction = Auction::factory()->create(['owner_id' => $owner->id]);
    $otherUser = User::factory()->create(['email_verified_at' => now()]);

    // Create an invitation for a different user
    AuctionRegistration::factory()->create([
        'auction_id' => $auction->id,
        'user_id' => $otherUser->id,
        'status' => RegistrationStatus::Invited,
    ]);

    actingAs($this->user)
        ->patch(route('auctions.accept-invitation', $auction))
        ->assertNotFound();
});
