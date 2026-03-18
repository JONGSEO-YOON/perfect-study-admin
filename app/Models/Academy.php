<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Academy extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'settings' => 'array',
    ];

    protected $hidden = [
        'toss_client_key',
        'toss_secret_key',
        'toss_customer_key',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function teachers()
    {
        return $this->hasMany(Teacher::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }

    public function classrooms()
    {
        return $this->hasMany(Classroom::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
