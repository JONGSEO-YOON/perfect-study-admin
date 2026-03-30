<?php

namespace App\Models;

use App\Models\Traits\BelongsToAcademy;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TestSheet extends Model
{
    use HasFactory, BelongsToAcademy;

    protected $casts = [
        'display' => 'boolean',
        'use_score_table' => 'boolean',
        'show_explanation_video' => 'boolean',
        'published_at' => 'datetime',
        'expired_at' => 'datetime',
        'target_grades' => 'array',
        'target_levels' => 'array',
        'target_classrooms' => 'array',
        'target_students' => 'array',
        'lecture_info' => 'array',
        'attachments' => 'array',
        'score_table' => 'array',
        'parsed_score_table' => 'array',
        'scopes' => 'array',
        'questions' => 'array',
        'tags' => 'array',
        'report' => 'array',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'print_layout' => 'array',
        'exam_years' => 'array',
        'exam_months' => 'array',
        'exam_grades' => 'array',
        'exam_subjects' => 'array',
        'exam_scores' => 'array',
        'exam_semesters' => 'array',
        'exam_types' => 'array',
    ];

    // 강의를 등록한 사용자와의 관계
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function permissions()
    {
        return $this->hasMany(TestSheetPermission::class);
    }

    public function sharedAcademies()
    {
        return $this->belongsToMany(Academy::class, 'test_sheet_permissions')
            ->withPivot('is_allowed')
            ->withTimestamps();
    }

    // 교사와의 다대다 관계
    public function teachers()
    {
        return $this->belongsToMany(Teacher::class, 'test_sheet_teacher');
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

    /**
     * 공유된 기출 문제지를 포함하는 scope
     * - 자기 학원 문제지 (AcademyScope가 이미 처리)
     * - share_scope='all'인 다른 학원 기출 문제지
     * - share_scope='restricted'이고 test_sheet_permissions에 허용된 문제지
     */
    public function scopeWithSharedExams(Builder $query): Builder
    {
        if (!auth()->check()) {
            return $query;
        }

        $user = auth()->user();

        // root_admin은 모든 것을 볼 수 있음
        if ($user->userable_type === 'App\\Models\\Teacher') {
            $role = \Illuminate\Support\Facades\DB::table('teachers')
                ->where('id', $user->userable_id)->value('role');
            if ($role === 'root_admin') {
                return $query->withoutGlobalScope(\App\Models\Scopes\AcademyScope::class);
            }
        }

        $academyId = $user->academy_id;

        // AcademyScope를 해제하고 직접 조건을 걸어야 다른 학원 공유 문제지도 보임
        return $query->withoutGlobalScope(\App\Models\Scopes\AcademyScope::class)
            ->where(function ($q) use ($academyId) {
                // 1) 자기 학원 문제지
                $q->where('test_sheets.academy_id', $academyId)
                    // 2) share_scope='all'인 다른 학원 기출 문제지
                    ->orWhere(function ($q) use ($academyId) {
                        $q->where('test_sheets.academy_id', '!=', $academyId)
                            ->where('test_sheets.share_scope', 'all');
                    })
                    // 3) share_scope='restricted'이고 허용된 문제지
                    ->orWhere(function ($q) use ($academyId) {
                        $q->where('test_sheets.academy_id', '!=', $academyId)
                            ->where('test_sheets.share_scope', 'restricted')
                            ->whereHas('permissions', function ($q) use ($academyId) {
                                $q->where('academy_id', $academyId)
                                    ->where('is_allowed', true);
                            });
                    });
            });
    }

    /**
     * 문제지의 공유 상태 라벨
     */
    public function getShareScopeLabelAttribute(): string
    {
        return match ($this->share_scope) {
            'all' => '전체 공개',
            'restricted' => '일부 학원 공개',
            'academy' => '내 학원만',
            default => '내 학원만',
        };
    }

    public function getTargetGradeNamesAttribute()
    {

        $grades = [];
        foreach ($this->target_grades ?? [] as $grade) {
            $_grade = GradeSystem::find($grade);
            $grades[] = $_grade->display_name;
            break;
        }
        // if empty, than use users grade
        if (empty($grades) && auth()->check()) {
            $grades = auth()->user()->userable->classrooms->pluck('target_grades')
                ->map(function ($grade) {
                    return GradeSystem::find($grade[0])->display_name;
                });
            return $grades[0] ?? '';
        }
        return implode(', ', $grades);
    }

    public function answers()
    {
        return $this->hasMany(TestSheetAnswer::class);
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
     * target_group 조건들을 쿼리에 추가 (클래스 기반)
     */
    protected function addTargetGroupConditionsForClass($query, Classroom $classroom): void
    {
        // 학년 대상
        $query->where(function ($subQ) use ($classroom) {
            $targetGrades = $classroom->target_grades ?? [];

            if (!empty($targetGrades)) {
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
            ->orWhere(function ($subQ) use ($classroom) {
                $subQ->where('target_group', 'classroom')
                    ->where(function ($jsonQ) use ($classroom) {
                        $jsonQ->orWhereJsonContains('target_classrooms', $classroom->id)
                            ->orWhereJsonContains('target_classrooms', (string)$classroom->id);
                    });
            });

        // 레벨 대상
        if ($classroom->target_level && !empty($classroom->target_grades)) {
            $query->orWhere(function ($subQ) use ($classroom) {
                $subQ->where('target_group', 'level')
                    ->where(function ($jsonQ) use ($classroom) {
                        $jsonQ->orWhereJsonContains('target_levels', $classroom->target_level);
                    })
                    ->where(function ($jsonQ) use ($classroom) {
                        foreach ($classroom->target_grades as $gradeId) {
                            $jsonQ->orWhereJsonContains('target_grades', $gradeId)
                                ->orWhereJsonContains('target_grades', (string)$gradeId);
                        }
                    });
            });
        }
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
                    ->where(function ($jsonQ) use ($student) {
                        $jsonQ->whereJsonContains('target_students', $student->user->id)
                            ->orWhereJsonContains('target_students', (string)$student->user->id);
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

    public function submitAnswer(array $answers, array $dontKnowAnswers, int $userId, int $elapsedTime = 0): bool
    {
        return DB::transaction(function () use ($answers, $dontKnowAnswers, $userId, $elapsedTime) {
            // 1. 채점 및 리포트 생성
            $result = $this->grade($answers, $dontKnowAnswers, $userId);

            // 2. 답안 업데이트 또는 생성
            TestSheetAnswer::updateOrCreate(
                [
                    'test_sheet_id' => $this->id,
                    'user_id' => $userId,
                    'status' => 'pending'
                ],
                [
                    'answers' => $answers,
                    'correct_count' => $result['correctCount'],
                    'correct_count_report' => $result['correctCountReport'],
                    'status' => 'completed',
                    'time' => $elapsedTime
                ]
            );

            // 3. 원본 테스트인 경우에만 1차 오답 테스트 생성
            if ($this->isOriginal() && !empty($result['wrongQuestions'])) {
                $this->createFirstRetryTest($result['wrongQuestions'], $userId);
            }

            return true;
        });
    }

    protected function grade(array $answers, array  $dontKnowAnswers, $userId): array
    {
        $correctCount = 0;
        $correctCountReport = [];
        $wrongQuestions = [];

        foreach ($answers as $index => $answer) {
            $question = $this->questions[$index];
            $questionType = $question['question_type_id'];

            // Initialize report entry if not exists
            if (!isset($correctCountReport[$questionType])) {
                $name = QuestionCategory::find($questionType)->name;
                $correctCountReport[$questionType] = [
                    'name' => $name,
                    'total' => 0,
                    'correct' => 0,
                    'attempt' => 0  // 추가
                ];
            }
            $correctCountReport[$questionType]['total']++;

            if ($answer !== null) {
                $correctCountReport[$questionType]['attempt']++;
            }

            // 정답 여부 체크
            $isCorrect = $answer === $question['answer'];
            if ($isCorrect) {
                if ($this->use_score_table) {
                    $score = $this->parsed_score_table['table'][$index + 1] ?? 1;
                    $correctCount += $score;
                    $correctCountReport[$questionType]['correct'] += $score;
                } else {
                    $correctCount++;
                    $correctCountReport[$questionType]['correct']++;
                }
            } else {
                // 오답인 경우 저장
                $wrongQuestions[] = [
                    'original_question_seq' => $index,
                    'original_question_id' => $question['id'],
                ];
                if (User::find($userId))
                    WrongAnswerNote::create([
                        'student_id' => User::find($userId)->userable->id,
                        'question' => $question,
                        'wrong_answer' => $answer ?? null,
                        'dont_know' => $dontKnowAnswers[$index] ?? false
                    ]);
            }
        }

        // 배점표 사용 시 업데이트
        if ($this->use_score_table) {
            foreach ($correctCountReport as $typeId => &$report) {
                $typeTotal = 0;
                foreach ($this->questions as $index => $question) {
                    if ($question['question_type_id'] == $typeId) {
                        $score = $this->parsed_score_table['table'][$index + 1] ?? 1;
                        $typeTotal += $score;
                    }
                }
                $report['total'] = $typeTotal;
            }
        }

        return [
            'correctCount' => $correctCount,
            'correctCountReport' => $correctCountReport,
            'wrongQuestions' => $wrongQuestions
        ];
    }

    protected function createFirstRetryTest(array $wrongQuestions, int $userId): void
    {
        // 각 틀린 문제의 첫 번째 child 문제 찾기
        $validQuestions = [];
        $validMappings = [];
        foreach ($wrongQuestions as $wrong) {
            $childQuestion = Question::where('parent_question_id', $wrong['original_question_id'])
                ->orderBy('id', 'asc')
                ->first();

            if ($childQuestion) {
                $wrong['new_question_seq'] = count($validQuestions);
                $wrong['new_question_id'] = $childQuestion->id;
                $validMappings[] = $wrong;
                $validQuestions[] = $childQuestion->toArray();
            }
        }

        // 새로운 테스트 시트 생성
        $newTestSheet = new static([
            ...$this->only(['title', 'sub_title', 'scopes', 'user_id', 'tags', 'show_explanation_video']),
            'use_score_table' => false,
            'name' => $this->name . ' (오답 유사 유형)',
            'target_group' => 'student',
            'target_students' => [$userId],
            'questions' => $validQuestions,
            'is_auto' => false,
            'status' => 'progress',
            'start_date' => now(),
            'end_date' => $this->end_date,
            'target_grades' => [],
            'target_levels' => [],
            'target_classrooms' => [],
            'print_layout' => $this->print_layout,
        ]);
        $newTestSheet->save();

        // WrongAnswerTestSheet 생성 또는 업데이트
        WrongAnswerTestSheet::updateOrCreate(
            [
                'original_test_sheet_id' => $this->id,
                'user_id' => $userId,
                'retry_count' => 1
            ],
            [
                'test_sheet_id' => $newTestSheet->id,
                'is_linked_to_original' => true,
                'wrong_answer_questions' => $validMappings,
            ]
        );
    }

    /**
     * 시험지를 마감하고 모든 대상 학생들의 답안 상태를 업데이트합니다.
     */
    public function complete(): bool
    {
        return DB::transaction(function () {
            // 1. 시험지 상태를 completed로 변경
            $this->status = 'completed';
            $this->end_date = now();
            $this->save();

            // 2. 대상 학생들의 답안 처리
            $targetStudents = $this->getTargetStudents();
            foreach ($targetStudents as $student) {
                $answer = TestSheetAnswer::where([
                    'test_sheet_id' => $this->id,
                    'user_id' => $student->user->id,
                ])->first();

                if (!$answer) {
                    // 답안이 없는 경우 빈 답안으로 제출 처리
                    $emptyAnswers = array_fill(0, count($this->questions), null);
                    $this->submitAnswer($emptyAnswers, [], $student->user->id, 0);
                } else if ($answer->status === 'pending') {
                    // 진행 중인 답안이 있는 경우 현재 상태로 제출 처리
                    $this->submitAnswer($answer->answers, $answer->dont_know_answers, $student->user->id, $answer->time);
                }
            }
            if ($this->isOriginal()) {

                // 3. 리포트 생성
                $this->generateReport();

                // 3-1. 개인별 주간 리포트 생성
                $this->generateWeeklyReport();

                // 3-2. 개인별 숙제 주간 리포트 생성
                $this->generateHomeworkWeeklyReport();

                // 4. 연관된 1차 오답 테스트들도 함께 마감
                $firstRetryTests = $this->originalWrongAnswerTest()
                    ->where('retry_count', 1)
                    ->get();

                foreach ($firstRetryTests as $firstRetryTest) {
                    $firstRetryTest->testSheet->complete();
                }

                // 5. 2차 오답 테스트 생성 (pending 상태로)
                foreach ($firstRetryTests as $firstRetryTest) {
                    $this->createSecondRetryTest($firstRetryTest);
                }
            } else if ($this->wrongAnswerTestSheets()->where('retry_count', 2)->exists()) {
                $this->generateSecondRetryReport();
            }

            return true;
        });
    }

    public function completeSecondRetryTests(): void
    {
        // 이 시험지의 1차 오답 테스트들을 통해 2차 오답 테스트들을 찾습니다
        $secondRetryTests = WrongAnswerTestSheet::query()
            ->where('original_test_sheet_id', $this->id)
            ->where('retry_count', 2)
            ->with('testSheet')
            ->get();

        // 각 2차 오답 테스트를 마감합니다
        foreach ($secondRetryTests as $retryTest) {
            if ($retryTest->testSheet) {
                $retryTest->testSheet->complete();
            }
        }
    }

    protected function createSecondRetryTest(WrongAnswerTestSheet $firstRetryTest): void
    {
        $originalWrongQuestions = $firstRetryTest->wrong_answer_questions;

        // 각 원본 오답 문제의 두 번째 child 문제 찾기
        $validQuestions = [];
        $validMappings = [];
        if (!empty($originalWrongQuestions)) {
            foreach ($originalWrongQuestions as $originalWrong) {
                $childQuestion = Question::where('parent_question_id', $originalWrong['original_question_id'])
                    ->orderBy('id', 'asc')
                    ->skip(1)
                    ->first();

                $mapping = [
                    'original_question_seq' => $originalWrong['original_question_seq'],
                    'original_question_id' => $originalWrong['original_question_id'],
                    'new_question_seq' => $childQuestion ? count($validQuestions) : null,
                    'new_question_id' => $childQuestion ? $childQuestion->id : null
                ];
                $validMappings[] = $mapping;

                if ($childQuestion) {
                    $validQuestions[] = $childQuestion->toArray();
                }
            }
        }

        // 새로운 테스트 시트 생성
        $newTestSheet = new static([
            ...$firstRetryTest->originalTestSheet->only(['title', 'sub_title', 'scopes', 'user_id', 'show_explanation_video']),
            'tags' => ['오답 테스트'],
            'name' => $firstRetryTest->originalTestSheet->name . ' (오답 테스트)',
            'target_group' => 'student',
            'target_students' => [$firstRetryTest->user_id],
            'questions' => $validQuestions,
            'status' => 'pending',  // 수동 출제를 위해 pending으로 설정
            'use_score_table' => false,
            'is_auto' => false,
            'start_date' => null,
            'end_date' => null,
            'target_grades' => [],
            'target_levels' => [],
            'target_classrooms' => [],
            'print_layout' => $firstRetryTest->originalTestSheet->print_layout,
        ]);
        $newTestSheet->save();

        // WrongAnswerTestSheet 생성
        WrongAnswerTestSheet::create([
            'original_test_sheet_id' => $firstRetryTest->original_test_sheet_id,
            'test_sheet_id' => $newTestSheet->id,
            'user_id' => $firstRetryTest->user_id,
            'retry_count' => 2,
            'is_linked_to_original' => false,
            'wrong_answer_questions' => $validMappings
        ]);
    }

    public function generateReport(): bool
    {
        return DB::transaction(function () {
            // 1. 전체 문제 수 계산
            $totalScore = $this->total_score;
            if ($totalScore === 0) return false;

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

            // 1-2. 반별 답안 수집
            $classroomAnalyses = [];
            foreach ($answers as $answer) {
                $student = $targetStudents->firstWhere('user.id', $answer->user_id);
                if (!$student) continue;

                $classroom = $this->getRepresentativeClassroom($student);
                if (!$classroom) continue;

                if (!isset($classroomAnalyses[$classroom->id])) {
                    $classroomAnalyses[$classroom->id] = [
                        'classroom_id' => $classroom->id,
                        'classroom_name' => $classroom->name,
                        'answers' => [],
                        'level_analysis' => null  // 나중에 계산될 분석 결과 저장용
                    ];
                }
                $classroomAnalyses[$classroom->id]['answers'][] = $answer;
            }

            // 2. 각 반별로 레벨 분석 수행
            foreach ($classroomAnalyses as $classroomId => &$classroomData) {
                $classroomData['level_analysis'] = $this->generateClassroomLevelAnalysis(
                    $this->questions,
                    collect($classroomData['answers'])
                );
            }

            // 2. 학생별 대표 classroom과 점수 수집
            $studentData = [];
            $classroomScores = [];
            $levelScores = [];
            $gradeScores = [];
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
                                'level' => [],
                                'grade' => []
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

                        if (!isset($typeScores[$typeName]['grade'][$classroom->target_grades[0]])) {
                            $typeScores[$typeName]['grade'][$classroom->target_grades[0]] = [];
                        }
                        $typeScores[$typeName]['grade'][$classroom->target_grades[0]][] = [
                            'correct' => $typeCorrect,
                            'total' => $typeTotal
                        ];


                        $typeStats[$typeName] = [
                            'correct' => $typeCorrect,
                            'total' => $typeTotal,
                            'attempt' => $typeData['attempt'] ?? 0
                        ];
                    }
                }

                // 이행도 계산 추가
                $attemptedCount = count(array_filter($answer->answers, function ($ans) {
                    return $ans !== null;
                }));
                $totalQuestions = $this->total_score;
                $totalQuestionCount = count($this->questions);

                // 개인 레벨 분석
                $personalLevelAnalysis = $this->generateLevelAnalysis(
                    $this->questions,
                    $answer->answers
                );

                // 계층적 분석 (기존 코드)
                $hierarchicalAnalysis = $this->generateHierarchicalAnalysis(
                    $this->questions,
                    $answer
                );

                $detailedHierarchicalAnalysis = $this->generateHierarchicalAnalysis(
                    $this->questions,
                    $answer,
                    false
                );

                // 점수별 분석 추가
                $scoreAnalysis = $this->generateScoreAnalysis(
                    $this->questions,
                    $answer->answers
                );

                $classroomLevelAnalysis = $classroomAnalyses[$classroom->id]['level_analysis'];

                // 학생 데이터 저장
                $studentData[] = [
                    'student_id' => $student->id,
                    'user_id' => $answer->user_id,
                    'name' => $student->user->name,
                    'score' => $score,
                    'classroom_id' => $classroom->id,
                    'classroom_name' => $classroom->name,
                    'level' => $classroom->target_level,
                    'grade' => $classroom->target_grades[0] ?? null,
                    'type_scores' => $typeStats,
                    'attempted_count' => $attemptedCount,
                    'total_questions' => $totalQuestions,
                    'attempt_rate' => $calculatePercentage($attemptedCount, $totalQuestionCount),
                    'hierarchical_analysis' => $hierarchicalAnalysis,
                    'detailed_hierarchical_analysis' => $detailedHierarchicalAnalysis,
                    'level_analysis' => [
                        'personal' => $personalLevelAnalysis,
                        'classroom' => $classroomLevelAnalysis
                    ],
                    'score_analysis' => $scoreAnalysis
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

                // 학년별 점수 집계
                $grade = $classroom->target_grades[0] ?? null;
                if ($grade) {
                    if (!isset($gradeScores[$grade])) {
                        $gradeScores[$grade] = [];
                    }
                    $gradeScores[$grade][] = $score;
                }
            }

            // 3. 전체 평균 계산
            $totalAverage = $calculateAverage($allScores);
            $totalStudentsCount = count($allScores);

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

                // 학년별 점수 정렬 및 통계
                $gradeScore = $gradeScores[$data['grade']] ?? [];
                rsort($gradeScore);

                // 각 평균 계산
                $classroomAverage = $calculateAverage($classroomScore);
                $levelAverage = $calculateAverage($levelScore);
                $gradeAverage = $calculateAverage($gradeScore);

                // 각 학생 수 계산
                $classroomStudentsCount = count($classroomScore);
                $levelStudentsCount = count($levelScore);
                $gradeStudentsCount = count($gradeScore);

                // 문제 유형별 통계 계산
                $questionTypeStats = [];
                foreach ($data['type_scores'] as $type => $scores) {
                    // 해당 유형의 모든 통계 데이터
                    $typeAllScores = $typeScores[$type]['all'];
                    $typeClassroomScores = $typeScores[$type]['classroom'][$data['classroom_id']] ?? [];
                    $typeLevelScores = $typeScores[$type]['level'][$data['level']] ?? [];
                    $typeGradeScores = $typeScores[$type]['grade'][$data['grade']] ?? [];


                    // 각 범주별 평균 계산
                    $calculateTypeAverage = function ($scoresArray, $total) {
                        if (empty($scoresArray)) return 0;

                        // 각 학생의 맞은 개수만 추출하여 평균 계산
                        $totalCorrect = array_sum(array_column($scoresArray, 'correct'));
                        $studentCount = count($scoresArray);

                        // 학생 수로 나누어 평균 계산
                        return $studentCount > 0 ? $totalCorrect / $studentCount : 0;
                    };

                    $typeAllAvg = $calculateTypeAverage($typeAllScores, $scores['total']);
                    $typeClassroomAvg = $calculateTypeAverage($typeClassroomScores, $scores['total']);
                    $typeLevelAvg = $calculateTypeAverage($typeLevelScores, $scores['total']);
                    $typeGradeAvg = $calculateTypeAverage($typeGradeScores, $scores['total']);

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

                    $gradeScoreArray = $getScoreArray($typeGradeScores);
                    rsort($gradeScoreArray);

                    $questionTypeStats[] = [
                        'name' => $type,
                        'personal_score' => $scores['correct'],
                        'personal_total' => $scores['total'],
                        'personal_score_percentage' => $calculatePercentage($scores['correct'], $scores['total']),
                        'classroom_average' => $typeClassroomAvg,
                        'classroom_total' => $scores['total'], // 총 문제 수는 동일
                        'classroom_average_percentage' => $calculatePercentage($typeClassroomAvg, $scores['total']),
                        'level_average' => $typeLevelAvg,
                        'level_total' => $scores['total'], // 총 문제 수는 동일
                        'level_average_percentage' => $calculatePercentage($typeLevelAvg, $scores['total']),
                        'total_average' => $typeAllAvg,
                        'total_total' => $scores['total'], // 총 문제 수는 동일
                        'total_average_percentage' => $calculatePercentage($typeAllAvg, $scores['total']),
                        'classroom_rank' => array_search($scores['correct'], $classroomScoreArray) + 1,
                        'level_rank' => array_search($scores['correct'], $levelScoreArray) + 1,
                        'attempt_count' => $scores['attempt'] ?? 0,
                        'attempt_rate' => $calculatePercentage($scores['attempt'] ?? 0, $scores['total']),

                        'grade_average' => $typeGradeAvg,
                        'grade_average_percentage' => $calculatePercentage($typeGradeAvg, $scores['total']),
                        'grade_rank' => array_search($scores['correct'], $gradeScoreArray) + 1,
                        'students_count' => $totalStudentsCount,
                        'classroom_students_count' => $classroomStudentsCount,
                        'level_students_count' => $levelStudentsCount,
                        'grade_students_count' => $gradeStudentsCount
                    ];
                }

                $report['students'][] = [
                    'test_sheet_id' => $this->id,  // 추가
                    'rank' => array_search($data['score'], $allScores) + 1,
                    'student_id' => $data['student_id'],
                    'user_id' => $data['user_id'],
                    'student_name' => $data['name'],
                    'classroom_id' => $data['classroom_id'],
                    'classroom_name' => $data['classroom_name'],
                    'personal_score' => $data['score'],
                    'personal_score_percentage' => $calculatePercentage($data['score'], $totalScore),
                    'classroom_average' => $classroomAverage,
                    'classroom_average_percentage' => $calculatePercentage($classroomAverage, $totalScore),
                    'level_average' => $levelAverage,
                    'level_average_percentage' => $calculatePercentage($levelAverage, $totalScore),
                    'total_average' => $totalAverage,
                    'total_average_percentage' => $calculatePercentage($totalAverage, $totalScore),
                    'classroom_rank' => array_search($data['score'], $classroomScore) + 1,
                    'level_rank' => array_search($data['score'], $levelScore) + 1,
                    'question_types' => $questionTypeStats,
                    'attempted_count' => $data['attempted_count'],
                    'total_questions' => $data['total_questions'],
                    'attempt_rate' => $data['attempt_rate'],
                    'hierarchical_analysis' => $data['hierarchical_analysis'],
                    'detailed_hierarchical_analysis' => $data['detailed_hierarchical_analysis'],
                    'level_analysis' => $data['level_analysis'],
                    'score_analysis' => $data['score_analysis'],

                    'grade_average' => $gradeAverage,
                    'grade_average_percentage' => $calculatePercentage($gradeAverage, $totalScore),
                    'grade_rank' => array_search($data['score'], $gradeScore) + 1,

                    'students_count' => $totalStudentsCount,
                    'classroom_students_count' => $classroomStudentsCount,
                    'level_students_count' => $levelStudentsCount,
                    'grade_students_count' => $gradeStudentsCount
                ];
            }

            // 5. 결과 저장
            $this->report = $report['students'];
            return $this->save();
        });
    }

    public function generateWeeklyReport(): void
    {
        // 숙제 태그가 있는 경우 제외
        if (in_array('숙제', $this->tags ?? [])) {
            return;
        }

        // 보고서가 없는 경우 제외
        if (empty($this->report)) {
            return;
        }

        $startDate = Carbon::parse($this->start_date);
        $year = $startDate->year;
        $week = $startDate->isoWeek();

        // 시험 범위 정리
        $scopes = collect($this->scopes ?? [])
            ->map(function ($scope) {
                return QuestionCategory::find($scope)->name ?? '';
            })
            ->filter()
            ->join(', ');

        foreach ($this->report as $studentReport) {

            $student = Student::find($studentReport['student_id']);
            if (!$student) continue;

            $classroom = Classroom::find($studentReport['classroom_id'] ?? null);

            if (!$classroom) continue;

            // 테스트 결과 데이터 구성
            $testData = [
                'test_sheet_id' => $this->id,  // 추가
                'target_group' => $this->target_group,  // 추가: 대상 그룹 정보
                'date' => $startDate->format('Y-m-d'),
                'test_name' => $this->name,
                'scopes' => $scopes,
                'total' => [
                    'personal_score' => $studentReport['personal_score'],
                    'total_questions' => $studentReport['total_questions'],
                    'classroom_average' => $studentReport['classroom_average'],
                    'level_average' => $studentReport['level_average'],
                    'classroom_rank' => $studentReport['classroom_rank'],

                    'grade_average' => $studentReport['grade_average'],
                    'level_rank' => $studentReport['level_rank'],
                    'grade_rank' => $studentReport['grade_rank'],
                    'students_count' => $studentReport['students_count'],
                    'classroom_students_count' => $studentReport['classroom_students_count'],
                    'level_students_count' => $studentReport['level_students_count'],
                    'grade_students_count' => $studentReport['grade_students_count']
                ],
                'by_types' => []
            ];

            // 문제 유형별 상세 데이터 추가
            foreach ($studentReport['question_types'] as $typeData) {
                $testData['by_types'][] = [
                    'name' => $typeData['name'],
                    'scores' => [
                        'personal_score' => $typeData['personal_score'],
                        'total_questions' => $typeData['personal_total'],
                        'classroom_average' => $typeData['classroom_average'],
                        'level_average' => $typeData['level_average'],
                        'classroom_rank' => $typeData['classroom_rank'],

                        'grade_average' => $typeData['grade_average'],
                        'level_rank' => $typeData['level_rank'],
                        'grade_rank' => $typeData['grade_rank'],
                        'students_count' => $typeData['students_count'],
                        'classroom_students_count' => $typeData['classroom_students_count'],
                        'level_students_count' => $typeData['level_students_count'],
                        'grade_students_count' => $typeData['grade_students_count']
                    ]
                ];
            }

            // 주간 보고서 조회
            $weeklyReport = WeeklyTestReport::firstOrNew([
                'student_id' => $student->id,
                'classroom_id' => $classroom->id,
                'year' => $year,
                'week' => $week,
                'type' => 'test'
            ]);

            // 현재 저장된 리포트 데이터 가져오기
            $reportData = $weeklyReport->report ?? [];

            // 중복 체크 부분
            $exists = false;
            foreach ($reportData as $key => $existingTest) {
                if (($existingTest['test_sheet_id'] ?? null) === $testData['test_sheet_id']) {
                    // 기존 데이터 업데이트
                    $reportData[$key] = $testData;
                    $exists = true;
                    break;
                }
            }

            // 동일한 테스트가 없으면 새로 추가
            if (!$exists) {
                $reportData[] = $testData;
            }

            // 보고서 저장
            $weeklyReport->report = $reportData;
            $weeklyReport->save();
        }
    }

    public function generateHomeworkWeeklyReport(): void
    {
        // 숙제 태그가 없는 경우 제외
        if (!in_array('숙제', $this->tags ?? [])) {
            return;
        }

        // 보고서가 없는 경우 제외
        if (empty($this->report)) {
            return;
        }

        $startDate = Carbon::parse($this->start_date);
        $year = $startDate->year;
        $week = $startDate->isoWeek();

        // 시험 범위 정리
        $scopes = collect($this->scopes ?? [])
            ->map(function ($scope) {
                return QuestionCategory::find($scope)->name ?? '';
            })
            ->filter()
            ->join(', ');

        foreach ($this->report as $studentReport) {
            $student = Student::find($studentReport['student_id']);
            if (!$student) continue;

            $classroom = Classroom::find($studentReport['classroom_id'] ?? null);
            if (!$classroom) continue;

            // 숙제 결과 데이터 구성
            $homeworkData = [
                'test_sheet_id' => $this->id,  // 추가
                'date' => $startDate->format('Y-m-d'),
                'homework_name' => $this->name,
                'scopes' => $scopes,
                'total' => [
                    'correct_count' => $studentReport['personal_score'],
                    'total_count' => $studentReport['total_questions'],
                    'attempt_rate' => $studentReport['attempt_rate'],
                    'correct_rate' => $studentReport['personal_score_percentage']
                ],
                'by_types' => []
            ];

            // 문제 유형별 상세 데이터 추가
            foreach ($studentReport['question_types'] as $typeData) {
                $homeworkData['by_types'][] = [
                    'name' => $typeData['name'],
                    'correct_count' => $typeData['personal_score'],
                    'total_count' => $typeData['personal_total'],
                    'attempt_rate' => $typeData['attempt_rate'],
                    'correct_rate' => $typeData['personal_score_percentage']
                ];
            }

            // 주간 보고서 조회
            $weeklyReport = WeeklyTestReport::firstOrNew([
                'student_id' => $student->id,
                'classroom_id' => $classroom->id,
                'year' => $year,
                'week' => $week,
                'type' => 'homework'
            ]);

            // 현재 저장된 리포트 데이터 가져오기
            $reportData = $weeklyReport->report ?? [];

            // 중복 체크 부분
            $exists = false;
            foreach ($reportData as $key => $existingHomework) {
                if (($existingHomework['test_sheet_id'] ?? null) === $homeworkData['test_sheet_id']) {
                    // 기존 데이터 업데이트
                    $reportData[$key] = $homeworkData;
                    $exists = true;
                    break;
                }
            }

            // 동일한 숙제가 없으면 새로 추가
            if (!$exists) {
                $reportData[] = $homeworkData;
            }

            // 보고서 저장
            $weeklyReport->report = $reportData;
            $weeklyReport->save();
        }
    }

    public function generateSecondRetryReport(): bool
    {
        return DB::transaction(function () {
            // 1. 이 시험지가 2차 오답 테스트인지 확인
            $wrongAnswerTest = $this->wrongAnswerTestSheets()
                ->where('retry_count', 2)
                ->first();

            if (!$wrongAnswerTest) {
                return false;
            }

            // 2. 연관된 테스트들 조회
            $originalTestSheet = TestSheet::find($wrongAnswerTest->original_test_sheet_id);
            $firstRetryTest = WrongAnswerTestSheet::where('original_test_sheet_id', $wrongAnswerTest->original_test_sheet_id)
                ->where('user_id', $wrongAnswerTest->user_id)
                ->where('retry_count', 1)
                ->with('testSheet')
                ->first();

            if (!$originalTestSheet || !$firstRetryTest) {
                return false;
            }

            // 3. 각 테스트의 답안 조회
            $originalAnswer = TestSheetAnswer::where('test_sheet_id', $originalTestSheet->id)
                ->where('user_id', $wrongAnswerTest->user_id)
                ->first();

            $firstRetryAnswer = TestSheetAnswer::where('test_sheet_id', $firstRetryTest->testSheet->id)
                ->where('user_id', $wrongAnswerTest->user_id)
                ->first();

            $secondRetryAnswer = TestSheetAnswer::where('test_sheet_id', $this->id)
                ->where('user_id', $wrongAnswerTest->user_id)
                ->first();

            if (!$originalAnswer || !$firstRetryAnswer || !$secondRetryAnswer) {
                return false;
            }

            // 4. 리포트 생성
            $report = [];
            foreach ($wrongAnswerTest->wrong_answer_questions as $question) {
                $originalSeq = $question['original_question_seq'];

                // 원본 문제의 오답 여부는 이미 알고 있음 (틀렸기 때문에 2차까지 왔음)
                // 1차 테스트 정답 확인
                $firstRetryQuestionMapping = collect($firstRetryTest->wrong_answer_questions)
                    ->firstWhere('original_question_seq', $originalSeq);
                $firstRetrySeq = $firstRetryQuestionMapping['new_question_seq'];
                $firstRetryCorrect = $firstRetryAnswer->answers[$firstRetrySeq] ===
                    $firstRetryTest->testSheet->questions[$firstRetrySeq]['answer'];

                // 2차 테스트 정답 확인
                $secondRetrySeq = $question['new_question_seq'];
                $secondRetryCorrect = $secondRetrySeq !== null ?
                    ($secondRetryAnswer->answers[$secondRetrySeq] === $this->questions[$secondRetrySeq]['answer']) :
                    null;

                $report[] = [
                    'original_seq' => $originalSeq + 1, // 1부터 시작하는 번호로 표시
                    'first_retry_correct' => $firstRetryCorrect,
                    'second_retry_correct' => $secondRetryCorrect ?? '없음'
                ];
            }

            // 5. 리포트 저장
            $this->report = $report;
            return $this->save();
        });
    }

    public function getRepresentativeClassroom(Student $student): ?Classroom
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

    public function getTargetStudents()
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

    public function getTotalScoreAttribute()
    {
        if ($this->use_score_table) {
            return $this->parsed_score_table['total_score'];
        }

        return count($this->questions);
    }

    public function getDueTextAttribute()
    {
        if (!$this->end_date) {
            return null;
        }

        $now = Carbon::now();
        $endDate = Carbon::parse($this->end_date);

        // 이미 기한이 지난 경우
        if ($now->gt($endDate)) {
            return null;
        }

        $diffInDays = abs($endDate->diffInDays($now));
        $diffInHours = abs($endDate->diffInHours($now));
        $diffInMinutes = abs($endDate->diffInMinutes($now));

        if ($diffInDays >= 1) {
            return round($diffInDays) . "일";
        } elseif ($diffInHours >= 1) {
            return round($diffInHours) . "시간";
        } else {
            // 1분 미만이어도 최소 1분으로 표시
            $minutes = max(1, round($diffInMinutes));
            return "{$minutes}분";
        }
    }

    public function wrongAnswerTestSheets()
    {
        return $this->hasMany(WrongAnswerTestSheet::class, 'test_sheet_id');
    }

    public function originalWrongAnswerTest()
    {
        return $this->hasMany(WrongAnswerTestSheet::class, 'original_test_sheet_id');
    }

    public function isOriginal(): bool
    {
        return !$this->wrongAnswerTestSheets()->exists();
    }

    public function hasWrongAnswerTests(): bool
    {
        return $this->originalWrongAnswerTest()->exists();
    }

    public function scopeOriginals($query)
    {
        return $query->whereDoesntHave('wrongAnswerTestSheets');
    }

    /**
     * 문제가 1개 이상인 시험지 조회 스코프
     */
    public function scopeHasQuestions(Builder $query): Builder
    {
        return $query->whereJsonLength('questions', '>', 0);
    }

    private function generateHierarchicalAnalysis($questions, $answer, $fromRoot = true)
    {
        $analysis = [];
        $answers = $answer->answers;
        $dontKnowAnswers = $answer->dont_know_answers;

        foreach ($questions as $index => $question) {
            $hierarchy = $this->getQuestionTypeHierarchy($question['question_type_id'], $fromRoot);
            if (!$hierarchy) continue;

            $major = $hierarchy['major'] ?? '기타';
            $middle = $hierarchy['middle'] ?? '기타';
            $type = $hierarchy['type'];
            $level = $question['level'] ?? 1;

            // 초기화
            if (!isset($analysis[$major])) {
                $analysis[$major] = [
                    'name' => $major,
                    'total' => 0,
                    'correct' => 0,
                    'sub_categories' => []
                ];
            }
            if (!isset($analysis[$major]['sub_categories'][$middle])) {
                $analysis[$major]['sub_categories'][$middle] = [
                    'name' => $middle,
                    'total' => 0,
                    'correct' => 0,
                    'types' => []
                ];
            }
            if (!isset($analysis[$major]['sub_categories'][$middle]['types'][$type])) {
                $analysis[$major]['sub_categories'][$middle]['types'][$type] = [
                    'name' => $type,
                    'total' => 0,
                    'correct' => 0,
                    'levels' => []
                ];
            }
            if (!isset($analysis[$major]['sub_categories'][$middle]['types'][$type]['levels'][$level])) {
                $analysis[$major]['sub_categories'][$middle]['types'][$type]['levels'][$level] = [
                    'level' => $level,
                    'total' => 0,
                    'correct' => 0,
                    'percentage' => 0,
                    'dont_know_answers_count' => 0
                ];
            }

            // 카운트 증가
            $isCorrect = $answers[$index] === $question['answer'];

            // 모름
            $isDontKnow = ($dontKnowAnswers[$index] ?? false) === true;

            // 대단원 통계
            $analysis[$major]['total']++;
            if ($isCorrect) $analysis[$major]['correct']++;

            // 중단원 통계
            $analysis[$major]['sub_categories'][$middle]['total']++;
            if ($isCorrect) $analysis[$major]['sub_categories'][$middle]['correct']++;

            // 문제 유형 통계
            $analysis[$major]['sub_categories'][$middle]['types'][$type]['total']++;
            if ($isCorrect) $analysis[$major]['sub_categories'][$middle]['types'][$type]['correct']++;

            // 레벨별 통계
            $analysis[$major]['sub_categories'][$middle]['types'][$type]['levels'][$level]['total']++;
            if ($isCorrect) $analysis[$major]['sub_categories'][$middle]['types'][$type]['levels'][$level]['correct']++;

            if ($isDontKnow) {
                $analysis[$major]['sub_categories'][$middle]['types'][$type]['levels'][$level]['dont_know_answers_count']++;
            }
        }

        // 정답률 계산 및 구조 정리
        foreach ($analysis as &$major) {
            $major['percentage'] = $major['total'] > 0 ?
                round(($major['correct'] / $major['total']) * 100, 2) : 0;

            foreach ($major['sub_categories'] as &$middle) {
                $middle['percentage'] = $middle['total'] > 0 ?
                    round(($middle['correct'] / $middle['total']) * 100, 2) : 0;

                foreach ($middle['types'] as &$type) {
                    $type['percentage'] = $type['total'] > 0 ?
                        round(($type['correct'] / $type['total']) * 100, 2) : 0;

                    foreach ($type['levels'] as &$level) {
                        $level['percentage'] = $level['total'] > 0 ?
                            round(($level['correct'] / $level['total']) * 100, 2) : 0;
                    }
                    // 레벨을 숫자순으로 정렬
                    ksort($type['levels']);
                }
            }
        }

        return $analysis;
    }

    private function getQuestionTypeHierarchy($typeId, $fromRoot = true)
    {
        $category = QuestionCategory::find($typeId);
        if (!$category) {
            return null;
        }

        $parent = $category->parent()->first();
        $grandParent = $parent ? $parent->parent()->first() : null;
        if ($fromRoot) {
            return [
                // 'major' => $grandParent ? $grandParent->name : null,
                // 'middle' => $parent ? $parent->name : null,
                // 'type' => $category->name,
                'major' => $grandParent ? $grandParent?->parent()?->first()?->name : null,
                'middle' => $parent ? $parent?->parent()?->first()?->name : null,
                'type' => $parent->name,
            ];
        } else {
            return [
                'major' => $grandParent ? $grandParent->name : null,
                'middle' => $parent ? $parent->name : null,
                'type' => $category->name,
                //'major' => $grandParent ? $grandParent?->parent()?->first()?->name : null,
                //'middle' => $parent ? $parent?->parent()?->first()?->name : null,
                //'type' => $parent->name,
            ];
        }
    }

    private function generateLevelAnalysis($questions, $answers): array
    {
        $analysis = [];

        // 레벨별 초기화 (1~5)
        for ($level = 1; $level <= 5; $level++) {
            $analysis[$level] = [
                'level' => $level,
                'total' => 0,
                'correct' => 0,
                'percentage' => 0
            ];
        }

        // 문제별 분석
        foreach ($questions as $index => $question) {
            $level = $question['level'] ?? 1;
            $isCorrect = $answers[$index] === $question['answer'];

            $analysis[$level]['total']++;
            if ($isCorrect) {
                $analysis[$level]['correct']++;
            }
        }

        // 정답률 계산
        foreach ($analysis as &$levelData) {
            $levelData['percentage'] = $levelData['total'] > 0
                ? round(($levelData['correct'] / $levelData['total']) * 100, 2)
                : 0;
        }

        return $analysis;
    }

    private function generateClassroomLevelAnalysis($questions, $classroomAnswers): array
    {
        $analysis = [];

        // 레벨별 초기화 (1~5)
        for ($level = 1; $level <= 5; $level++) {
            $analysis[$level] = [
                'level' => $level,
                'total' => 0,
                'correct' => 0,
                'percentage' => 0,
                'answered_students' => 0  // 해당 레벨 문제를 푼 학생 수
            ];
        }

        // 문제별, 학생별 분석
        foreach ($questions as $index => $question) {
            $level = $question['level'] ?? 1;
            $analysis[$level]['total']++;

            foreach ($classroomAnswers as $answer) {
                if (isset($answer->answers[$index])) {
                    $analysis[$level]['answered_students']++;
                    if ($answer->answers[$index] === $question['answer']) {
                        $analysis[$level]['correct']++;
                    }
                }
            }
        }

        // 정답률 계산
        foreach ($analysis as &$levelData) {
            $totalPossibleAnswers = $levelData['answered_students'];
            $levelData['percentage'] = $totalPossibleAnswers > 0
                ? round(($levelData['correct'] / $totalPossibleAnswers) * 100, 2)
                : 0;
        }

        return $analysis;
    }

    private function generateScoreAnalysis($questions, $answers): array
    {
        $analysis = [];

        // 문제별로 순회하면서 분석
        foreach ($questions as $index => $question) {
            // 기본 점수는 1점, use_score_table이 true인 경우 배점표에서 가져옴
            $score = $this->use_score_table
                ? ($this->parsed_score_table['table'][$index + 1] ?? 1)
                : 1;

            // 계층 정보 가져오기
            $hierarchy = $this->getQuestionTypeHierarchy($question['question_type_id'], false);
            if (!$hierarchy) continue;

            $major = $hierarchy['major'] ?? '기타';
            $middle = $hierarchy['middle'] ?? '기타';
            $type = $hierarchy['type'];
            $level = $question['level'] ?? 1;

            // 대단원이 없으면 초기화
            if (!isset($analysis[$major])) {
                $analysis[$major] = [
                    'name' => $major,
                    'types' => []
                ];
            }

            // 유형이 없으면 초기화
            if (!isset($analysis[$major]['types'][$type])) {
                $analysis[$major]['types'][$type] = [
                    'name' => $type,
                    'middle' => $middle,
                    'scores' => []
                ];
            }

            // 점수가 없으면 초기화
            if (!isset($analysis[$major]['types'][$type]['scores'][$score])) {
                $analysis[$major]['types'][$type]['scores'][$score] = [
                    'score' => $score,
                    'levels' => array_fill(1, 5, [
                        'total_possible' => 0,    // 획득 가능한 총점
                        'total_earned' => 0,      // 실제 획득한 총점
                    ])
                ];
            }

            // 정답 여부 확인
            $isCorrect = $answers[$index] === $question['answer'];

            // 해당 레벨의 통계 업데이트
            $analysis[$major]['types'][$type]['scores'][$score]['levels'][$level]['total_possible'] += $score;
            if ($isCorrect) {
                $analysis[$major]['types'][$type]['scores'][$score]['levels'][$level]['total_earned'] += $score;
            }
        }

        // 데이터 정리 (점수별 정렬 등)
        foreach ($analysis as &$majorData) {
            foreach ($majorData['types'] as &$typeData) {
                // 점수를 키로 가진 배열을 점수 순으로 정렬
                ksort($typeData['scores']);
            }
        }

        return $analysis;
    }


    public static function getFormattedAnalysisReport(
        Student $student,
        string $dateFrom,
        string $dateUntil,
        int $classroomId
    ): Collection {
        if (!$student || !$dateFrom || !$dateUntil || !$classroomId) {
            return collect();
        }

        $startDate = explode('/', $dateFrom)[0];
        $endDate = explode('/', $dateUntil)[1];
        $startCarbon = Carbon::parse($startDate);
        $endCarbon = Carbon::parse($endDate);

        // 조건에 맞는 테스트 시트 조회
        $testSheets = static::query()
            ->where('status', 'completed')
            ->originals()
            ->whereHas('answers', function ($query) use ($student) {
                $query->where('user_id', $student->user->id);
            })
            ->whereBetween('start_date', [$startCarbon, $endCarbon])
            ->orderBy('start_date', 'asc')
            ->get()
            ->filter(function ($testSheet) use ($student, $classroomId) {
                $classroom = $testSheet->getRepresentativeClassroom($student);
                return $classroom && $classroom->id == $classroomId;
            });

        // 테스트 시트별로 리포트 데이터 구성
        return $testSheets->map(function ($testSheet) use ($student) {
            $report = collect($testSheet->report)
                ->firstWhere('student_id', $student->id);

            if (!$report) return null;

            $weekDate = Carbon::parse($testSheet->start_date);
            $week_label = sprintf(
                '%d년 %d월 %d주차',
                $weekDate->format('y'),
                $weekDate->format('n'),
                floor(($weekDate->format('d') - 1) / 7) + 1
            );

            return [
                'test_sheet_id' => $testSheet->id,
                'date' => $week_label,
                'name' => $weekDate->format('m월 d일') . ' ' . $testSheet->name,
                'class_name' => $report['classroom_name'],
                'hierarchy' => static::transformHierarchyData($report['hierarchical_analysis'] ?? []),
                'detailed_hierarchy' => static::transformHierarchyData($report['detailed_hierarchical_analysis'] ?? []),
                'personal_level' => $report['level_analysis']['personal'] ?? [],
                'classroom_level' => $report['level_analysis']['classroom'] ?? []
            ];
        })->filter(function ($report) {
            return $report && $report['hierarchy'] && $report['personal_level'] && $report['classroom_level'];
        })->values();
    }

    protected static function transformHierarchyData($hierarchicalData): array
    {
        $result = [];

        foreach ($hierarchicalData as $major => $majorData) {
            foreach ($majorData['sub_categories'] as $middle => $middleData) {
                foreach ($middleData['types'] as $type => $typeData) {
                    $levelData = [];
                    foreach ($typeData['levels'] as $level => $data) {
                        $levelData[$level] = [
                            'total' => $data['total'],
                            'correct' => $data['correct'],
                            'percentage' => $data['percentage'],
                            'dont_know_answers_count' => $data['dont_know_answers_count'] ?? 0
                        ];
                    }

                    $result[] = [
                        'major' => $majorData['name'],
                        'middle' => $middleData['name'],
                        'type' => $typeData['name'],
                        'levels' => $levelData
                    ];
                }
            }
        }

        return $result;
    }


    public static function getFormattedHighschoolAnalysisReport(
        Student $student,
        string $dateFrom,
        string $dateUntil,
        int $classroomId
    ): Collection {
        if (!$student || !$dateFrom || !$dateUntil || !$classroomId) {
            return collect();
        }

        $startDate = explode('/', $dateFrom)[0];
        $endDate = explode('/', $dateUntil)[1];
        $startCarbon = Carbon::parse($startDate);
        $endCarbon = Carbon::parse($endDate);

        // 조건에 맞는 테스트 시트 조회
        $testSheets = static::query()
            ->where('status', 'completed')
            ->originals()
            ->whereHas('answers', function ($query) use ($student) {
                $query->where('user_id', $student->user->id);
            })
            ->whereBetween('start_date', [$startCarbon, $endCarbon])
            ->orderBy('start_date', 'asc')
            ->get()
            ->filter(function ($testSheet) use ($student, $classroomId) {
                $classroom = $testSheet->getRepresentativeClassroom($student);
                return $classroom && $classroom->id == $classroomId;
            });

        // 테스트 시트별로 리포트 데이터 구성
        return $testSheets->map(function ($testSheet) use ($student) {
            $report = collect($testSheet->report)
                ->firstWhere('student_id', $student->id);

            if (!$report || !isset($report['score_analysis'])) return null;

            $weekDate = Carbon::parse($testSheet->start_date);
            $week_label = sprintf(
                '%d년 %d월 %d주차',
                $weekDate->format('y'),
                $weekDate->format('n'),
                floor(($weekDate->format('d') - 1) / 7) + 1
            );

            return [
                'date' => $week_label,
                'name' => $weekDate->format('m월 d일') . ' ' . $testSheet->name,
                'class_name' => $report['classroom_name'],
                'type' => in_array('숙제', $testSheet->tags ?? []) ? '숙제' : '일일테스트',
                'score_data' => static::transformHighschoolScoreData($report['score_analysis'])
            ];
        })->filter()->values();
    }

    protected static function transformHighschoolScoreData($scoreAnalysis): array
    {
        $result = [];
        foreach ($scoreAnalysis as $major => $majorData) {
            $middleGroups = [];

            foreach ($majorData['types'] as $type => $typeData) {
                $middleName = $typeData['middle'] ?? '기타';

                if (!isset($middleGroups[$middleName])) {
                    $middleGroups[$middleName] = [];
                }

                $scoreItems = [];
                foreach ($typeData['scores'] as $scoreData) {
                    $score = $scoreData['score'];
                    $levels = $scoreData['levels'];

                    for ($level = 1; $level <= 5; $level++) {
                        $scoreItems[$score][$level] = $levels[$level]['total_earned'] ?? 0;
                    }
                }

                $middleGroups[$middleName][] = [
                    'type' => $type,
                    'scores' => $scoreItems
                ];
            }

            // Convert middleGroups to the desired format
            $middles = [];
            foreach ($middleGroups as $middleName => $items) {
                $middles[] = [
                    'middle' => $middleName,
                    'middleItems' => $items
                ];
            }

            $result[] = [
                'major' => $major,
                'middles' => $middles
            ];
        }

        return $result;
    }

    protected static function _transformHighschoolScoreData($scoreAnalysis): array
    {
        $result = [];

        foreach ($scoreAnalysis as $major => $majorData) {
            $majorItems = [];

            foreach ($majorData['types'] as $type => $typeData) {
                if (isset($typeData['middle'])) {
                    dd($scoreAnalysis);
                }
                $scoreItems = [];

                // 모든 점수에 대해 (2,3,4점)
                foreach ($typeData['scores'] as $scoreData) {
                    $score = $scoreData['score'];
                    $levels = $scoreData['levels'];

                    // 각 레벨별 점수를 배열에 저장
                    for ($level = 1; $level <= 5; $level++) {
                        $scoreItems[$score][$level] = $levels[$level]['total_earned'] ?? 0;
                    }
                }

                $majorItems[] = [
                    'type' => $type,
                    'scores' => $scoreItems
                ];
            }

            $result[] = [
                'major' => $major,
                'items' => $majorItems
            ];
        }

        return $result;
    }

    /**
     * 클래스에 해당되는 시험지만 조회하는 스코프
     */
    public function scopeAvailableForClass(Builder $query, Classroom $classroom): Builder
    {
        $teacherId = $classroom->teacher->user->id;

        return $query->where(function ($query) use ($classroom, $teacherId) {
            // 출제자가 클래스의 강사인 경우만
            // $query->where('user_id', $teacherId);

            // target_group별 조건 체크
            $query->where(function ($q) use ($classroom) {
                $this->addTargetGroupConditionsForClass($q, $classroom);
            });
        });
    }
}
