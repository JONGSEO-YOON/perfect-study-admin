<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class ExamSeries extends Model
{
    protected $table = 'exam_series';
    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        $clear = fn() => Cache::forget('exam_series_active_names');
        static::saved($clear);
        static::deleted($clear);
    }

    public static function activeNames(): array
    {
        return Cache::remember('exam_series_active_names', 600, function () {
            return static::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->pluck('name')
                ->toArray();
        });
    }
}
