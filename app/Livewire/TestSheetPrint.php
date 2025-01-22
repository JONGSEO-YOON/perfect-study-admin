<?php

namespace App\Livewire;

use App\Models\Student;
use App\Models\TestSheet;
use App\Models\WrongAnswerNote;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

class TestSheetPrint extends Component
{
  public $testSheet;
  public $initialLayoutMode = 'default';

  public function mount()
  {
    // 일반 시험지 조회인 경우
    if (request()->query('test_sheet_id')) {
      $this->testSheet = TestSheet::findOrFail(request()->query('test_sheet_id'));
      return;
    }

    // 오답 노트 출력인 경우
    $this->initialLayoutMode = '4Items';
    $studentId = request()->query('student_id');
    $from = request()->query('from');
    $to = request()->query('to');

    if ($studentId && $from && $to) {
      $student = Student::findOrFail($studentId);

      // 기간 내의 오답 문제들 조회

      // 임시 TestSheet 객체 생성
      $testSheet = new TestSheet();
      $testSheet->name = "{$student->user->name} 학생 오답 노트";
      $testSheet->title = "{$student->user->name} 학생 오답 문제 모음";
      $testSheet->subTitle = Carbon::parse($from)->format('Y-m-d') . ' ~ ' . Carbon::parse($to)->format('Y-m-d');

      // 문제 정보 구성
      $questions = WrongAnswerNote::getQuestions($studentId, $from, $to);

      $testSheet->questions = $questions;

      // 출력용 기본 설정
      $testSheet->template = 'default';
      $testSheet->split = 'default';
      $testSheet->print_layout = [
        'title' => $testSheet->title,
        'subTitle' => $testSheet->subTitle,
      ];

      $this->testSheet = $testSheet;
    }
  }

  #[Layout('components.layouts.print')]
  public function render()
  {
    return view('livewire.test-sheet-print');
  }
}
