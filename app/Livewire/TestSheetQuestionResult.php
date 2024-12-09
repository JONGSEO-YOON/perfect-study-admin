<?php

namespace App\Livewire;

use App\Models\TestSheet;
use App\Models\TestSheetAnswer;
use Livewire\Component;

class TestSheetQuestionResult extends Component
{
  public $testsheet;
  public $questionNo;
  public $question;
  public $testSheetAnswer;
  public $userAnswer;
  public $isCorrect;

  public function mount($id, $questionNo)
  {
    $this->testsheet = TestSheet::findOrFail($id);
    $this->questionNo = $questionNo;
    $this->question = $this->testsheet->questions[$questionNo - 1];

    $this->testSheetAnswer = TestSheetAnswer::where('test_sheet_id', $id)
      ->where('user_id', auth()->id())
      ->latest()
      ->firstOrFail();

    $this->userAnswer = $this->testSheetAnswer->answers[$questionNo - 1];
    $this->isCorrect = $this->userAnswer === $this->question['answer'];
  }

  public function backToList()
  {
    return redirect()->route('test-sheet.result', ['id' => $this->testsheet->id]);
  }

  public function render()
  {
    return view('livewire.test-sheet-question-result');
  }
}
