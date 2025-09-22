<?php

namespace App\Livewire;

use App\Models\WeeklyTestReport;
use App\Models\AttendanceLog;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Textarea;
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
    // dd($this->weeklyReports);
    $this->initializeComments();
  }

  public function updatedDateFrom()
  {
    // $this->validateDateRange();
    $this->weeklyReports = $this->getWeeklyReports();
  }

  public function updatedDateUntil()
  {
    // $this->validateDateRange();
    $this->weeklyReports = $this->getWeeklyReports();
  }

  private function validateDateRange()
  {
    if ($this->dateFrom && $this->dateUntil) {
      $dateFromPart = strpos($this->dateFrom, '/') !== false ?
        explode('/', $this->dateFrom)[0] : $this->dateFrom;
      $dateUntilPart = strpos($this->dateUntil, '/') !== false ?
        explode('/', $this->dateUntil)[1] : $this->dateUntil;

      $startDate = Carbon::parse($dateFromPart);
      $endDate = Carbon::parse($dateUntilPart);

      $diffInWeeks = $startDate->diffInWeeks($endDate);

      if ($diffInWeeks > 52) {
        Notification::make()
          ->title('날짜 범위 초과')
          ->body('검색 가능한 범위는 최대 52주(1년)입니다. 더 짧은 기간을 선택해주세요.')
          ->warning()
          ->send();
      }
    }
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

    // 날짜 범위를 주차 범위로 변환
    // dateFrom이 "2025-08-25/2025-08-31" 형식일 경우 첫 번째 날짜만 사용
    $dateFromPart = strpos($this->dateFrom, '/') !== false ?
      explode('/', $this->dateFrom)[0] : $this->dateFrom;
    $dateUntilPart = strpos($this->dateUntil, '/') !== false ?
      explode('/', $this->dateUntil)[1] : $this->dateUntil;

    $startDate = Carbon::parse($dateFromPart);
    $endDate = Carbon::parse($dateUntilPart);

    $weeklyReports = collect();

    // 주차별로 반복
    $current = $startDate->copy()->startOfWeek();

    while ($current->lte($endDate)) {
      $year = $current->year;
      $week = $current->isoWeek();

      // AttendanceLog에서 해당 주차 데이터 조회 (정규 및 보충 모두 포함)
      $attendanceData = AttendanceLog::getRegularAttendanceByWeek(
        $this->student->id,
        $year,
        $week
      );

      // 테스트 및 숙제 리포트 데이터 조회
      $testReport = WeeklyTestReport::where('student_id', $this->student->id)
        ->where('classroom_id', $this->classroomId)
        ->where('year', $year)
        ->where('week', $week)
        ->where('type', 'test')
        ->first();

      $homeworkReport = WeeklyTestReport::where('student_id', $this->student->id)
        ->where('classroom_id', $this->classroomId)
        ->where('year', $year)
        ->where('week', $week)
        ->where('type', 'homework')
        ->first();

      // 코멘트 데이터 조회
      $commentReport = $this->getWeeklyCommentReport($year, $week);
      $commentData = $commentReport->report ?? null;
      $commentContent = $commentData['comment'] ?? null;
      $commentStatus = $commentData['status'] ?? null;

      // 빈 주차라도 기본 구조 생성
      $weekStartDate = $current->format('Y-m-d');
      $weekEndDate = $current->copy()->endOfWeek()->format('Y-m-d');

      $weeklyReports->push([
        'year' => $year,
        'week' => $week,
        'week_range' => $weekStartDate . '/' . $weekEndDate,
        'week_label' => $year . '년 ' . $week . '주차 (' . $current->format('m/d') . ' ~ ' . $current->copy()->endOfWeek()->format('m/d') . ')',
        'test_report' => $this->formatReport($testReport),
        'attendance_logs' => $attendanceData, // 새로운 AttendanceLog 컬렉션
        'homework_report' => $this->formatReport($homeworkReport),
        'comment_report' => $commentContent, // 실제 코멘트 내용
        'comment_status' => $commentStatus // 실제 코멘트 상태
      ]);

      $current->addWeek();
    }

    return $weeklyReports;
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

  protected function formatReport($report): ?array
  {
    if (!$report || !$report->report) {
      return null;
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
    return Action::make('addAttendance')
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
            ->seconds(false)
            ->displayFormat('H:i'),
          TimePicker::make('check_out_time')
            ->label('하원 시간')
            ->seconds(false)
            ->displayFormat('H:i'),
          \Filament\Forms\Components\Toggle::make('is_late')
            ->label('지각 여부')
            ->default(false),
          \Filament\Forms\Components\Toggle::make('is_supplementary')
            ->label('보충 수업 여부')
            ->default(false),
          \Filament\Forms\Components\Toggle::make('is_absent')
            ->label('결석 여부')
            ->default(false)
            ->reactive()
            ->afterStateUpdated(function ($state, callable $set) {
              if ($state) {
                $set('is_late', false);
                $set('is_supplementary', false);
                $set('check_in_time', null);
                $set('check_out_time', null);
              }
            }),
          Textarea::make('memo')
            ->label('비고')
            ->columnSpanFull()
        ])
      ])
      ->action(function ($data) {
        $selectedDate = Carbon::parse($data['date']);

        // 해당 날짜에 이미 출결 기록이 있는지 확인
        $existingLog = AttendanceLog::where('student_id', $this->student->id)
          ->whereDate('created_at', $selectedDate->format('Y-m-d'))
          ->first();

        if ($existingLog) {
          Notification::make()
            ->title('출결 기록 중복')
            ->body('해당 날짜에 이미 출결 기록이 있습니다. 기존 기록을 수정하거나 삭제 후 다시 시도해주세요.')
            ->warning()
            ->send();
          return;
        }

        $attendanceData = [
          'student_id' => $this->student->id,
          'is_late' => $data['is_late'] ?? false,
          'is_absent' => $data['is_absent'] ?? false,
          'is_supplementary' => $data['is_supplementary'] ?? false,
          'memo' => $data['memo'],
          'check_in_time' => $data['check_in_time'] ? $selectedDate->copy()->setTimeFromTimeString($data['check_in_time']) : null,
          'check_out_time' => $data['check_out_time'] ? $selectedDate->copy()->setTimeFromTimeString($data['check_out_time']) : null,
        ];

        // 타임스탬프 자동 업데이트 비활성화 후 수동 설정
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
          ->title('출석이 추가되었습니다.')
          ->success()
          ->send();

        $this->weeklyReports = $this->getWeeklyReports();
      });
  }

  public function editAttendanceAction(): Action
  {
    return Action::make('editAttendance')
      ->modalHeading('출석 수정')
      ->modalWidth('md')
      ->modalSubmitActionLabel('수정')
      ->form([
        Grid::make(2)->schema([
          DatePicker::make('date')
            ->label('날짜')
            ->required()
            ->columnSpanFull(),
          TimePicker::make('check_in_time')
            ->label('등원 시간')
            ->seconds(false)
            ->displayFormat('H:i'),
          TimePicker::make('check_out_time')
            ->label('하원 시간')
            ->seconds(false)
            ->displayFormat('H:i'),
          \Filament\Forms\Components\Toggle::make('is_late')
            ->label('지각 여부')
            ->default(false),
          \Filament\Forms\Components\Toggle::make('is_supplementary')
            ->label('보충 수업 여부')
            ->default(false),
          \Filament\Forms\Components\Toggle::make('is_absent')
            ->label('결석 여부')
            ->default(false)
            ->reactive()
            ->afterStateUpdated(function ($state, callable $set) {
              if ($state) {
                $set('is_late', false);
                $set('is_supplementary', false);
                $set('check_in_time', null);
                $set('check_out_time', null);
              }
            }),
          Textarea::make('memo')
            ->label('비고')
            ->columnSpanFull()
        ])
      ])
      ->fillForm(function ($arguments) {
        $logId = $arguments['log_id'];
        $log = AttendanceLog::find($logId);

        if (!$log) {
          return [];
        }

        return [
          'date' => $log->created_at->format('Y-m-d'),
          'check_in_time' => $log->check_in_time ? $log->check_in_time->format('H:i') : null,
          'check_out_time' => $log->check_out_time ? $log->check_out_time->format('H:i') : null,
          'is_late' => $log->is_late,
          'is_absent' => $log->is_absent,
          'is_supplementary' => $log->is_supplementary,
          'memo' => $log->memo
        ];
      })
      ->action(function ($data, $arguments) {
        $logId = $arguments['log_id'];
        $log = AttendanceLog::find($logId);

        if ($log) {
          $selectedDate = Carbon::parse($data['date']);

          // 타임스탬프 자동 업데이트 비활성화
          $log->timestamps = false;
          $log->is_late = $data['is_late'];
          $log->is_absent = $data['is_absent'];
          $log->is_supplementary = $data['is_supplementary'];
          $log->memo = $data['memo'];
          $log->check_in_time = $data['check_in_time'] ? $selectedDate->copy()->setTimeFromTimeString($data['check_in_time']) : null;
          $log->check_out_time = $data['check_out_time'] ? $selectedDate->copy()->setTimeFromTimeString($data['check_out_time']) : null;

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
            ->title('출석이 수정되었습니다.')
            ->success()
            ->send();

          $this->weeklyReports = $this->getWeeklyReports();
        }
      });
  }

  public function deleteAttendanceAction(): Action
  {
    return Action::make('deleteAttendance')
      ->requiresConfirmation()
      ->modalHeading('출석 삭제')
      ->action(function ($arguments) {
        $this->arguments = $arguments;
        $log_id = $arguments['log_id'];

        // 특정 출석 기록만 삭제
        AttendanceLog::where('id', $log_id)
          ->where('student_id', $this->student->id)
          ->delete();

        Notification::make()
          ->title('출석이 삭제되었습니다.')
          ->success()
          ->send();

        $this->weeklyReports = $this->getWeeklyReports();
      });
  }

  protected function getAttendanceLogsForDate(string $date)
  {
    return AttendanceLog::where('student_id', $this->student->id)
      ->whereDate('created_at', $date)
      ->get();
  }


  public function saveComment($year, $week)
  {
    $key = "{$year}-{$week}";
    $comment = $this->comments[$key] ?? '';

    $report = $this->getWeeklyCommentReport($year, $week);

    if (empty($comment)) {
      $report->delete();
    } else {
      $report->report = [
        'comment' => $comment,
        'status' => 'draft'
      ];
      $report->save();
    }

    // 저장 후 weeklyReports 새로고침
    $this->weeklyReports = $this->getWeeklyReports();

    Notification::make()
      ->title('코멘트가 임시 저장되었습니다.')
      ->success()
      ->send();
  }

  public function sendCommentToParent($year, $week)
  {
    $key = "{$year}-{$week}";
    $comment = $this->comments[$key] ?? '';

    if (empty($comment)) {
      Notification::make()
        ->title('전달할 코멘트가 없습니다.')
        ->warning()
        ->send();
      return;
    }

    $report = $this->getWeeklyCommentReport($year, $week);
    $report->report = [
      'comment' => $comment,
      'status' => 'sent',
      'sent_at' => now()->format('Y-m-d H:i:s')
    ];
    $report->save();

    // 저장 후 weeklyReports 새로고침
    $this->weeklyReports = $this->getWeeklyReports();

    Notification::make()
      ->title('코멘트가 학부모에게 전달되었습니다.')
      ->success()
      ->send();
  }

  public function deleteComment($year, $week)
  {
    $report = $this->getWeeklyCommentReport($year, $week);
    $report->delete();

    // 로컬 상태도 초기화
    $key = "{$year}-{$week}";
    $this->comments[$key] = '';

    // 저장 후 weeklyReports 새로고침
    $this->weeklyReports = $this->getWeeklyReports();

    Notification::make()
      ->title('코멘트가 삭제되었습니다.')
      ->success()
      ->send();
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
