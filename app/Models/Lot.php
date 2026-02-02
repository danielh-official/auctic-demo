<?php

namespace App\Models;

use App\Enums\LotStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lot extends Model
{
    /** @use HasFactory<\Database\Factories\LotFactory> */
    use HasFactory, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'auction_id',
        'title',
        'sku',
        'reserve_price',
        'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'reserve_price' => 'int',
            'status' => LotStatus::class,
        ];
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', LotStatus::Open);
    }

    public function auction(): BelongsTo
    {
        return $this->belongsTo(Auction::class);
    }

    public function bids(): HasMany
    {
        return $this->hasMany(Bid::class);
    }

    public function winningBid(): HasOne
    {
        return $this->hasOne(Bid::class)->ofMany('amount', 'max');
    }

    public function cooldownPhaseInSeconds(): Attribute
    {
        return Attribute::make(
            get: fn (): int => now()->isSameDay($this->auction->live_ends_at)
                ? 15
                : 30,
        );
    }
}
