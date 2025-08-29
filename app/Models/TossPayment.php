<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TossPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_id',
        'order_id',
        'payment_info',
        'payment_log',
        'success_url',
        'fail_url',
        'status',
        'payment_key',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'payment_info' => 'array',
            'approved_at' => 'datetime',
        ];
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}