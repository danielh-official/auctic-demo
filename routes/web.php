<?php

use App\Http\Controllers\AuctionController;
use App\Http\Controllers\AuctionParticipant\AcceptInvitationToAuctionFromOwner;
use App\Http\Controllers\AuctionParticipant\BanParticipantsFromAllMyAuctions;
use App\Http\Controllers\AuctionParticipant\BanParticipantsFromMyAuction;
use App\Http\Controllers\AuctionParticipant\InviteParticipantsToMyAuction;
use App\Http\Controllers\AuctionParticipant\JoinAuction;
use App\Http\Controllers\MakeBid;
use App\Http\Controllers\MyAuctionController;
use App\Http\Controllers\MyAuctionLotController;
use App\Http\Controllers\Pay;
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

            Route::post('bid', MakeBid::class)->name('bid');

            Route::post('pay', Pay::class)->name('pay');
        });
    });

    // Authenticated user's own auctions
    Route::prefix('my')->name('my.')->group(function () {
        Route::resource('auctions', MyAuctionController::class);

        Route::prefix('auctions')->name('auctions.')->group(function () {
            Route::post('/all/ban-participants', BanParticipantsFromAllMyAuctions::class)->name('all.ban-participants');

            Route::prefix('{auction}')->group(function () {
                Route::resource('lots', MyAuctionLotController::class);

                Route::post('/ban-participants', BanParticipantsFromMyAuction::class)->name('ban-participants');

                Route::post('/invite-participants', InviteParticipantsToMyAuction::class)->name('invite-participants');
            });
        });
    });
});

require __DIR__.'/settings.php';
