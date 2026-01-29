<?php

namespace App\Models;

use App\Enums\AuctionBillStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
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
        'subtotal_amount',
        'buyer_premium_amount',
        'tax_amount',
        'total_amount',
        'paid_amount',
        'status',
        'due_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'subtotal_amount' => 'int',
            'buyer_premium_amount' => 'int',
            'tax_amount' => 'int',
            'total_amount' => 'int',
            'paid_amount' => 'int',
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

    public function remainingBalanceAmount(): Attribute
    {
        return Attribute::get(fn () => max(0, $this->total_amount - $this->paid_amount));
    }

    public function isFullyPaid(): bool
    {
        return $this->paid_amount >= $this->total_amount;
    }

    public function isOverdue(): bool
    {
        return $this->due_at !== null
            && $this->due_at->isPast()
            && ! $this->isFullyPaid();
    }
}
