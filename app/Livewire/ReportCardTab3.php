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
  public $weeklyReports = [];
  public $arguments = [];
  public $comments = [];  // 주차별 코멘트를 저장할 배열

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
    if (!$this->student || !$this->classroomId || !$this->dateFrom || !$this->dateUntil) {
      return collect();
    }

    // 날짜 문자열을 Carbon 인스턴스로 변환
    $startDate = explode('/', $this->dateFrom)[0];
    $endDate = explode('/', $this->dateUntil)[1];
    $startCarbon = Carbon::parse($startDate);
    $endCarbon = Carbon::parse($endDate);

    // 시작 주와 끝 주 계산
    $startYear = $startCarbon->year;
    $startWeek = $startCarbon->isoWeek();
    $endYear = $endCarbon->year;
    $endWeek = $endCarbon->isoWeek();

    // 모든 보고서 조회
    $reports = WeeklyTestReport::where('student_id', $this->student->id)
      ->where('classroom_id', $this->classroomId)
      ->where(function ($query) use ($startYear, $startWeek, $endYear, $endWeek) {
        if ($startYear === $endYear) {
          $query->where('year', $startYear)
            ->whereBetween('week', [$startWeek, $endWeek]);
        } else {
          $query->where(function ($q) use ($startYear, $startWeek, $endYear, $endWeek) {
            $q->where(function ($q1) use ($startYear, $startWeek) {
              $q1->where('year', $startYear)
                ->where('week', '>=', $startWeek);
            })->orWhere(function ($q2) use ($endYear, $endWeek) {
              $q2->where('year', $endYear)
                ->where('week', '<=', $endWeek);
            });
          });
        }
      })
      ->get()
      ->groupBy(function ($report) {
        return $report->year . '-' . $report->week;
      });

    // 모든 주차 생성
    $allWeeks = collect();
    $currentDate = $startCarbon->copy();

    while ($currentDate <= $endCarbon) {
      $year = $currentDate->year;
      $week = $currentDate->isoWeek();
      $key = $year . '-' . $week;

      if (!$allWeeks->has($key)) {
        $weekDate = Carbon::now()->setISODate($year, $week, 1);
        $weekReports = $reports->get($key, collect());

        $allWeeks[$key] = [
          'year' => $year,
          'week' => $week,
          'week_label' => sprintf(
            '%d년 %d월 %d주차',
            $weekDate->format('y'),
            $weekDate->format('n'),
            floor(($weekDate->format('d') - 1) / 7) + 1
          ),
          'week_range' => $this->getWeekRange($year, $week),
          'test_report' => $this->formatReport($weekReports->firstWhere('type', 'test')),
          'homework_report' => $this->formatReport($weekReports->firstWhere('type', 'homework')),
          'attendance_report' => $this->formatReport($weekReports->firstWhere('type', 'attendance')),
          'comment_report' => $weekReports->firstWhere('type', 'comment')?->report['comment'] ?? ''
        ];
      }

      $currentDate->addWeek();
    }

    return $allWeeks->sortBy(['year', 'week'])->values();
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


  protected function initializeComments()
  {
    foreach ($this->weeklyReports as $report) {
      $key = "{$report['year']}-{$report['week']}";
      $commentReport = $this->getWeeklyCommentReport($report['year'], $report['week']);
      $this->comments[$key] = $commentReport->report['comment'] ?? '';
    }
  }


  protected function formatReport($report)
  {
    if (!$report) {
      return null;
    }

    if ($report->type === 'attendance') {
      return collect($report->report)
        ->sortBy('date')
        ->values()
        ->all();
    }

    return collect($report->report)
      ->sortBy('test_sheet_id')
      ->map(function ($test) {
        return [
          'date' => $test['date'],
          'test_sheet_id' => $test['test_sheet_id'] ?? 0,
          'name' => $test[isset($test['test_name']) ? 'test_name' : 'homework_name'],
          'scopes' => $test['scopes'],
          'total' => $test['total'],
          'by_types' => collect($test['by_types'])->sortBy('name')->values()->all()
        ];
      })
      ->values()
      ->all();
  }

  protected function getWeekRange($year, $week)
  {
    $date = Carbon::now();
    $date->setISODate($year, $week);
    $startOfWeek = $date->startOfWeek()->format('Y-m-d');
    $endOfWeek = $date->endOfWeek()->format('Y-m-d');
    return "$startOfWeek ~ $endOfWeek";
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
            ->required(),
          Select::make('attendance')
            ->label('출석')
            ->columnStart(1)
            ->default('정규등원 (출석)')
            ->options([
              '정규등원 (출석)' => '정규등원 (출석)',
              '정규등원 (지각)' => '정규등원 (지각)',
              '정규등원 (결석)' => '정규등원 (결석)',
              '보충등원 (출석)' => '보충등원 (출석)',
              '보충등원 (지각)' => '보충등원 (지각)',
              '보충등원 (결석)' => '보충등원 (결석)',
            ])
            ->required(),
          Textarea::make('memo1')
            ->label('지각, 결석 사유')
            ->columnSpanFull(),
          Textarea::make('memo2')
            ->label('비고')
            ->columnSpanFull()
        ])
      ])
      ->action(function ($data) {

        $report = $this->getWeeklyAttendanceReport($data['date']);
        $reportData = $report->report ?? [];

        $attendanceData = [
          'date' => $data['date'],
          'attendance' => $data['attendance'],
          'memo1' => $data['memo1'],
          'memo2' => $data['memo2']
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
