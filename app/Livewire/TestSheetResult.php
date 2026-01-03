<?php

namespace App\Livewire;

use App\Models\TestSheet;
use App\Models\TestSheetAnswer;
use App\Models\WrongAnswerTestSheet;
use Livewire\Component;

class TestSheetResult extends Component
{
  public $testsheet;
  public $testSheetAnswer;
  public $correctCount;
  public $totalQuestions;
  public $percentage;
  public $answers = [];

  public $selectedQuestionNo = null;
  public $startNumber = 1;


  public function mount($id)
  {
    $this->testsheet = TestSheet::findOrFail($id);
    $this->testSheetAnswer = TestSheetAnswer::where('test_sheet_id', $id)
      ->where('user_id', auth()->id())
      ->latest()
      ->firstOrFail();

    $this->answers = $this->testSheetAnswer->answers;
    $this->correctCount = $this->testSheetAnswer->correct_count;
    $this->totalQuestions = $this->testsheet->total_score;
    $this->percentage = round(($this->correctCount / $this->totalQuestions) * 100);
    $this->startNumber = $this->testsheet->print_layout['startingNumber'] ?? 1;
  }

  public function getAnswerStatus($questionIndex)
  {
    $userAnswer = $this->answers[$questionIndex] ?? null;
    $correctAnswer = $this->testsheet->questions[$questionIndex]['answer'];

    return [
      'isCorrect' => $userAnswer === $correctAnswer,
      'userAnswer' => $userAnswer,
      'correctAnswer' => $correctAnswer
    ];
  }

  public function returnToMain()
  {
    return redirect()->route('main');
  }

  public function render()
  {
    return view('livewire.test-sheet-result', [
      'answerStatuses' => collect($this->testsheet->questions)
        ->map(fn($q, $i) => $this->getAnswerStatus($i))
    ]);
  }

  public function selectQuestion($questionNo)
  {
    $this->selectedQuestionNo = $questionNo;
  }

  public function shouldShowRetestButton()
  {
    // 원본 시험지인지 확인
    if (!$this->testsheet->isOriginal()) {
      return false;
    }

    // 1차 오답 테스트 조회
    $firstRetryTest = WrongAnswerTestSheet::where('original_test_sheet_id', $this->testsheet->id)
      ->where('user_id', auth()->id())
      ->where('retry_count', 1)
      ->whereHas('testSheet', function ($query) {
        $query->hasQuestions();
      })
      ->first();

    if (!$firstRetryTest) {
      return false;
    }

    // 1차 오답 테스트의 답안 조회하여 완료 여부 확인
    $firstRetryAnswer = TestSheetAnswer::where('test_sheet_id', $firstRetryTest->test_sheet_id)
      ->where('user_id', auth()->id())
      ->where('status', 'completed')
      ->exists();

    // 1차 오답 테스트가 있고 아직 완료되지 않은 경우에만 true 반환
    return !$firstRetryAnswer;
  }


  public function retest()
  {
    $firstRetryTest = WrongAnswerTestSheet::where('original_test_sheet_id', $this->testsheet->id)
      ->where('user_id', auth()->id())
      ->where('retry_count', 1)
      ->firstOrFail();

    return redirect('/test-sheet/' .  $firstRetryTest->test_sheet_id);
  }
}
