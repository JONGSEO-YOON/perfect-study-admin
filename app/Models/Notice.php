<?php

namespace App\Models;

use App\Models\Traits\BelongsToAcademy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notice extends Model
{
    use HasFactory, BelongsToAcademy;

    protected $casts = [
        'attachments' => 'array',
        'target_groups' => 'array',
        'pinned_at' => 'datetime'
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }


    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->author_id && auth()->check()) {
                $model->author_id = auth()->id();
            }
        });
    }
}
