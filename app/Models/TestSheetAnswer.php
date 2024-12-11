<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TestSheetAnswer extends Model
{
    use HasFactory;

    protected $fillable = ['test_sheet_id', 'user_id', 'answers', 'correct_count'];

    protected $casts = [
        'answers' => 'array',
        'correct_count_report' => 'array'
    ];

    public function testSheet()
    {
        return $this->belongsTo(TestSheet::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getAnswerCountAttribute()
    {
        if (!$this->answers) {
            return 0;
        }

        return count(array_filter($this->answers, function ($answer) {
            return $answer !== null;
        }));
    }
}
