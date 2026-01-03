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
  public $showDetail = false;
  public $startNumber;
  public $questionNumber;

  public function mount($testsheet, $questionNo, $startNumber)
  {
    $this->testsheet = $testsheet;
    $this->questionNo = $questionNo;
    $this->startNumber = $startNumber;
    $this->questionNumber = $questionNo + $startNumber - 1;
    $this->question = $this->testsheet->questions[$questionNo - 1];
    $this->showDetail = false;

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

  public function closeDetail()
  {
    $this->showDetail = false;
  }

  public function openDetail()
  {
    $this->showDetail = true;
  }
}
