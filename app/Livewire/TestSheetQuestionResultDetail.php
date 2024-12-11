<?php

namespace App\Livewire;

use App\Models\TestSheetAnswer;
use Livewire\Component;

class TestSheetQuestionResultDetail extends Component
{
  public $testsheet;
  public $questionNo;
  public $question;
  public $userAnswer;
  public $isCorrect;

  public function mount($testsheet, $questionNo)
  {
    $this->testsheet = $testsheet;
    $this->questionNo = $questionNo;
    $this->question = $this->testsheet->questions[$questionNo - 1];

    // 사용자의 최신 답안 조회
    $testSheetAnswer = TestSheetAnswer::where('test_sheet_id', $this->testsheet->id)
      ->where('user_id', auth()->id())
      ->latest()
      ->firstOrFail();

    $this->userAnswer = $testSheetAnswer->answers[$questionNo - 1] ?? null;
    $this->isCorrect = $this->userAnswer === $this->question['answer'];
  }

  public function render()
  {
    return view('livewire.test-sheet-question-result-detail');
  }
}
