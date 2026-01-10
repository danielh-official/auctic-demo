<?php

namespace App\Models;

use App\Enums\AuctionState;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Auction extends Model
{
    /** @use HasFactory<\Database\Factories\AuctionFactory> */
    use HasFactory, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'description',
        'state',
        'scheduled_at',
        'live_at',
        'live_ends_at',
        'closed_at',
        'owner_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'state' => AuctionState::class,
            'scheduled_at' => 'datetime',
            'live_at' => 'datetime',
            'live_ends_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public function lots(): HasMany
    {
        return $this->hasMany(Lot::class);
    }

    public function participants(): HasMany
    {
        return $this->hasMany(AuctionParticipant::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
}
