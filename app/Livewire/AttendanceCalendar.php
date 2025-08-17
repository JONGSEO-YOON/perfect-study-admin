<?php

namespace App\Livewire;

use Guava\Calendar\Widgets\CalendarWidget;
use Guava\Calendar\ValueObjects\CalendarEvent;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use App\Models\AttendanceLog;
use App\Models\WeeklyTestReport;

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
                    ->title('(보충)등원')
                    ->start($isoString)
                    ->end($isoString)
                    ->backgroundColor('#8b5cf6');
            }
            if ($attendanceLog->check_out_time) {
                $isoString = $attendanceLog->attendance_date . 'T' . $attendanceLog->check_out_time . 'Z';
                $events[] = CalendarEvent::make()
                    ->title('(보충)하원')
                    ->start($isoString)
                    ->end($isoString)
                    ->backgroundColor('#a78bfa');
            }
        }

        $weeklyTestReports = WeeklyTestReport::where('student_id', $this->studentId)
            ->where('type', 'attendance')
            ->whereBetween('created_at', [$fetchInfo['startStr'], $fetchInfo['endStr']])
            ->get();

        foreach ($weeklyTestReports as $weeklyTestReport) {
            $reportData = $weeklyTestReport->report;
            foreach ($reportData as $report) {
                if (isset($report['check_in_time']) && $report['check_in_time'] !== null) {
                    $isoString = $report['date'] . 'T' . $report['check_in_time'] . 'Z';
                    $events[] = CalendarEvent::make()
                        ->title('(정규)등원')
                        ->start($isoString)
                        ->end($isoString)
                        ->backgroundColor('#06b6d4');
                }
                if (isset($report['check_out_time']) && $report['check_out_time'] !== null) {
                    $isoString = $report['date'] . 'T' . $report['check_out_time'] . 'Z';
                    $events[] = CalendarEvent::make()
                        ->title('(정규)하원')
                        ->start($isoString)
                        ->end($isoString)
                        ->backgroundColor('#22d3ee');
                }
            }
        }



        return $events;
    }
}
