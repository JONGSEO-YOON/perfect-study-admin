<?php

namespace App\Models;

use App\Models\Traits\HasUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Student extends Model
{
    use HasFactory, HasUser;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sms_targets' => 'array',
            'initially_attended_at' => 'date',
            'sms_agree' => 'boolean',
        ];
    }

    public function gradeSystem()
    {
        return $this->belongsTo(GradeSystem::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get the classrooms that the student belongs to.
     */
    public function classrooms(): BelongsToMany
    {
        return $this->belongsToMany(Classroom::class)
            ->withTimestamps();
    }
}
