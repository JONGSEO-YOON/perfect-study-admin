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

    public function examSharingRules()
    {
        return $this->hasMany(ExamSharingRule::class);
    }

    public function isMenuGroupVisible(string $groupKey): bool
    {
        $settings = $this->settings ?? [];
        return ($settings["{$groupKey}_visible"] ?? true) !== false;
    }

    public static function isMenuGroupVisibleForCurrentUser(string $groupKey): bool
    {
        $user = auth()->user();
        if (!$user) {
            return true;
        }
        if ($user->role === 'root_admin') {
            return true;
        }
        $academy = $user->academy;
        if (!$academy) {
            return true;
        }
        return $academy->isMenuGroupVisible($groupKey);
    }
}
