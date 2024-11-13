<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Classroom extends Model
{
    protected function casts(): array
    {
        return [
            'attachments' => 'array',
            'timetable' => 'array',
            'target_grades' => 'array',
        ];
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    /**
     * Get the students that belong to the classroom.
     */
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class)
            ->withTimestamps();
    }
}
