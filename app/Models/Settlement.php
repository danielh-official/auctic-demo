<?php

namespace App\Models;

use App\Enums\SettlementStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Settlement extends Model
{
    /** @use HasFactory<\Database\Factories\SettlementFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'lot_id',
        'winning_bid_id',
        'buyer_premium_cents',
        'total_cents',
        'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'buyer_premium_cents' => 'int',
            'total_cents' => 'int',
            'status' => SettlementStatus::class,
        ];
    }

    public function lot(): BelongsTo
    {
        return $this->belongsTo(Lot::class);
    }

    public function winningBid(): BelongsTo
    {
        return $this->belongsTo(Bid::class, 'winning_bid_id');
    }

    public function paymentIntents(): HasMany
    {
        return $this->hasMany(PaymentIntent::class);
    }
}
