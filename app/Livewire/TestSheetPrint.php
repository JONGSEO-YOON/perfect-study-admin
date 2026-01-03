<?php

namespace App\Livewire;

use App\Models\Student;
use App\Models\TestSheet;
use App\Models\WrongAnswerNote;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Attributes\Title;

class TestSheetPrint extends Component
{
  public $testSheet;
  public $initialLayoutMode = 'default';

  public function mount()
  {
    // 일반 시험지 조회인 경우
    if (request()->query('test_sheet_id')) {
      $this->testSheet = TestSheet::findOrFail(request()->query('test_sheet_id'));
      $studentId = request()->query('student_id');
      if ($studentId) {
        $student = Student::findOrFail($studentId);
        $printLayout = $this->testSheet->print_layout ?? [];
        $printLayout['title'] = "{$student->user->name} 학생 오답 문제 모음";
        $printLayout['subTitle'] = $this->testSheet->sub_title;
        $printLayout['grade'] = $student->gradeSystem->display_name;
        $this->testSheet->print_layout = $printLayout;
      }
      return;
    }

    // 오답 노트 출력인 경우
    $this->initialLayoutMode = '4Items';
    $studentId = request()->query('student_id');
    $from = request()->query('from');
    $to = request()->query('to');
    $is_dont_know_only = request()->query('is_dont_know_only', false);

    if ($studentId && $from && $to) {
      $student = Student::findOrFail($studentId);

      // 기간 내의 오답 문제들 조회

      // 임시 TestSheet 객체 생성
      $testSheet = new TestSheet();
      $testSheet->name = "{$student->user->name} 학생 오답 노트";
      $testSheet->title = "{$student->user->name} 학생 오답 문제 모음";
      $testSheet->subTitle = Carbon::parse($from)->format('Y-m-d') . ' ~ ' . Carbon::parse($to)->format('Y-m-d');

      // 문제 정보 구성
      $questions = WrongAnswerNote::getQuestions($studentId, $from, $to, $is_dont_know_only);

      $testSheet->questions = $questions;

      // 출력용 기본 설정
      $testSheet->template = 'default';
      $testSheet->split = 'default';
      $testSheet->print_layout = [
        'title' => $testSheet->title,
        'subTitle' => $testSheet->subTitle,
        'grade' => $student->gradeSystem->display_name,
      ];

      $this->testSheet = $testSheet;
    }
  }

  #[Title('문제 출력')]
  #[Layout('components.layouts.print')]
  public function render()
  {
    return view('livewire.test-sheet-print');
  }
}
