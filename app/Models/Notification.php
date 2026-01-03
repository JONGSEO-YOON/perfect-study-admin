<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'content',
        'data',
        'is_read',
        'read_at'
    ];

    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime'
    ];

    // 알림 유형 상수 정의
    const TYPE_MEMBERSHIP_APPROVAL = 'membership_approval';
    const TYPE_COUNSELING_REQUEST = 'counseling_request';
    const TYPE_COUNSELING_CONFIRMATION = 'counseling_confirmation';

    // 알림을 받는 사용자와의 관계 정의
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // 알림을 읽음 처리하는 메서드
    public function markAsRead()
    {
        $this->is_read = true;
        $this->read_at = now();
        $this->save();
    }

    // 읽지 않은 알림만 조회하는 스코프
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    // 특정 유형의 알림만 조회하는 스코프
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }
}
