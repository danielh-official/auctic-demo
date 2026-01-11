<?php

namespace App\Models;

use App\Enums\AuctionBillStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuctionBill extends Model
{
    /** @use HasFactory<\Database\Factories\AuctionBillFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'auction_id',
        'user_id',
        'subtotal_cents',
        'buyer_premium_cents',
        'tax_cents',
        'total_cents',
        'paid_cents',
        'status',
        'due_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'subtotal_cents' => 'int',
            'buyer_premium_cents' => 'int',
            'tax_cents' => 'int',
            'total_cents' => 'int',
            'paid_cents' => 'int',
            'status' => AuctionBillStatus::class,
            'due_at' => 'datetime',
        ];
    }

    public function auction(): BelongsTo
    {
        return $this->belongsTo(Auction::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function remainingBalanceCents(): int
    {
        return max(0, $this->total_cents - $this->paid_cents);
    }

    public function isFullyPaid(): bool
    {
        return $this->paid_cents >= $this->total_cents;
    }

    public function isOverdue(): bool
    {
        return $this->due_at !== null
            && $this->due_at->isPast()
            && ! $this->isFullyPaid();
    }
}
