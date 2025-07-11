<?php

namespace App\Livewire;

use Guava\Calendar\Widgets\CalendarWidget;
use Guava\Calendar\ValueObjects\CalendarEvent;
use Illuminate\Support\Collection;
use Livewire\Attributes\Url;
use Livewire\Attributes\Layout;
use App\Models\AttendanceLog;
use App\Models\Student;

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
            ->whereBetween('attendance_date', [$fetchInfo['startStr'], $fetchInfo['endStr']])
            ->orderBy('attendance_date', 'asc')
            ->get();

        $events = [];
        foreach ($attendanceLogs as $attendanceLog) {
            if ($attendanceLog->check_in_time) {
                $isoString = $attendanceLog->attendance_date . 'T' . $attendanceLog->check_in_time . 'Z';
                $events[] = CalendarEvent::make()
                    ->title('등원')
                    ->start($isoString)
                    ->end($isoString)
                    ->backgroundColor('#1b76a3');
            }
            if ($attendanceLog->check_out_time) {
                $isoString = $attendanceLog->attendance_date . 'T' . $attendanceLog->check_out_time . 'Z';
                $events[] = CalendarEvent::make()
                    ->title('하원')
                    ->start($isoString)
                    ->end($isoString)
                    ->backgroundColor('#8b5cf6');
            }
        }
        return $events;
    }
}
