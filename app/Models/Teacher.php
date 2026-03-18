<?php

namespace App\Models;

use App\Models\Traits\BelongsToAcademy;
use App\Models\Traits\HasUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Teacher extends Model
{
    use HasFactory, HasUser, BelongsToAcademy;

    public function classrooms(): HasMany
    {
        return $this->hasMany(Classroom::class);
    }

    // 시험지와의 다대다 관계
    public function testSheets()
    {
        return $this->belongsToMany(TestSheet::class, 'test_sheet_teacher');
    }
}
