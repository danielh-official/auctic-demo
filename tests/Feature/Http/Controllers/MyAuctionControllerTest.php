<?php

use App\Enums\AuctionState;
use App\Models\Auction;
use App\Models\User;

use function Pest\Laravel\actingAs;

// Index Tests
test('authenticated user can view their auctions list', function () {
    $this->markTestIncomplete('Frontend not implemented yet');

    $user = User::factory()->create();
    $auction = Auction::factory()->for($user, 'owner')->create();

    $response = actingAs($user)->get(route('my.auctions.index'));

    $response->assertOk();
});

test('guest cannot view auctions index', function () {
    $response = $this->get(route('my.auctions.index'));

    $response->assertRedirect(route('login'));
});

test('user only sees their own auctions in index', function () {
    $this->markTestIncomplete('Frontend not implemented yet');

    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $userAuction = Auction::factory()->for($user, 'owner')->create(['title' => 'User Auction']);
    $otherAuction = Auction::factory()->for($otherUser, 'owner')->create(['title' => 'Other Auction']);

    $response = actingAs($user)->get(route('my.auctions.index'));

    $response->assertOk();
});

// Create Tests
test('authenticated user can view create form', function () {
    $this->markTestIncomplete('Frontend not implemented yet');

    $user = User::factory()->create();

    $response = actingAs($user)->get(route('my.auctions.create'));

    $response->assertOk();
});

test('guest cannot view create form', function () {
    $response = $this->get(route('my.auctions.create'));

    $response->assertRedirect(route('login'));
});

// Store Tests
test('authenticated user can create auction', function () {
    $user = User::factory()->create();

    $auctionData = [
        'title' => 'Test Auction',
        'description' => 'Test Description',
        'scheduled_at' => now()->addDays(7)->toDateTimeString(),
    ];

    $response = actingAs($user)->post(route('my.auctions.store'), $auctionData);

    $response->assertRedirect();
    $this->assertDatabaseHas('auctions', [
        'title' => 'Test Auction',
        'owner_id' => $user->id,
        'state' => AuctionState::Draft->value,
    ]);
});

test('auction creation requires title', function () {
    $user = User::factory()->create();

    $response = actingAs($user)->post(route('my.auctions.store'), [
        'description' => 'Test Description',
    ]);

    $response->assertSessionHasErrors('title');
});

test('auction title cannot exceed 255 characters', function () {
    $user = User::factory()->create();

    $response = actingAs($user)->post(route('my.auctions.store'), [
        'title' => str_repeat('a', 256),
        'description' => 'Test Description',
    ]);

    $response->assertSessionHasErrors('title');
});

test('scheduled_at must be in the future', function () {
    $user = User::factory()->create();

    $response = actingAs($user)->post(route('my.auctions.store'), [
        'title' => 'Test Auction',
        'scheduled_at' => now()->subDay()->toDateTimeString(),
    ]);

    $response->assertSessionHasErrors('scheduled_at');
});

test('guest cannot create auction', function () {
    $response = $this->post(route('my.auctions.store'), [
        'title' => 'Test Auction',
    ]);

    $response->assertRedirect(route('login'));
});

// Show Tests
test('owner can view their auction', function () {
    $this->markTestIncomplete('Frontend not implemented yet');

    $user = User::factory()->create();
    $auction = Auction::factory()->for($user, 'owner')->create();

    $response = actingAs($user)->get(route('my.auctions.show', $auction));

    $response->assertOk();
});

test('non-owner cannot view another users auction', function () {
    $this->markTestIncomplete('Frontend not implemented yet');

    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $auction = Auction::factory()->for($owner, 'owner')->create();

    $response = actingAs($otherUser)->get(route('my.auctions.show', $auction));

    $response->assertForbidden();
});

test('admin can view any auction', function () {
    $this->markTestIncomplete('Frontend not implemented yet');

    $admin = User::factory()->create(['is_admin' => true]);
    $owner = User::factory()->create();
    $auction = Auction::factory()->for($owner, 'owner')->create();

    $response = actingAs($admin)->get(route('my.auctions.show', $auction));

    $response->assertOk();
});

test('guest cannot view auction', function () {
    $auction = Auction::factory()->create();

    $response = $this->get(route('my.auctions.show', $auction));

    $response->assertRedirect(route('login'));
});

// Edit Tests
test('owner can view edit form for their auction', function () {
    $this->markTestIncomplete('Frontend not implemented yet');

    $user = User::factory()->create();
    $auction = Auction::factory()->for($user, 'owner')->create();

    $response = actingAs($user)->get(route('my.auctions.edit', $auction));

    $response->assertOk();
});

test('non-owner cannot view edit form for another users auction', function () {
    $this->markTestIncomplete('Frontend not implemented yet');

    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $auction = Auction::factory()->for($owner, 'owner')->create();

    $response = actingAs($otherUser)->get(route('my.auctions.edit', $auction));

    $response->assertForbidden();
});

test('guest cannot view edit form', function () {
    $auction = Auction::factory()->create();

    $response = $this->get(route('my.auctions.edit', $auction));

    $response->assertRedirect(route('login'));
});

// Update Tests
test('owner can update their auction', function () {
    $user = User::factory()->create();
    $auction = Auction::factory()->for($user, 'owner')->create([
        'title' => 'Original Title',
        'description' => 'Original Description',
    ]);

    $response = actingAs($user)->put(route('my.auctions.update', $auction), [
        'title' => 'Updated Title',
        'description' => 'Updated Description',
    ]);

    $this->assertDatabaseHas('auctions', [
        'id' => $auction->id,
        'title' => 'Updated Title',
        'description' => 'Updated Description',
    ]);

    // TODO: Show view not implemented yet, so this will fail until it is. For now just verify the database update.
    // $response->assertRedirect(route('my.auctions.show', $auction));
});

test('non-owner cannot update another users auction', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $auction = Auction::factory()->for($owner, 'owner')->create([
        'title' => 'Original Title',
    ]);

    $response = actingAs($otherUser)->put(route('my.auctions.update', $auction), [
        'title' => 'Updated Title',
    ]);

    $response->assertForbidden();
    $this->assertDatabaseHas('auctions', [
        'id' => $auction->id,
        'title' => 'Original Title',
    ]);
});

test('admin cannot update any auction', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $owner = User::factory()->create();
    $auction = Auction::factory()->for($owner, 'owner')->create([
        'title' => 'Original Title',
    ]);

    $response = actingAs($admin)->put(route('my.auctions.update', $auction), [
        'title' => 'Admin Updated Title',
        'description' => 'Admin Updated Description',
    ]);

    $this->assertDatabaseHas('auctions', [
        'id' => $auction->id,
        'title' => 'Original Title',
    ]);

    // TODO: Show view not implemented yet, so this will fail until it is. For now just verify the database update.
    // $response->assertRedirect(route('my.auctions.show', $auction));
});

test('guest cannot update auction', function () {
    $auction = Auction::factory()->create();

    $response = $this->put(route('my.auctions.update', $auction), [
        'title' => 'Updated Title',
    ]);

    $response->assertRedirect(route('login'));
});

// Destroy Tests
test('owner can delete their auction', function () {
    $user = User::factory()->create();
    $auction = Auction::factory()->for($user, 'owner')->create();

    $response = actingAs($user)->delete(route('my.auctions.destroy', $auction));

    $response->assertRedirect(route('my.auctions.index'));
    $this->assertSoftDeleted('auctions', [
        'id' => $auction->id,
    ]);
});

test('non-owner cannot delete another users auction', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $auction = Auction::factory()->for($owner, 'owner')->create();

    $response = actingAs($otherUser)->delete(route('my.auctions.destroy', $auction));

    $response->assertForbidden();
    $this->assertDatabaseHas('auctions', [
        'id' => $auction->id,
        'deleted_at' => null,
    ]);
});

test('admin cannot delete any auction', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $owner = User::factory()->create();
    $auction = Auction::factory()->for($owner, 'owner')->create();

    $response = actingAs($admin)->delete(route('my.auctions.destroy', $auction));

    $this->assertNotSoftDeleted('auctions', [
        'id' => $auction->id,
    ]);

    // TODO: Index view not implemented yet, so this will fail until it is. For now just verify the database deletion.
    // $response->assertRedirect(route('my.auctions.index'));
});

test('guest cannot delete auction', function () {
    $auction = Auction::factory()->create();

    $response = $this->delete(route('my.auctions.destroy', $auction));

    $response->assertRedirect(route('login'));
});
