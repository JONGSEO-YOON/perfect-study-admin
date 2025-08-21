<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FcmToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_phone',
        'token'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * 특정 부모의 토큰을 조회하는 스코프
     */
    public function scopeForParent($query, $phone)
    {
        return $query->where('parent_phone', $phone);
    }

    /**
     * 모든 부모 토큰 조회
     */
    public function scopeAllParents($query)
    {
        return $query->select('token');
    }
}
