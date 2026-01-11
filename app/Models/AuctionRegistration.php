<?php

namespace App\Models;

use App\Enums\RegistrationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuctionRegistration extends Model
{
    /** @use HasFactory<\Database\Factories\AuctionRegistrationFactory> */
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'auction_registrations';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'auction_id',
        'user_id',
        'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => RegistrationStatus::class,
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
}
