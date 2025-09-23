<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Student;
use App\Models\AttendanceLog;
use App\Models\Classroom;
use Carbon\Carbon;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use App\Services\FcmService;
use Illuminate\Support\Facades\Log;

class MonthlyAttendance extends Page
{
    protected static ?string $navigationIcon = null;

    protected static ?string $navigationGroup = '교실 관리';

    protected static ?string $navigationLabel = '월별출결 현황';

    protected static ?string $title = '월별출결 현황';

    protected static ?int $navigationSort = 8;

    protected static string $view = 'filament.pages.monthly-attendance';

    public $selectedYear;
    public $selectedMonth;
    public $selectedClassroom;

    public $showModal = false;
    public $modalStudentId;
    public $modalDate;
    public $modalStudentName;

    public function mount()
    {
        $this->selectedYear = now()->year;
        $this->selectedMonth = now()->month;
        $this->selectedClassroom = null;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('selectedClassroom')
                    ->label('반 선택')
                    ->placeholder('전체 반')
                    ->options(function () {
                        $query = Classroom::query()->orderBy('name');

                        if (!auth()->user()->isRoleAbove('manager', true)) {
                            $query->where('teacher_id', auth()->user()->userable->id);
                        }

                        return $query->pluck('name', 'id');
                    })
                    ->reactive()
                    ->afterStateUpdated(function () {
                        // 선택된 반이 변경되면 페이지를 새로고침
                    }),
            ]);
    }

    public function previousMonth()
    {
        $date = Carbon::create($this->selectedYear, $this->selectedMonth, 1)->subMonth();
        $this->selectedYear = $date->year;
        $this->selectedMonth = $date->month;
    }

    public function nextMonth()
    {
        $date = Carbon::create($this->selectedYear, $this->selectedMonth, 1)->addMonth();
        $this->selectedYear = $date->year;
        $this->selectedMonth = $date->month;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('editAttendance')
                ->label('출결 등록')
                ->modalHeading(fn() => $this->modalStudentName . ' - ' . $this->modalDate . ' 출결 기록')
                ->extraModalFooterActions(function () {
                    $actions = [];

                    // 기존 기록이 있는 경우에만 삭제 버튼 추가
                    if ($this->hasExistingRecord()) {
                        $actions[] = Action::make('delete')
                            ->label('삭제')
                            ->color('danger')
                            ->requiresConfirmation()
                            ->modalHeading('출결 기록 삭제')
                            ->modalDescription('정말로 이 출결 기록을 삭제하시겠습니까?')
                            ->action(function () {
                                $this->deleteAttendanceRecord();
                                $this->js('location.reload()');
                            });
                    }

                    return $actions;
                })
                ->form([
                    Select::make('student_id')
                        ->label('학생 선택')
                        ->options(function () {
                            $students = $this->getStudents();
                            return $students->pluck('user.name', 'id');
                        })
                        ->searchable()
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $set) {
                            if ($state) {
                                $student = $this->getStudents()->find($state);
                                $this->modalStudentName = $student?->user?->name;
                                $this->modalStudentId = $state;
                            }
                        })
                        ->visible(fn() => !$this->showModal),
                    Select::make('day')
                        ->label('날짜 선택')
                        ->options(function () {
                            $options = [];
                            for ($day = 1; $day <= $this->getDaysInMonth(); $day++) {
                                $date = \Carbon\Carbon::create($this->selectedYear, $this->selectedMonth, $day);
                                $dayNames = ['일', '월', '화', '수', '목', '금', '토'];
                                $dayOfWeek = $dayNames[$date->dayOfWeek];
                                $options[$day] = $day . '일 (' . $dayOfWeek . ')';
                            }
                            return $options;
                        })
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $set) {
                            if ($state) {
                                $this->modalDate = $state;
                            }
                        })
                        ->visible(fn() => !$this->showModal),
                    TimePicker::make('check_in_time_only')
                        ->label('등원 시간')
                        ->seconds(false)
                        ->displayFormat('H:i')
                        ->required(fn($get) => !$get('is_absent')),
                    TimePicker::make('check_out_time_only')
                        ->label('하원 시간')
                        ->seconds(false)
                        ->displayFormat('H:i'),
                    Toggle::make('is_late')
                        ->label('지각 여부')
                        ->default(false)
                        ->visible(fn($get) => !$get('is_absent')),
                    Toggle::make('is_supplementary')
                        ->label('보충 수업 여부')
                        ->default(false)
                        ->visible(fn($get) => !$get('is_absent')),
                    Toggle::make('is_absent')
                        ->label('결석 여부')
                        ->default(false)
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $set) {
                            if ($state) {
                                $set('is_late', false);
                                $set('is_supplementary', false);
                            }
                        }),
                    Toggle::make('send_notification')
                        ->label('알림 발송')
                        ->default(false),
                    Textarea::make('memo')
                        ->label('메모')
                        ->rows(3),
                ])
                ->fillForm(function () {
                    if (!$this->modalStudentId || !$this->modalDate) {
                        return [];
                    }

                    $selectedDate = Carbon::create($this->selectedYear, $this->selectedMonth, $this->modalDate)->format('Y-m-d');
                    $log = AttendanceLog::where('student_id', $this->modalStudentId)
                        ->whereDate('created_at', $selectedDate)
                        ->first();

                    if ($log) {
                        return [
                            'is_late' => $log->is_late ?? false,
                            'is_absent' => $log->is_absent ?? false,
                            'is_supplementary' => $log->is_supplementary ?? false,
                            'memo' => $log->memo,
                            'check_in_time_only' => $log->check_in_time ? $log->check_in_time->format('H:i') : null,
                            'check_out_time_only' => $log->check_out_time ? $log->check_out_time->format('H:i') : null,
                        ];
                    }

                    return [];
                })
                ->action(function ($data) {
                    $selectedDate = Carbon::create($this->selectedYear, $this->selectedMonth, $this->modalDate);

                    $attendanceData = [
                        'student_id' => $this->modalStudentId,
                        'is_late' => $data['is_late'] ?? false,
                        'is_absent' => $data['is_absent'] ?? false,
                        'is_supplementary' => $data['is_supplementary'] ?? false,
                        'memo' => $data['memo'],
                        'check_in_time' => $data['check_in_time_only'] ? $selectedDate->copy()->setTimeFromTimeString($data['check_in_time_only']) : null,
                        'check_out_time' => $data['check_out_time_only'] ? $selectedDate->copy()->setTimeFromTimeString($data['check_out_time_only']) : null,
                    ];

                    $log = AttendanceLog::where('student_id', $this->modalStudentId)
                        ->whereDate('created_at', $selectedDate->format('Y-m-d'))
                        ->first();

                    if ($log) {
                        // 기존 기록 수정
                        $log->timestamps = false;
                        $log->is_late = $attendanceData['is_late'];
                        $log->is_absent = $attendanceData['is_absent'];
                        $log->is_supplementary = $attendanceData['is_supplementary'];
                        $log->memo = $attendanceData['memo'];
                        $log->check_in_time = $attendanceData['check_in_time'];
                        $log->check_out_time = $attendanceData['check_out_time'];

                        // created_at을 등원시간 또는 하원시간으로 설정, 둘 다 없으면 선택한 날짜로 설정
                        if ($log->check_in_time) {
                            $log->created_at = $log->check_in_time;
                        } elseif ($log->check_out_time) {
                            $log->created_at = $log->check_out_time;
                        } else {
                            $log->created_at = $selectedDate;
                        }

                        $log->updated_at = now();
                        $log->save();

                        Notification::make()
                            ->title('출결 기록이 수정되었습니다.')
                            ->success()
                            ->send();

                        // 알림 발송 처리
                        if ($data['send_notification'] ?? false) {
                            $this->sendAttendanceNotification($this->modalStudentId, $attendanceData);
                        }
                    } else {
                        // 새 기록 생성
                        $attendanceLog = new AttendanceLog($attendanceData);
                        $attendanceLog->timestamps = false;

                        // created_at을 등원시간 또는 하원시간으로 설정, 둘 다 없으면 선택한 날짜로 설정
                        if ($attendanceLog->check_in_time) {
                            $attendanceLog->created_at = $attendanceLog->check_in_time;
                        } elseif ($attendanceLog->check_out_time) {
                            $attendanceLog->created_at = $attendanceLog->check_out_time;
                        } else {
                            $attendanceLog->created_at = $selectedDate;
                        }

                        $attendanceLog->updated_at = now();
                        $attendanceLog->save();

                        Notification::make()
                            ->title('출결 기록이 생성되었습니다.')
                            ->success()
                            ->send();

                        // 알림 발송 처리
                        if ($data['send_notification'] ?? false) {
                            $this->sendAttendanceNotification($this->modalStudentId, $attendanceData);
                        }
                    }
                })
                ->visible(true),
        ];
    }

    public function openAttendanceModal($studentId, $day, $studentName)
    {
        $this->modalStudentId = $studentId;
        $this->modalDate = $day;
        $this->modalStudentName = $studentName;
        $this->showModal = true;

        $this->mountAction('editAttendance');
    }

    public function getViewData(): array
    {
        $students = $this->getStudents();
        $attendanceData = $this->getAttendanceData($students);
        $daysInMonth = $this->getDaysInMonth();

        return [
            'students' => $students,
            'attendanceData' => $attendanceData,
            'daysInMonth' => $daysInMonth,
            'selectedYear' => $this->selectedYear,
            'selectedMonth' => $this->selectedMonth,
        ];
    }

    private function getStudents()
    {
        $query = Student::with('user');

        // 반 선택에 따른 필터링
        if ($this->selectedClassroom) {
            $query->whereHas('classrooms', function ($q) {
                $q->where('classrooms.id', $this->selectedClassroom);
            });
        } else {
            // 반을 선택하지 않은 경우 권한에 따른 필터링
            if (!auth()->user()->isRoleAbove('manager', true)) {
                $query->whereHas('classrooms', function ($q) {
                    $q->where('classrooms.teacher_id', auth()->user()->userable->id);
                });
            }
        }

        return $query->orderBy('id')->get();
    }

    private function getAttendanceData($students)
    {
        $startDate = Carbon::create($this->selectedYear, $this->selectedMonth, 1)->startOfDay();
        $endDate = $startDate->copy()->endOfMonth()->endOfDay();

        $attendanceLogs = AttendanceLog::whereIn('student_id', $students->pluck('id'))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get()
            ->groupBy('student_id');

        $attendanceData = [];
        foreach ($students as $student) {
            $studentLogs = $attendanceLogs->get($student->id, collect());
            $attendanceData[$student->id] = $studentLogs->groupBy(function ($log) {
                return $log->created_at->day;
            });
        }

        return $attendanceData;
    }

    private function getDaysInMonth()
    {
        return Carbon::create($this->selectedYear, $this->selectedMonth, 1)->daysInMonth;
    }

    public function hasExistingRecord(): bool
    {
        if (!$this->modalStudentId || !$this->modalDate) {
            return false;
        }

        $selectedDate = Carbon::create($this->selectedYear, $this->selectedMonth, $this->modalDate)->format('Y-m-d');
        return AttendanceLog::where('student_id', $this->modalStudentId)
            ->whereDate('created_at', $selectedDate)
            ->exists();
    }

    public function deleteAttendanceRecord(): void
    {
        if (!$this->modalStudentId || !$this->modalDate) {
            return;
        }

        $selectedDate = Carbon::create($this->selectedYear, $this->selectedMonth, $this->modalDate)->format('Y-m-d');
        $log = AttendanceLog::where('student_id', $this->modalStudentId)
            ->whereDate('created_at', $selectedDate)
            ->first();

        if ($log) {
            $log->delete();

            Notification::make()
                ->title('출결 기록이 삭제되었습니다.')
                ->success()
                ->send();

            // 모달 상태 초기화
            $this->resetModal();
        }
    }

    private function resetModal(): void
    {
        $this->modalStudentId = null;
        $this->modalDate = null;
        $this->modalStudentName = null;
        $this->showModal = false;
    }

    public static function getNavigationBadge(): ?string
    {
        return null;
    }

    /**
     * 출석 알림 발송
     */
    private function sendAttendanceNotification($studentId, $attendanceData)
    {
        try {
            $student = Student::with('user')->find($studentId);
            if (!$student) {
                return;
            }

            $fcmService = new FcmService();
            $currentTime = now()->format('H:i');

            // 등원/하원 구분
            $keyWord = '';
            if ($attendanceData['check_in_time']) {
                $keyWord = '등원';
            } elseif ($attendanceData['check_out_time']) {
                $keyWord = '하원';
            } else {
                $keyWord = '출석';
            }

            // 정규/보충 구분
            $attendanceType = ($attendanceData['is_supplementary'] ?? false) ? '보충' : '정규';

            // 부모 전화번호 찾기
            $parentPhones = [];
            if ($student->phone_father !== '010--') {
                $parentPhones[] = $student->phone_father;
            }
            if ($student->phone_mother !== '010--') {
                $parentPhones[] = $student->phone_mother;
            }

            $uniqueParentPhones = array_unique($parentPhones);

            if (empty($uniqueParentPhones)) {
                Log::info("학생 {$student->user->name}의 부모 연락처가 없습니다.");
                return;
            }

            // 알림 내용 구성
            $title = "📍 {$student->user->name} 학생 {$keyWord} 알림";
            $body = "{$student->user->name} 학생이 {$currentTime}에 {$keyWord}하였습니다. ({$attendanceType})";

            foreach ($uniqueParentPhones as $parentPhone) {
                $fcmService->sendToParent(
                    $parentPhone,
                    $title,
                    $body,
                    [
                        'type' => 'attendance',
                        'title' => $title,
                        'body' => $body,
                    ]
                );
            }
        } catch (\Exception $e) {
            Log::error("출석 알림 전송 중 오류: " . $e->getMessage(), [
                'student_id' => $studentId,
                'error' => $e->getMessage()
            ]);
        }
    }
}
