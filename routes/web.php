<?php

use App\Http\Controllers\AuctionController;
use App\Http\Controllers\AuctionRegistration\AcceptInvitationToAuctionFromOwner;
use App\Http\Controllers\AuctionRegistration\BanUsersFromAllMyAuctions;
use App\Http\Controllers\AuctionRegistration\BanUsersFromMyAuction;
use App\Http\Controllers\AuctionRegistration\InviteUsersToMyAuction;
use App\Http\Controllers\AuctionRegistration\JoinAuction;
use App\Http\Controllers\MyAuctionController;
use App\Http\Controllers\MyAuctionLotController;
use App\Http\Controllers\Pay;
use App\Http\Controllers\PlaceBid;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

// Public auction browsing (accessible to all users)
Route::resource('auctions', AuctionController::class)->only(['index', 'show']);

// Only accessible to authenticated and verified users
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::prefix('auctions')->name('auctions.')->group(function () {
        Route::prefix('{auction}')->group(function () {
            Route::post('join', JoinAuction::class)->name('join');

            Route::patch('accept-invitation', AcceptInvitationToAuctionFromOwner::class)->name('accept-invitation');

            Route::post('pay', Pay::class)->name('pay');
        });

        Route::prefix('lots/{lot}')->group(function () {
            Route::post('bid', PlaceBid::class)->name('lots.bid');
        });
    });

    // Authenticated user's own auctions
    Route::prefix('my')->name('my.')->group(function () {
        Route::resource('auctions', MyAuctionController::class);

        Route::prefix('auctions')->name('auctions.')->group(function () {
            Route::post('/all/ban-users', BanUsersFromAllMyAuctions::class)->name('all.ban-users');

            Route::prefix('{auction}')->group(function () {
                Route::resource('lots', MyAuctionLotController::class);

                Route::post('/ban-users', BanUsersFromMyAuction::class)->name('ban-users');

                Route::post('/invite-users', InviteUsersToMyAuction::class)->name('invite-users');
            });
        });
    });
});

require __DIR__.'/settings.php';
