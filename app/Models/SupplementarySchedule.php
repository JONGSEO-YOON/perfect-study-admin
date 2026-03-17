<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplementarySchedule extends Model
{
    protected function casts(): array
    {
        return [
            'scheduled_date' => 'date',
        ];
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }
}
