<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StudentHistoryResource\Pages;
use App\Models\Student;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
use App\Models\Classroom;

class StudentHistoryResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static ?string $navigationGroup = '교실 관리';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = '학생 이력관리';

    protected static ?string $slug = 'student-histories';

    protected static ?string $pluralModelLabel = '학생 이력관리';

    public static function getBreadcrumb(): string
    {
        return '';
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(function ($query) {
                if (!auth()->user()->isRoleAbove('manager', true)) {
                    $query->whereHas('classrooms', function ($q) {
                        $q->where('classrooms.teacher_id', auth()->user()->userable->id)
                            ->orWhere('classrooms.sub_teacher_id', auth()->user()->userable->id);
                    });
                }
                return $query;
            })
            ->columns([
                TextColumn::make('id')
                    ->label('No')
                    ->rowIndex(),
                TextColumn::make('user.name')
                    ->label('이름')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('school.name')
                    ->label('학교')
                    ->searchable(),
                TextColumn::make('gradeSystem.display_name')
                    ->label('학년'),
                TextColumn::make('status')
                    ->label('현재 상태')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'active', 'enrolled' => 'success',
                        'pending' => 'warning',
                        'withdrawn' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'active', 'enrolled' => '재원',
                        'pending' => '승인예정',
                        'withdrawn' => '퇴원',
                        default => $state,
                    }),
                TextColumn::make('created_at')
                    ->date('Y-m-d')
                    ->label('등록일')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('상태')
                    ->options([
                        'active' => '재원',
                        'enrolled' => '재원(기존)',
                        'pending' => '승인예정',
                        'withdrawn' => '퇴원',
                    ]),
                SelectFilter::make('classroom_id')
                    ->label('반')
                    ->options(function () {
                        return Classroom::query()
                            ->orderBy('name')
                            ->pluck('name', 'id');
                    })
                    ->query(function (Builder $query, $data) {
                        $classroomId = $data["value"] ?? null;
                        $query->when($classroomId, function ($query, $classroomId) {
                            $query->whereHas('classrooms', function ($q) use ($classroomId) {
                                $q->where('classrooms.id', $classroomId);
                            });
                        });
                    }),
            ], layout: FiltersLayout::AboveContent)
            ->actions([
                Tables\Actions\Action::make('view-history')
                    ->label('이력 보기')
                    ->icon('heroicon-m-clock')
                    ->modalHeading(fn($record) => $record->user->name . ' 학생 이력')
                    ->modalSubmitAction(false)
                    ->modalWidth('3xl')
                    ->modalContent(function ($record) {
                        $timeline = self::buildTimeline($record);
                        return view('filament.components.modals.student-history', [
                            'timeline' => $timeline,
                            'student' => $record,
                        ]);
                    }),
            ])
            ->hiddenFilterIndicators(true)
            ->emptyStateHeading('학생이 없습니다.');
    }

    public static function buildTimeline(Student $student): array
    {
        $events = [];

        // 상태 이력 로드 (새로운 event_type 기반)
        $statusHistories = $student->statusHistories()
            ->with('changedBy')
            ->orderBy('created_at')
            ->get();

        // "created" 이력이 존재하면 그걸 사용, 없으면 student.created_at로 폴백
        $createdHistory = $statusHistories->firstWhere('event_type', 'created');
        if (!$createdHistory) {
            $events[] = [
                'date' => $student->created_at,
                'type' => 'register',
                'color' => 'blue',
                'title' => '학원 등록',
                'description' => '',
                'details' => [],
            ];
        }

        // 반 배정 이력 (pivot 기반 - classroom_assigned event_type이 없는 레거시용)
        $classrooms = $student->allClassroomsIncludingRemoved()
            ->withPivot('created_at', 'deleted_at')
            ->with(['teacher.user', 'subTeacher.user'])
            ->get();

        // 이미 classroom_assigned/removed/transferred 이벤트 기록이 있는지 확인 (pivot과 중복 방지)
        $hasClassroomEvents = $statusHistories->whereIn('event_type', ['classroom_assigned', 'classroom_removed', 'classroom_transferred'])->isNotEmpty();

        if (!$hasClassroomEvents) {
            // 레거시: pivot에서 반 배정/해제 이력 재구성
            foreach ($classrooms as $classroom) {
                $teacherInfo = $classroom->teacher?->user?->name ?? '-';
                $subTeacherInfo = $classroom->subTeacher?->user?->name;

                if ($classroom->pivot->created_at) {
                    $events[] = [
                        'date' => \Carbon\Carbon::parse($classroom->pivot->created_at),
                        'type' => 'classroom',
                        'color' => 'green',
                        'title' => '반 배정',
                        'description' => $classroom->name,
                        'details' => [
                            '담임' => $teacherInfo,
                            '부담임' => $subTeacherInfo ?: null,
                        ],
                    ];
                }
                if ($classroom->pivot->deleted_at) {
                    $events[] = [
                        'date' => \Carbon\Carbon::parse($classroom->pivot->deleted_at),
                        'type' => 'classroom_removed',
                        'color' => 'orange',
                        'title' => '반 해제',
                        'description' => $classroom->name,
                        'details' => [
                            '담임' => $teacherInfo,
                        ],
                    ];
                }
            }
        }

        // 상태 이력 기반 이벤트
        foreach ($statusHistories as $history) {
            $eventType = $history->event_type ?: self::guessLegacyEventType($history);
            $changedBy = $history->changedBy?->name;

            switch ($eventType) {
                case 'created':
                    $events[] = [
                        'date' => $history->created_at,
                        'type' => 'register',
                        'color' => 'blue',
                        'title' => '학원 등록',
                        'description' => $history->reason ?: '',
                        'details' => array_filter([
                            '정보' => $history->memo,
                            '처리자' => $changedBy,
                        ]),
                    ];
                    break;

                case 'updated':
                    $events[] = [
                        'date' => $history->created_at,
                        'type' => 'updated',
                        'color' => 'yellow',
                        'title' => '정보 수정',
                        'description' => $history->reason ?: '정보 수정',
                        'details' => array_filter([
                            '변경 내용' => $history->memo,
                            '처리자' => $changedBy,
                        ]),
                    ];
                    break;

                case 'approved':
                    $events[] = [
                        'date' => $history->created_at,
                        'type' => 'approved',
                        'color' => 'emerald',
                        'title' => '계정 승인',
                        'description' => $history->reason ?: '',
                        'details' => array_filter([
                            '정보' => $history->memo,
                            '처리자' => $changedBy,
                        ]),
                    ];
                    break;

                case 'approval_revoked':
                    $events[] = [
                        'date' => $history->created_at,
                        'type' => 'approval_revoked',
                        'color' => 'orange',
                        'title' => '승인 취소',
                        'description' => $history->reason ?: '',
                        'details' => array_filter([
                            '정보' => $history->memo,
                            '처리자' => $changedBy,
                        ]),
                    ];
                    break;

                case 'classroom_assigned':
                    $events[] = [
                        'date' => $history->created_at,
                        'type' => 'classroom',
                        'color' => 'green',
                        'title' => '반 배정',
                        'description' => $history->memo ?: '',
                        'details' => array_filter([
                            '처리자' => $changedBy,
                        ]),
                    ];
                    break;

                case 'classroom_removed':
                    $events[] = [
                        'date' => $history->created_at,
                        'type' => 'classroom_removed',
                        'color' => 'orange',
                        'title' => '반 해제',
                        'description' => $history->memo ?: '',
                        'details' => array_filter([
                            '처리자' => $changedBy,
                        ]),
                    ];
                    break;

                case 'classroom_transferred':
                    $events[] = [
                        'date' => $history->created_at,
                        'type' => 'classroom_transferred',
                        'color' => 'amber',
                        'title' => '전반',
                        'description' => $history->memo ?: '',
                        'details' => array_filter([
                            '사유' => $history->reason !== '전반' ? $history->reason : null,
                            '처리자' => $changedBy,
                        ]),
                    ];
                    break;

                case 'withdrawn':
                case 'status_change':
                    if ($history->to_status === 'withdrawn') {
                        $events[] = self::buildWithdrawEvent($history, $classrooms, $changedBy);
                    } elseif (in_array($history->to_status, ['active', 'enrolled']) && $history->from_status === 'withdrawn') {
                        $events[] = self::buildReRegisterEvent($history, $classrooms, $changedBy);
                    }
                    break;

                case 're_register':
                    $events[] = self::buildReRegisterEvent($history, $classrooms, $changedBy);
                    break;

                case 'deleted':
                    $events[] = [
                        'date' => $history->created_at,
                        'type' => 'deleted',
                        'color' => 'gray',
                        'title' => '학생 삭제',
                        'description' => $history->reason ?: '삭제',
                        'details' => array_filter([
                            '삭제 시점 정보' => $history->memo,
                            '처리자' => $changedBy,
                        ]),
                    ];
                    break;
            }
        }

        // 4. 현재 퇴원 상태인데 statusHistories에 기록이 없는 경우 (이전 데이터)
        if ($student->status === 'withdrawn' && $statusHistories->where('to_status', 'withdrawn')->isEmpty()) {
            $withdrawnDate = $student->withdrawn_at ? \Carbon\Carbon::parse($student->withdrawn_at) : $student->updated_at;
            $classroomDetails = self::resolveClassroomsAtTimeDetailed($classrooms, $withdrawnDate);

            $events[] = [
                'date' => $withdrawnDate,
                'type' => 'withdrawn',
                'color' => 'red',
                'title' => '퇴원',
                'description' => $student->withdrawal_reason ?: '사유 없음',
                'details' => array_filter([
                    '퇴원한 반' => self::formatClassroomNames($classroomDetails),
                    '퇴원한 담임' => self::formatTeacherNames($classroomDetails),
                    '상세' => $student->withdrawal_reason_detail ?: null,
                ]),
            ];
        }

        // 날짜순 정렬 (최신이 위로)
        usort($events, fn($a, $b) => $b['date']->timestamp - $a['date']->timestamp);

        return $events;
    }

    /**
     * 퇴원 이벤트 생성 (반/담임 분리 표시)
     *
     * 우선순위:
     * 1. pivot에서 해당 시점의 소속 반 조회
     * 2. memo에 저장된 "소속 반: ..." 정보 파싱
     */
    private static function buildWithdrawEvent($history, $classrooms, ?string $changedBy): array
    {
        // 1순위: pivot에서 해당 시점 소속 반 조회
        $classroomDetails = self::resolveClassroomsAtTimeDetailed($classrooms, $history->created_at);

        // 2순위: memo 파싱 (pivot에 이력이 없는 경우)
        if (empty($classroomDetails) && $history->memo) {
            $classroomDetails = self::parseClassroomMemo($history->memo);
        }

        return [
            'date' => $history->created_at,
            'type' => 'withdrawn',
            'color' => 'red',
            'title' => '퇴원',
            'description' => $history->reason ?: '사유 없음',
            'details' => array_filter([
                '퇴원한 반' => self::formatClassroomNames($classroomDetails),
                '퇴원한 담임' => self::formatTeacherNames($classroomDetails),
                '처리자' => $changedBy,
            ]),
        ];
    }

    /**
     * 재등록 이벤트 생성
     */
    private static function buildReRegisterEvent($history, $classrooms, ?string $changedBy): array
    {
        $classroomDetails = self::resolveClassroomsAfterTimeDetailed($classrooms, $history->created_at);

        return [
            'date' => $history->created_at,
            'type' => 're_register',
            'color' => 'indigo',
            'title' => '재등록',
            'description' => $history->reason ?: '',
            'details' => array_filter([
                '배정 반' => self::formatClassroomNames($classroomDetails),
                '담임' => self::formatTeacherNames($classroomDetails),
                '처리자' => $changedBy,
            ]),
        ];
    }

    /**
     * [['name' => ..., 'teacher' => ...], ...] → "반1, 반2"
     */
    private static function formatClassroomNames(array $details): ?string
    {
        if (empty($details)) return null;
        $names = array_filter(array_column($details, 'name'));
        return !empty($names) ? implode(', ', $names) : null;
    }

    /**
     * [['name' => ..., 'teacher' => ...], ...] → "선생1, 선생2" (중복 제거)
     */
    private static function formatTeacherNames(array $details): ?string
    {
        if (empty($details)) return null;
        $teachers = array_filter(array_unique(array_column($details, 'teacher')));
        return !empty($teachers) ? implode(', ', $teachers) : null;
    }

    /**
     * 레거시 이력(event_type이 비어있는 기존 데이터) 유형 추측
     */
    private static function guessLegacyEventType($history): string
    {
        if ($history->to_status === 'withdrawn') {
            return 'withdrawn';
        }
        if (in_array($history->to_status, ['active', 'enrolled']) && $history->from_status === 'withdrawn') {
            return 're_register';
        }
        return 'status_change';
    }

    /**
     * 특정 시점에 소속되어 있던 반+담임 정보 문자열 반환
     */
    private static function resolveClassroomsAtTime($classrooms, $time): ?string
    {
        $details = self::resolveClassroomsAtTimeDetailed($classrooms, $time);
        if (empty($details)) return null;

        return collect($details)
            ->map(fn($c) => "{$c['name']} (담임: {$c['teacher']})")
            ->join(', ');
    }

    /**
     * 특정 시점에 소속되어 있던 반+담임 정보를 구조화된 배열로 반환
     * [['name' => '서현이반', 'teacher' => '장용현'], ...]
     *
     * 1순위: 해당 시점에 실제로 배정 상태였던 반
     * 2순위 (fallback): 해당 시점 이전 마지막으로 배정되었던 반 (해당 시점 전후 해제)
     */
    private static function resolveClassroomsAtTimeDetailed($classrooms, $time): array
    {
        $exactMatches = [];
        $fallbackMatches = [];

        foreach ($classrooms as $classroom) {
            $assignedAt = $classroom->pivot->created_at ? \Carbon\Carbon::parse($classroom->pivot->created_at) : null;
            $removedAt = $classroom->pivot->deleted_at ? \Carbon\Carbon::parse($classroom->pivot->deleted_at) : null;

            if (!$assignedAt) continue;

            $teacher = $classroom->teacher?->user?->name ?? '-';
            $entry = ['name' => $classroom->name, 'teacher' => $teacher];

            // 정확 매치: 배정 <= 시점 <= 해제 (또는 현재까지 활성)
            if ($assignedAt->lte($time) && (!$removedAt || $removedAt->gte($time))) {
                $exactMatches[] = $entry;
                continue;
            }

            // Fallback: 퇴원 시점 직전(24시간 이내)에 해제된 반
            if ($removedAt && $assignedAt->lte($time) && $removedAt->lte($time)
                && $removedAt->diffInHours($time) <= 24) {
                $fallbackMatches[] = $entry;
            }
        }

        return !empty($exactMatches) ? $exactMatches : $fallbackMatches;
    }

    /**
     * 특정 시점 이후 배정된 반+담임 정보 문자열 반환 (재등록 후 배정 확인용)
     */
    private static function resolveClassroomsAfterTime($classrooms, $time): ?string
    {
        $details = self::resolveClassroomsAfterTimeDetailed($classrooms, $time);
        if (empty($details)) return null;

        return collect($details)
            ->map(fn($c) => "{$c['name']} (담임: {$c['teacher']})")
            ->join(', ');
    }

    /**
     * 특정 시점 이후 배정된 반+담임 정보를 구조화된 배열로 반환
     */
    private static function resolveClassroomsAfterTimeDetailed($classrooms, $time): array
    {
        $result = [];
        foreach ($classrooms as $classroom) {
            $assignedAt = $classroom->pivot->created_at ? \Carbon\Carbon::parse($classroom->pivot->created_at) : null;

            if (!$assignedAt) continue;

            $isAfter = $assignedAt->gte($time);
            $isCurrentlyActive = !$classroom->pivot->deleted_at;

            if ($isAfter || ($isCurrentlyActive && $assignedAt->lte($time))) {
                $teacher = $classroom->teacher?->user?->name ?? '-';
                $result[] = ['name' => $classroom->name, 'teacher' => $teacher];
            }
        }

        return $result;
    }

    /**
     * memo에서 "소속 반: 반1 (담임: 선생1), 반2 (담임: 선생2)" 형식 파싱
     * 반환: [['name' => '반1', 'teacher' => '선생1'], ...]
     */
    private static function parseClassroomMemo(?string $memo): array
    {
        if (!$memo) return [];

        // "소속 반: " prefix 제거
        $content = preg_replace('/^소속\s*반\s*:\s*/u', '', $memo);

        // "반1 (담임: 선생1), 반2 (담임: 선생2)" → 쉼표로 분리 (괄호 안 쉼표 방지)
        $items = preg_split('/,\s*(?![^()]*\))/u', $content);

        $result = [];
        foreach ($items as $item) {
            $item = trim($item);
            if (preg_match('/^(.+?)\s*\(\s*담임\s*:\s*(.+?)\s*\)$/u', $item, $m)) {
                $result[] = ['name' => trim($m[1]), 'teacher' => trim($m[2])];
            } elseif (!empty($item)) {
                // 담임 정보 없는 레거시 포맷
                $result[] = ['name' => $item, 'teacher' => null];
            }
        }

        return $result;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStudentHistories::route('/'),
        ];
    }
}
