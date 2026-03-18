<?php

namespace App\Models;

use App\Models\Traits\BelongsToAcademy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory, BelongsToAcademy;

    protected $fillable = [
        'user_id',
        'student_id',
        'amount',
        'payment_status',
        'payment_method',
        'billing_name',
        'billing_memo',
        'paid_at',
        'cancelled_at',
        'cancel_reason',
    ];

    protected function casts(): array
    {
        return [
            'paid_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * 결제 성공 처리
     *
     * @param string $paymentKey
     * @param string $log
     * @return bool
     */
    public function markAsSuccess(string $paymentKey, string $log = null, $method = null): bool
    {
        return $this->update([
            'payment_status' => 'paid',
            'payment_method' => $method,
            'payment_key' => $paymentKey,
            'payment_log' => $log,
            'approved_at' => now(),
        ]);
    }

    public function markAsFailed(string $log = null): bool
    {
        return $this->update([
            'payment_status' => 'failed',
            'payment_log' => $log,
        ]);
    }

    /**
     * 결제 취소 처리
     *
     * @param string $cancelReason
     * @param string $log
     * @return bool
     */
    public function markAsCancelled(string $cancelReason = null, string $log = null): bool
    {
        return $this->update([
            'payment_status' => 'cancelled',
            'cancelled_at' => now(),
            'cancel_reason' => $cancelReason,
            'payment_log' => $log,
        ]);
    }
}
