<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Builder;
use PhpParser\Node\Expr\BinaryOp\Equal;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'birthed_at' => 'date',
            'attachments' => 'array',
            'meta' => 'array',
            'password' => 'hashed',
        ];
    }

    public function userable()
    {
        return $this->morphTo();
    }

    public function canAccessPanel(Panel $panel): bool
    {
        // userable_type is not 'App\Models\Student'
        if ($this->userable_type !== Student::class) {
            return true;
        }
        return false;
    }

    /**
     * 특정 교실에 속하지 않은 학생들을 이름으로 검색
     *
     * @param string $search 검색할 학생 이름
     * @param int $classroomId 제외할 교실 ID
     * @return array<int, string> 학생ID를 key로, 이름을 value로 하는 배열
     */
    public static function getAvailableStudentsByClassRoomId(string $search, int $classroomId): array
    {
        return User::query()
            ->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->where('userable_type', Student::class)
            ->whereHasMorph('userable', [Student::class], function (Builder $query) use ($classroomId) {
                $query->whereDoesntHave('classrooms', function (Builder $q) use ($classroomId) {
                    $q->where('classrooms.id', $classroomId);
                });
            })
            // ->whereHas('userable', function (Builder $query) use ($classroomId) {
            //     $query->whereDoesntHave('classrooms', function (Builder $q) use ($classroomId) {
            //         $q->where('classrooms.id', $classroomId);
            //     });
            // })
            ->with('userable')
            ->get()
            ->mapWithKeys(function ($user) {
                return [$user->userable->id => $user->name . ' (' . $user->birthed_at->format('Y-m-d') . ')'];
            })
            ->toArray();
    }

    /**
     * Get the user's role.
     */
    public function getRoleAttribute(): string
    {
        if (!$this->userable) {
            return 'guest';
        }

        if ($this->userable instanceof \App\Models\Teacher) {
            return $this->userable->role;
        }

        // Counselor나 Student의 경우 클래스명의 소문자 버전을 반환
        return strtolower(class_basename($this->userable));
    }

    public function isRoleAboveOrSelf($user): bool
    {
        return $this->isRoleAbove($user->role) || $this->id === $user->id;
    }

    /**
     * Check if current user's role is above the given role
     */
    public function isRoleAbove(string $role, bool $equal = false): bool
    {
        $roles = [
            'root_admin' => 0,
            'admin' => 1,
            'manager' => 2,
            'general' => 3,
            'counselor' => 3,
            'student' => 5,
            'guest' => 6,
        ];

        if ($this->role === 'root_admin') {
            return true;
        }

        // 현재 유저의 role이나 비교할 role이 정의되지 않은 경우
        if (!isset($roles[$this->role]) || !isset($roles[$role])) {
            return false;
        }

        // 숫자가 작을수록 높은 권한
        if ($equal) {
            return $roles[$this->role] <= $roles[$role];
        } else {
            return $roles[$this->role] < $roles[$role];
        }
    }


    public function student()
    {
        return $this->belongsTo(Student::class, 'userable_id')
            ->where('users.userable_type', Student::class);
    }
}
