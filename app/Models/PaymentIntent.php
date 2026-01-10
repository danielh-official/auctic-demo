<?php

namespace App\Models;

use App\Enums\PaymentIntentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentIntent extends Model
{
    /** @use HasFactory<\Database\Factories\PaymentIntentFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'settlement_id',
        'amount_cents',
        'status',
        'reference',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount_cents' => 'int',
            'status' => PaymentIntentStatus::class,
        ];
    }

    public function settlement(): BelongsTo
    {
        return $this->belongsTo(Settlement::class);
    }
}
