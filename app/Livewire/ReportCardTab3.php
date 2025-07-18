<?php

namespace App\Livewire;

use App\Models\WeeklyTestReport;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Livewire\Attributes\On;
use Livewire\Component;

class ReportCardTab3 extends Component implements HasActions, HasForms
{
  use InteractsWithActions, InteractsWithForms;

  public $student = null;
  public $classroomId = null;
  public $dateFrom = null;
  public $dateUntil = null;
  public $readonly = false;
  public $weeklyReports = [];
  public $arguments = [];
  public $comments = [];

  public $hide = [
    'header' => false,
    'weekRow' => false,
    'attendance' => false,
    'comment' => false,
  ];

  public function mount()
  {
    $this->weeklyReports = $this->getWeeklyReports();
    $this->initializeComments();
  }

  public function render()
  {
    return view('livewire.report-card-tab3');
  }

  protected function getWeeklyReports()
  {
    if (!$this->classroomId) {
      return collect();
    }
    return WeeklyTestReport::getFormattedWeeklyReport(
      $this->student,
      $this->dateFrom,
      $this->dateUntil,
      $this->classroomId
    );
  }

  protected function initializeComments()
  {
    foreach ($this->weeklyReports as $report) {
      $key = "{$report['year']}-{$report['week']}";
      $commentReport = $this->getWeeklyCommentReport($report['year'], $report['week']);
      $this->comments[$key] = $commentReport->report['comment'] ?? '';
    }
  }

  protected function getWeeklyCommentReport(int $year, int $week): WeeklyTestReport
  {
    return WeeklyTestReport::firstOrNew([
      'student_id' => $this->student->id,
      'classroom_id' => $this->classroomId,
      'year' => $year,
      'week' => $week,
      'type' => 'comment'
    ]);
  }

  #[On('reportFormChange')]
  public function onReportFormChange($data)
  {
    $this->dateFrom = $data['date_from'];
    $this->dateUntil = $data['date_until'];
    $this->classroomId = $data['classroom_id'];
    $this->weeklyReports = $this->getWeeklyReports();
    $this->initializeComments();
  }

  public function addAttendance(): Action
  {
    return Action::make('delete')
      ->modalHeading('출석 추가')
      ->modalWidth('md')
      ->modalSubmitActionLabel('추가')
      ->form([
        Grid::make(2)->schema([
          DatePicker::make('date')
            ->label('날짜')
            ->default(Carbon::now()->format('Y-m-d'))
            ->required()
            ->columnSpanFull(),
          TimePicker::make('check_in_time')
            ->label('등원 시간')
            ->default(Carbon::now()->format('H:i:s'))
            ->required(),
          TimePicker::make('check_out_time')
            ->label('하원 시간')
            ->required(),
          // Select::make('attendance')
          //   ->label('출석')
          //   ->columnStart(1)
          //   ->default('정규등원 (출석)')
          //   ->options([
          //     '정규등원 (출석)' => '정규등원 (출석)',
          //     '정규등원 (지각)' => '정규등원 (지각)',
          //     '정규등원 (결석)' => '정규등원 (결석)',
          //     '보충등원 (출석)' => '보충등원 (출석)',
          //     '보충등원 (지각)' => '보충등원 (지각)',
          //     '보충등원 (결석)' => '보충등원 (결석)',
          //   ])
          //   ->required(),
          // Textarea::make('memo1')
          //   ->label('지각, 결석 사유')
          //   ->columnSpanFull(),
          Textarea::make('memo1')
            ->label('비고')
            ->columnSpanFull()
        ])
      ])
      ->action(function ($data) {

        $report = $this->getWeeklyAttendanceReport($data['date']);
        $reportData = $report->report ?? [];

        $attendanceData = [
          'date' => $data['date'],
          'attendance' => '정규',
          'check_in_time' => $data['check_in_time'],
          'check_out_time' => $data['check_out_time'],
          'memo1' => $data['memo1'],
          'memo2' => null
        ];

        $exists = false;
        foreach ($reportData as $key => $attendance) {
          if ($attendance['date'] === $data['date']) {
            $reportData[$key] = $attendanceData;
            $exists = true;
            break;
          }
        }

        if (!$exists) {
          $reportData[] = $attendanceData;
        }

        $this->saveAttendanceReport($report, $reportData);

        Notification::make()
          ->title('출석이 추가되었습니다.')
          ->success()
          ->send();

        $this->weeklyReports = $this->getWeeklyReports();
      });
  }

  public function deleteAttendanceAction(): Action
  {
    return Action::make('deleteAttendance')
      ->requiresConfirmation()
      ->modalHeading('출석 삭제')
      ->action(function ($arguments) {
        $this->arguments = $arguments;
        $date = $arguments['date'];
        $report = $this->getWeeklyAttendanceReport($date);

        $reportData = array_filter(
          $report->report ?? [],
          fn($attendance) => $attendance['date'] !== $date
        );

        $this->saveAttendanceReport($report, $reportData);

        Notification::make()
          ->title('출석이 삭제되었습니다.')
          ->success()
          ->send();

        $this->weeklyReports = $this->getWeeklyReports();
      });
  }

  protected function getWeeklyAttendanceReport(string $date): WeeklyTestReport
  {
    $carbon = Carbon::parse($date);
    return WeeklyTestReport::firstOrNew([
      'student_id' => $this->student->id,
      'classroom_id' => $this->classroomId,
      'year' => $carbon->year,
      'week' => $carbon->isoWeek(),
      'type' => 'attendance'
    ]);
  }

  protected function saveAttendanceReport(WeeklyTestReport $report, array $reportData): void
  {
    if (empty($reportData)) {
      $report->delete();
      return;
    }

    usort($reportData, fn($a, $b) => strcmp($a['date'], $b['date']));
    $report->report = array_values($reportData);
    $report->save();
  }

  public function updateComment($year, $week, $comment)
  {
    $report = $this->getWeeklyCommentReport($year, $week);

    if (empty($comment)) {
      $report->delete();
      return;
    }

    $report->report = ['comment' => $comment];
    $report->save();
  }
}
