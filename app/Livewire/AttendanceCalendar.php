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
            ->whereBetween('created_at', [$fetchInfo['startStr'] . ' 00:00:00', $fetchInfo['endStr'] . ' 23:59:59'])
            ->orderBy('created_at', 'asc')
            ->get();

        $events = [];
        foreach ($attendanceLogs as $attendanceLog) {
            $title = $attendanceLog->type === 'in' ? '등원' : '하원';
            $color = $attendanceLog->type === 'in' ? '#8b5cf6' : '#a78bfa';
            
            // 정규/보충 구분
            if ($attendanceLog->classroom_id) {
                $title = '(정규)' . $title;
                $color = $attendanceLog->type === 'in' ? '#8b5cf6' : '#a78bfa';
            } else {
                $title = '(보충)' . $title;
                $color = $attendanceLog->type === 'in' ? '#06b6d4' : '#22d3ee';
            }
            
            // 지각 표시
            if ($attendanceLog->is_late) {
                $title .= ' (지각)';
                $color = '#f59e0b';
            }

            $isoString = $attendanceLog->created_at->toISOString();
            $events[] = CalendarEvent::make()
                ->title($title)
                ->start($isoString)
                ->end($isoString)
                ->backgroundColor($color);
        }

        return $events;
    }
}
