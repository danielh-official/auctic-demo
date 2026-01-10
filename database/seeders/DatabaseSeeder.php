<?php

namespace Database\Seeders;

use App\Models\Auction;
use App\Models\AuctionParticipant;
use App\Models\Lot;
use App\Models\PaymentIntent;
use App\Models\Settlement;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::factory()->admin()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
        ]);

        $auction = Auction::factory()->live()->create([
            'title' => 'Demo Auction',
            'description' => 'A small catalog to showcase backend flows.',
        ]);

        $lots = Lot::factory()->count(3)->open()->for($auction)->create();

        foreach ($lots as $lot) {
            AuctionParticipant::factory()->for($auction)->for($admin)->create();

            $settlement = Settlement::factory()->for($lot)->create();

            PaymentIntent::factory()
                ->for($settlement)
                ->succeeded()
                ->state([
                    'amount_cents' => $settlement->total_cents,
                ])
                ->create();
        }
    }
}
