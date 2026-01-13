<?php

use App\Models\User;

test('users are not admins by default', function () {
    $user = User::factory()->create();

    expect($user->is_admin)->toBeFalse()
        ->and($user->isAdmin())->toBeFalse();
});

test('admin state sets the admin flag', function () {
    $admin = User::factory()->admin()->create();

    expect($admin->is_admin)->toBeTrue()
        ->and($admin->isAdmin())->toBeTrue();
});
