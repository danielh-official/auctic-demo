<?php

namespace App\Jobs;

// use App\Enums\BidStatus;
// use App\Models\Bid;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

// use Illuminate\Support\Facades\DB;

class ProcessBidPlacement implements ShouldBeUnique, ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected int $lotId,
        protected int $userId,
        protected int $amountCents,
        protected Carbon $placedAt,
    ) {
        $this->onQueue('bids');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // TODO: Implement bid processing logic here
    }

    /**
     * Get the unique ID for the job.
     * Prevents duplicate bid jobs for the same user on the same lot.
     */
    public function uniqueId(): string
    {
        return "place-bid:user:{$this->userId}:lot:{$this->lotId}";
    }

    /**
     * Determine if the job should be marked as failed on timeout.
     */
    public function timeout(): int
    {
        return 30;
    }
}
