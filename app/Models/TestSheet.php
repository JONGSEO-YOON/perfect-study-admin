<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class TestSheet extends Model
{
    use HasFactory;

    protected $casts = [
        'display' => 'boolean',
        'published_at' => 'datetime',
        'expired_at' => 'datetime',
        'target_grades' => 'array',
        'target_levels' => 'array',
        'target_classrooms' => 'array',
        'target_students' => 'array',
        'lecture_info' => 'array',
        'attachments' => 'array',
        'scopes' => 'array',
        'questions' => 'array',
        'tags' => 'array',
        'report' => 'array',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    // 강의를 등록한 사용자와의 관계
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->user_id && auth()->check()) {
                $model->user_id = auth()->id();
            }
        });
    }

    public function getTargetGradeNamesAttribute($value)
    {
        $grades = [];
        foreach ($this->target_grades as $grade) {
            $_grade = GradeSystem::find($grade);
            $grades[] = $_grade->display_name;
        }
        return implode(', ', $grades);
    }

    public function userAnswers()
    {
        return $this->hasMany(TestSheetAnswer::class)
            ->where('user_id', auth()->id());
    }

    public function latestUserAnswer()
    {
        return $this->hasOne(TestSheetAnswer::class)
            ->where('user_id', auth()->id())
            ->latest();
    }


    /**
     * 학생에게 해당되는 시험지만 조회하는 스코프
     */
    public function scopeAvailableFor(Builder $query, Student $student): Builder
    {
        $teacherIds = $student->classrooms()
            ->with('teacher.user')
            ->get()
            ->pluck('teacher.user.id')
            ->unique()
            ->values()
            ->all();

        return $query->where(function ($query) use ($student, $teacherIds) {
            // 출제자가 학생의 강사인 경우만
            $query->whereIn('user_id', $teacherIds);

            // target_group별 조건 체크
            $query->where(function ($q) use ($student) {
                $this->addTargetGroupConditions($q, $student);
            });
        });
    }

    /**
     * 진행중 시험지 조회 스코프
     */
    public function scopeInProgress(Builder $query): Builder
    {
        return $query->where(function ($query) {
            $query->where('status', 'progress');
        });
    }


    /**
     * 진행중이거나 완료된 시험지 조회 스코프
     * completed 상태의 경우 사용자의 답안이 있는 것만 조회
     */
    public function scopeInProgressOrCompleted(Builder $query): Builder
    {
        return $query->where(function ($query) {
            $query->where('status', 'progress')
                ->orWhere(function ($q) {
                    $q->where('status', 'completed')
                        ->whereHas('userAnswers');
                });
        });
    }

    /**
     * target_group 조건들을 쿼리에 추가
     */
    protected function addTargetGroupConditions($query, Student $student): void
    {
        // 학년 대상
        $query->where(function ($subQ) use ($student) {
            $targetGrades = $student->classrooms()
                ->get()
                ->pluck('target_grades')
                ->flatten()
                ->unique()
                ->values();

            if ($targetGrades->isNotEmpty()) {
                $subQ->where('target_group', 'grade')
                    ->where(function ($jsonQ) use ($targetGrades) {
                        foreach ($targetGrades as $gradeId) {
                            $jsonQ->orWhereJsonContains('target_grades', $gradeId)
                                ->orWhereJsonContains('target_grades', (string)$gradeId);
                        }
                    });
            }
        })
            // 반 대상
            ->orWhere(function ($subQ) use ($student) {
                $classroomIds = $student->classrooms->pluck('id');
                $subQ->where('target_group', 'classroom')
                    ->where(function ($jsonQ) use ($classroomIds) {
                        foreach ($classroomIds as $id) {
                            $jsonQ->orWhereJsonContains('target_classrooms', $id)
                                ->orWhereJsonContains('target_classrooms', (string)$id);
                        }
                    });
            })
            // 학생 대상
            ->orWhere(function ($subQ) use ($student) {
                $subQ->where('target_group', 'student')
                    ->where(function ($jsonQ) {
                        $jsonQ->whereJsonContains('target_students', auth()->id())
                            ->orWhereJsonContains('target_students', (string)auth()->id());
                    });
            });

        // 레벨 대상
        if ($student->classrooms->isNotEmpty()) {
            $query->orWhere(function ($subQ) use ($student) {
                $classrooms = $student->classrooms;
                $classroomLevels = $classrooms->pluck('target_level')->filter();
                $classroomGrades = $classrooms->pluck('target_grades')
                    ->flatten()
                    ->filter()
                    ->unique()
                    ->values();

                if ($classroomLevels->isNotEmpty() && $classroomGrades->isNotEmpty()) {
                    $subQ->where('target_group', 'level')
                        ->where(function ($jsonQ) use ($classroomLevels) {
                            foreach ($classroomLevels as $level) {
                                $jsonQ->orWhereJsonContains('target_levels', $level);
                            }
                        })
                        ->where(function ($jsonQ) use ($classroomGrades) {
                            foreach ($classroomGrades as $gradeId) {
                                $jsonQ->orWhereJsonContains('target_grades', $gradeId)
                                    ->orWhereJsonContains('target_grades', (string)$gradeId);
                            }
                        });
                }
            });
        }
    }

    /**
     * 시험지를 마감하고 모든 대상 학생들의 답안 상태를 업데이트합니다.
     * 
     * @return bool
     */
    public function complete(): bool
    {
        // 트랜잭션 시작
        return DB::transaction(function () {
            // 1. 시험지 상태를 completed로 변경
            $this->status = 'completed';
            $this->end_date = now();
            $this->save();

            // 2. 대상 학생들 수집
            $targetStudents = $this->getTargetStudents();

            // 3. 각 학생별로 답안 상태 업데이트 또는 생성
            foreach ($targetStudents as $student) {
                $answer = TestSheetAnswer::where([
                    'test_sheet_id' => $this->id,
                    'user_id' => $student->user->id,
                ])->first();

                if (!$answer) {
                    // 기존 답안이 없는 경우에만 새로 생성
                    TestSheetAnswer::create([
                        'test_sheet_id' => $this->id,
                        'user_id' => $student->user->id,
                        'status' => 'completed',
                        'answers' => [], // 빈 배열로 초기화
                        'correct_count' => 0 // 0점으로 초기화
                    ]);
                } else {
                    // 기존 답안이 있는 경우 status만 업데이트
                    $answer->update([
                        'status' => 'completed'
                    ]);
                }
            }

            // 4. 리포트 생성
            return $this->generateReport();
        });
    }

    public function generateReport(): bool
    {
        return DB::transaction(function () {
            // 1. 전체 문제 수 계산
            $totalQuestions = count($this->questions);
            if ($totalQuestions === 0) return false;

            $calculatePercentage = function ($value, $total) {
                return $total > 0 ? round(($value / $total) * 100, 2) : 0;
            };

            // 평균 점수 계산 헬퍼 함수
            $calculateAverage = function ($scores) {
                return count($scores) > 0 ? array_sum($scores) / count($scores) : 0;
            };

            // 1. 대상 학생들과 답안 수집
            $targetStudents = $this->getTargetStudents();
            $answers = TestSheetAnswer::where('test_sheet_id', $this->id)->get();

            // 2. 학생별 대표 classroom과 점수 수집
            $studentData = [];
            $classroomScores = [];
            $levelScores = [];
            $allScores = [];

            // 문제 유형별 점수 저장 배열
            $typeScores = [];

            foreach ($answers as $answer) {
                $student = $targetStudents->firstWhere('user.id', $answer->user_id);
                if (!$student) continue;

                $classroom = $this->getRepresentativeClassroom($student);
                if (!$classroom) continue;

                $score = $answer->correct_count;
                $allScores[] = $score;

                // 문제 유형별 점수 처리
                $typeStats = [];
                if ($answer->correct_count_report) {
                    foreach ($answer->correct_count_report as $typeId => $typeData) {
                        $typeName = $typeData['name'];
                        $typeCorrect = $typeData['correct'];
                        $typeTotal = $typeData['total'];

                        // 유형별 전체 통계를 위한 배열 초기화
                        if (!isset($typeScores[$typeName])) {
                            $typeScores[$typeName] = [
                                'all' => [],
                                'classroom' => [],
                                'level' => []
                            ];
                        }

                        // 유형별 전체 통계 저장
                        $typeScores[$typeName]['all'][] = [
                            'correct' => $typeCorrect,
                            'total' => $typeTotal
                        ];

                        // 반별 통계 저장
                        if (!isset($typeScores[$typeName]['classroom'][$classroom->id])) {
                            $typeScores[$typeName]['classroom'][$classroom->id] = [];
                        }
                        $typeScores[$typeName]['classroom'][$classroom->id][] = [
                            'correct' => $typeCorrect,
                            'total' => $typeTotal
                        ];

                        // 레벨별 통계 저장
                        if (!isset($typeScores[$typeName]['level'][$classroom->target_level])) {
                            $typeScores[$typeName]['level'][$classroom->target_level] = [];
                        }
                        $typeScores[$typeName]['level'][$classroom->target_level][] = [
                            'correct' => $typeCorrect,
                            'total' => $typeTotal
                        ];

                        $typeStats[$typeName] = [
                            'correct' => $typeCorrect,
                            'total' => $typeTotal
                        ];
                    }
                }

                // 학생 데이터 저장
                $studentData[] = [
                    'student_id' => $student->id,
                    'user_id' => $answer->user_id,
                    'name' => $student->user->name,
                    'score' => $score,
                    'classroom_id' => $classroom->id,
                    'classroom_name' => $classroom->name,
                    'level' => $classroom->target_level,
                    'type_scores' => $typeStats
                ];

                // 반별 점수 집계
                if (!isset($classroomScores[$classroom->id])) {
                    $classroomScores[$classroom->id] = [];
                }
                $classroomScores[$classroom->id][] = $score;

                // 레벨별 점수 집계
                $level = $classroom->target_level;
                if (!isset($levelScores[$level])) {
                    $levelScores[$level] = [];
                }
                $levelScores[$level][] = $score;
            }

            // 3. 전체 평균 계산
            $totalAverage = $calculateAverage($allScores);

            // 4. 학생별 상세 정보 생성
            $report = ['students' => []];

            // 전체 점수로 등수 계산을 위한 정렬
            rsort($allScores);

            foreach ($studentData as $data) {
                // 반별 점수 정렬
                $classroomScore = $classroomScores[$data['classroom_id']] ?? [];
                rsort($classroomScore);

                // 레벨별 점수 정렬
                $levelScore = $levelScores[$data['level']] ?? [];
                rsort($levelScore);

                // 각 평균 계산
                $classroomAverage = $calculateAverage($classroomScore);
                $levelAverage = $calculateAverage($levelScore);

                // 문제 유형별 통계 계산
                $questionTypeStats = [];
                foreach ($data['type_scores'] as $type => $scores) {
                    // 해당 유형의 모든 통계 데이터
                    $typeAllScores = $typeScores[$type]['all'];
                    $typeClassroomScores = $typeScores[$type]['classroom'][$data['classroom_id']] ?? [];
                    $typeLevelScores = $typeScores[$type]['level'][$data['level']] ?? [];

                    // 각 범주별 평균 계산
                    $calculateTypeAverage = function ($scoresArray) {
                        if (empty($scoresArray)) return ['correct' => 0, 'total' => 0];

                        $totalCorrect = array_sum(array_column($scoresArray, 'correct'));
                        $totalQuestions = array_sum(array_column($scoresArray, 'total'));

                        return [
                            'correct' => $totalCorrect,
                            'total' => $totalQuestions
                        ];
                    };

                    $typeAllAvg = $calculateTypeAverage($typeAllScores);
                    $typeClassroomAvg = $calculateTypeAverage($typeClassroomScores);
                    $typeLevelAvg = $calculateTypeAverage($typeLevelScores);

                    // 순위 계산을 위한 점수 배열
                    $getScoreArray = function ($scoresArray) {
                        return array_map(function ($score) {
                            return $score['correct'];
                        }, $scoresArray);
                    };

                    $classroomScoreArray = $getScoreArray($typeClassroomScores);
                    rsort($classroomScoreArray);

                    $levelScoreArray = $getScoreArray($typeLevelScores);
                    rsort($levelScoreArray);

                    $questionTypeStats[] = [
                        'name' => $type,
                        'personal_score' => $scores['correct'],
                        'personal_total' => $scores['total'],
                        'personal_score_percentage' => $calculatePercentage($scores['correct'], $scores['total']),
                        'classroom_average' => $typeClassroomAvg['correct'],
                        'classroom_total' => $typeClassroomAvg['total'],
                        'classroom_average_percentage' => $calculatePercentage($typeClassroomAvg['correct'], $typeClassroomAvg['total']),
                        'level_average' => $typeLevelAvg['correct'],
                        'level_total' => $typeLevelAvg['total'],
                        'level_average_percentage' => $calculatePercentage($typeLevelAvg['correct'], $typeLevelAvg['total']),
                        'total_average' => $typeAllAvg['correct'],
                        'total_total' => $typeAllAvg['total'],
                        'total_average_percentage' => $calculatePercentage($typeAllAvg['correct'], $typeAllAvg['total']),
                        'classroom_rank' => array_search($scores['correct'], $classroomScoreArray) + 1,
                        'level_rank' => array_search($scores['correct'], $levelScoreArray) + 1
                    ];
                }

                $report['students'][] = [
                    'rank' => array_search($data['score'], $allScores) + 1,
                    'student_id' => $data['student_id'],
                    'user_id' => $data['user_id'],
                    'student_name' => $data['name'],
                    'classroom_name' => $data['classroom_name'],
                    'personal_score' => $data['score'],
                    'personal_score_percentage' => $calculatePercentage($data['score'], $totalQuestions),
                    'classroom_average' => $classroomAverage,
                    'classroom_average_percentage' => $calculatePercentage($classroomAverage, $totalQuestions),
                    'level_average' => $levelAverage,
                    'level_average_percentage' => $calculatePercentage($levelAverage, $totalQuestions),
                    'total_average' => $totalAverage,
                    'total_average_percentage' => $calculatePercentage($totalAverage, $totalQuestions),
                    'classroom_rank' => array_search($data['score'], $classroomScore) + 1,
                    'level_rank' => array_search($data['score'], $levelScore) + 1,
                    'question_types' => $questionTypeStats
                ];
            }

            // 5. 결과 저장
            $this->report = $report['students'];
            return $this->save();
        });
    }

    protected function getRepresentativeClassroom(Student $student): ?Classroom
    {
        $classrooms = $student->classrooms;
        if ($classrooms->isEmpty()) {
            return null;
        }

        switch ($this->target_group) {
            case 'student':
                // 임의의 반 선택
                return $classrooms->first();

            case 'classroom':
                // target_classrooms과 겹치는 반 중 하나 선택
                return $classrooms->filter(function ($classroom) {
                    return in_array($classroom->id, $this->target_classrooms) ||
                        in_array((string)$classroom->id, $this->target_classrooms);
                })->first();

            case 'grade':
                // target_grades에 해당하는 반 중 하나 선택
                return $classrooms->filter(function ($classroom) {
                    $classroomGrades = $classroom->target_grades ?? [];
                    return count(array_intersect($classroomGrades, $this->target_grades)) > 0;
                })->first();

            case 'level':
                // target_levels와 target_grades 모두에 해당하는 반 중 하나 선택
                return $classrooms->filter(function ($classroom) {
                    // level 체크
                    $levelMatch = in_array($classroom->target_level, $this->target_levels);

                    // grade 체크
                    $classroomGrades = $classroom->target_grades ?? [];
                    $gradeMatch = count(array_intersect($classroomGrades, $this->target_grades)) > 0;

                    // 둘 다 만족해야 함
                    return $levelMatch && $gradeMatch;
                })->first();

            default:
                return null;
        }
    }

    protected function getTargetStudents()
    {
        $allStudents = Student::with(['user', 'classrooms.teacher.user'])
            ->whereHas('classrooms.teacher.user', function ($query) {
                $query->where('id', $this->user_id);
            })
            ->get();

        // 2. 대상 학생 필터링을 위한 배열
        $targetStudents = [];

        // 3. 각 학생별로 시험지 대상자인지 확인
        foreach ($allStudents as $student) {
            // 현재 시험지의 clone을 만들어서 스코프 체크
            $testSheetQuery = static::query()
                ->where('id', $this->id)
                ->availableFor($student);

            // 해당 학생이 시험지 대상자인 경우
            if ($testSheetQuery->exists()) {
                $targetStudents[] = $student;
            }
        }

        // 4. 필터링된 학생들 반환
        return collect($targetStudents);
    }
}
