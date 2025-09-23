<?php

namespace App\Livewire;

use Guava\Calendar\Widgets\CalendarWidget;
use Guava\Calendar\ValueObjects\CalendarEvent;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use App\Models\AttendanceLog;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use App\Services\FcmService;

#[Layout('layouts.public')]
class AttendanceCalendar extends CalendarWidget
{
    public $studentData;

    public $studentId;

    public $selectedDate;

    protected static string $view = 'livewire.attendance-calendar';
    protected ?string $locale = 'ko';
    protected bool $useFilamentTimezone = true;
    protected bool $dateClickEnabled = true;

    public function mount()
    {
        $this->studentData = json_decode(base64_decode($this->studentData), true);
        $this->studentId = $this->studentData['student_id'];
    }

    public function getHeading(): string
    {
        return  $this->studentData['name'] . ' 학생 - 등하원 기록';
    }

    public function getEvents(array $fetchInfo = []): Collection | array
    {
        $attendanceLogs = AttendanceLog::where('student_id', $this->studentId)
            ->where(function ($query) use ($fetchInfo) {
                $query->whereBetween('check_in_time', [$fetchInfo['startStr'] . ' 00:00:00', $fetchInfo['endStr'] . ' 23:59:59'])
                    ->orWhereBetween('check_out_time', [$fetchInfo['startStr'] . ' 00:00:00', $fetchInfo['endStr'] . ' 23:59:59']);
            })
            ->orderBy('created_at', 'asc')
            ->get();

        $events = [];
        foreach ($attendanceLogs as $attendanceLog) {
            // 등원/하원 시간에 따라 이벤트 생성
            if ($attendanceLog->check_in_time) {
                $title = '등원';
                $color = '#8b5cf6';

                // 정규/보충 구분
                if ($attendanceLog->is_supplementary) {
                    $title = '(보충)' . $title;
                    $color = '#06b6d4';
                } else {
                    $title = '(정규)' . $title;
                    $color = '#8b5cf6';
                }

                // 지각 표시
                if ($attendanceLog->is_late) {
                    $title .= ' (지각)';
                    $color = '#f59e0b';
                }

                $localTime = $attendanceLog->check_in_time->addHours(9);
                $events[] = CalendarEvent::make()
                    ->title($title)
                    ->start($localTime)
                    ->end($localTime)
                    ->backgroundColor($color);
            }

            if ($attendanceLog->check_out_time) {
                $title = '하원';
                $color = '#a78bfa';

                // 정규/보충 구분
                if ($attendanceLog->is_supplementary) {
                    $title = '(보충)' . $title;
                    $color = '#22d3ee';
                } else {
                    $title = '(정규)' . $title;
                    $color = '#a78bfa';
                }

                $localTime = $attendanceLog->check_out_time->addHours(9);
                $events[] = CalendarEvent::make()
                    ->title($title)
                    ->start($localTime)
                    ->end($localTime)
                    ->backgroundColor($color);
            }
        }

        return $events;
    }

    public function onDateClick(array $info = []): void
    {
        // dateStr이 로컬 타임존 기준으로 정확한 날짜를 제공합니다
        $clickedDate = $info['dateStr'] ?? $info['date'] ?? $info['start'] ?? null;

        // 날짜 형식을 Y-m-d로 정규화
        if ($clickedDate) {
            try {
                $this->selectedDate = Carbon::parse($clickedDate)->format('Y-m-d');
            } catch (\Exception $e) {
                $this->selectedDate = $clickedDate;
            }
        } else {
            $this->selectedDate = now()->format('Y-m-d');
        }

        // 직접 액션 호출
        $this->createAttendanceForDate($this->selectedDate);
    }

    public function createAttendanceForDate($date)
    {
        $this->selectedDate = $date;
        $this->mountAction('createAttendance');
    }

    public function getDateClickContextMenuActions(): array
    {
        return [];  // 컨텍스트 메뉴 비활성화하고 직접 onDateClick 사용
    }

    protected function createAttendanceAction(): Action
    {
        return Action::make('createAttendance')
            ->label('출석 기록 생성')
            ->icon('heroicon-o-plus')
            ->modalHeading(function () {
                $dateText = $this->selectedDate ? Carbon::parse($this->selectedDate)->format('Y년 m월 d일') : '오늘';
                return $this->studentData['name'] . ' - ' . $dateText . ' 출석 기록';
            })
            ->modalWidth('md')
            ->modalAutofocus(false)
            ->fillForm(function () {
                if (!$this->selectedDate) {
                    return [];
                }

                $selectedDate = Carbon::parse($this->selectedDate)->format('Y-m-d');
                $log = AttendanceLog::where('student_id', $this->studentId)
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
            ->form([
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
            ->action(function ($data) {
                // 클릭한 날짜를 정확히 파싱 (타임존 고려)
                $dateToUse = $this->selectedDate ?: now()->format('Y-m-d');
                Log::info('Action selectedDate: ' . $this->selectedDate);
                Log::info('Action dateToUse: ' . $dateToUse);

                $selectedDate = Carbon::parse($dateToUse)
                    ->setTimezone(config('app.timezone'))
                    ->startOfDay();

                // 해당 날짜에 이미 기록이 있는지 확인
                $existingLog = AttendanceLog::where('student_id', $this->studentId)
                    ->whereDate('created_at', $selectedDate->format('Y-m-d'))
                    ->first();

                // 클릭한 날짜에 시간을 추가하여 정확한 datetime 생성
                $checkInTime = null;
                $checkOutTime = null;

                if ($data['check_in_time_only']) {
                    $checkInTime = Carbon::createFromFormat(
                        'Y-m-d H:i',
                        $selectedDate->format('Y-m-d') . ' ' . $data['check_in_time_only']
                    );
                }

                if ($data['check_out_time_only']) {
                    $checkOutTime = Carbon::createFromFormat(
                        'Y-m-d H:i',
                        $selectedDate->format('Y-m-d') . ' ' . $data['check_out_time_only']
                    );
                }

                $attendanceData = [
                    'student_id' => $this->studentId,
                    'is_late' => $data['is_late'] ?? false,
                    'is_absent' => $data['is_absent'] ?? false,
                    'is_supplementary' => $data['is_supplementary'] ?? false,
                    'memo' => $data['memo'],
                    'check_in_time' => $checkInTime,
                    'check_out_time' => $checkOutTime,
                ];

                if ($existingLog) {
                    // 기존 기록 수정
                    $existingLog->timestamps = false;
                    $existingLog->is_late = $attendanceData['is_late'];
                    $existingLog->is_absent = $attendanceData['is_absent'];
                    $existingLog->is_supplementary = $attendanceData['is_supplementary'];
                    $existingLog->memo = $attendanceData['memo'];
                    $existingLog->check_in_time = $attendanceData['check_in_time'];
                    $existingLog->check_out_time = $attendanceData['check_out_time'];

                    // created_at을 등원시간 또는 하원시간으로 설정, 둘 다 없으면 선택한 날짜로 설정
                    if ($existingLog->check_in_time) {
                        $existingLog->created_at = $existingLog->check_in_time;
                    } elseif ($existingLog->check_out_time) {
                        $existingLog->created_at = $existingLog->check_out_time;
                    } else {
                        $existingLog->created_at = $selectedDate;
                    }

                    $existingLog->updated_at = now();
                    $existingLog->save();

                    Notification::make()
                        ->title('출석 기록이 수정되었습니다.')
                        ->success()
                        ->send();

                    // 알림 발송 처리
                    if ($data['send_notification'] ?? false) {
                        $this->sendAttendanceNotification($this->studentId, $attendanceData);
                    }
                } else {
                    // 새 기록 생성
                    $attendanceLog = new AttendanceLog($attendanceData);
                    $attendanceLog->timestamps = false;

                    // created_at을 등원시간 또는 하원시간으로 설정, 둘 다 없으면 클릭한 날짜로 설정
                    if ($checkInTime) {
                        $attendanceLog->created_at = $checkInTime;
                    } elseif ($checkOutTime) {
                        $attendanceLog->created_at = $checkOutTime;
                    } else {
                        $attendanceLog->created_at = $selectedDate;
                    }

                    $attendanceLog->updated_at = now();
                    $attendanceLog->save();

                    Notification::make()
                        ->title('출석 기록이 생성되었습니다.')
                        ->success()
                        ->send();

                    // 알림 발송 처리
                    if ($data['send_notification'] ?? false) {
                        $this->sendAttendanceNotification($this->studentId, $attendanceData);
                    }
                }
                $this->js('location.reload()');
            });
    }

    /**
     * 출석 알림 발송
     */
    private function sendAttendanceNotification($studentId, $attendanceData)
    {
        try {
            $student = \App\Models\Student::with('user')->find($studentId);
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
