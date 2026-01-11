<?php

namespace App\Jobs;

use App\Enums\BidStatus;
use App\Models\Lot;
use Illuminate\Contracts\Broadcasting\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\SerializesModels;

class SetHighestAcceptedBid implements ShouldBeUnique, ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected Lot $lot
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Get highest bid for the lot
        $highestBid = $this->lot->bids()->where('status', '<>', BidStatus::Rejected)->orderBy('amount_cents', 'desc')->first();

        // Get all previous bids and make sure their status is set to Outbid
        $this->lot->bids()
            ->where('amount_cents', '<', $highestBid->amount_cents)
            ->update(['status' => BidStatus::Outbid]);
    }

    public function uniqueId()
    {
        return "set-highest-accept-bid-for-lot:{$this->lot->id}";
    }
}
