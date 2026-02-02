<?php

namespace App\Jobs;

use App\Enums\BidStatus;
use App\Models\Lot;
use App\Models\User;
use App\Notifications\BidRejected;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ProcessBidPlacement implements ShouldBeUnique, ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected Lot $lot,
        protected User $user,
        protected int $amount,
        protected Carbon $placedAt,
    ) {
        $this->onQueue('bids');
    }

    /**
     * Execute the job.
     *
     * When a bids are placed, the jobs should be processed sequentially in order of first to last to avoid race conditions.
     */
    public function handle(): void
    {
        $cacheKey = self::bidInProgressCacheKey($this->lot, $this->user);
        $cacheTtl = now()->addSeconds($this->timeout() + 30);

        if (! Cache::add($cacheKey, true, $cacheTtl)) {
            $message = 'Still processing original bid.';

            $this->user->notify(new BidRejected($message));

            $this->fail($message);

            return;
        }

        try {
            $isFirstBid = $this->lot->bids()->count() === 0;

            $currentHighestBid = $this->lot->bids()->orderByDesc('amount')->first();

            $minimumIncrement = 5000;

            $minimumBid = $isFirstBid ? $this->lot->reserve_price : $currentHighestBid->amount + $minimumIncrement;

            if ($this->amount <= $minimumBid) {
                $minimumBidInDollars = number_format($minimumBid / 100, 2);

                $message = "Your bid must be higher than \${$minimumBidInDollars}.";

                $this->user->notify(new BidRejected($message));

                $this->fail($message);

                return;
            }

            DB::transaction(function () {
                $this->lot->bids()->create([
                    'user_id' => $this->user->id,
                    'amount' => $this->amount,
                    'placed_at' => $this->placedAt,
                ]);

                // Update all previous bids to 'outbid' status
                $this->lot->bids()
                    ->where('user_id', '!=', $this->user->id)
                    ->lockForUpdate()
                    ->update(['status' => BidStatus::Outbid]);
            });
        } finally {
            Cache::forget($cacheKey);
        }
    }

    /**
     * Get the unique ID for the job.
     * Prevents duplicate bid jobs for the same lot.
     */
    public function uniqueId(): string
    {
        return "place-bid:lot:{$this->lot->id}";
    }

    public static function bidInProgressCacheKey(Lot $lot, User $user): string
    {
        return "bid-processing:lot:{$lot->id}:user:{$user->id}";
    }

    /**
     * Determine if the job should be marked as failed on timeout.
     */
    public function timeout(): int
    {
        return 30;
    }
}
