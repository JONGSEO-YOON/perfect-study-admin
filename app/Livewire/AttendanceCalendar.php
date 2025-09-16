<?php

namespace App\Livewire;

use Guava\Calendar\Widgets\CalendarWidget;
use Guava\Calendar\ValueObjects\CalendarEvent;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use App\Models\AttendanceLog;

#[Layout('layouts.public')]
class AttendanceCalendar extends CalendarWidget
{
    public $studentData;

    public $studentId;

    protected static string $view = 'livewire.attendance-calendar';
    protected ?string $locale = 'ko';
    protected bool $useFilamentTimezone = true;

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
            ->where(function($query) use ($fetchInfo) {
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
                if ($attendanceLog->classroom_id) {
                    $title = '(정규)' . $title;
                    $color = '#8b5cf6';
                } else {
                    $title = '(보충)' . $title;
                    $color = '#06b6d4';
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
                if ($attendanceLog->classroom_id) {
                    $title = '(정규)' . $title;
                    $color = '#a78bfa';
                } else {
                    $title = '(보충)' . $title;
                    $color = '#22d3ee';
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
}
