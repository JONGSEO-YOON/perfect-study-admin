<?php

namespace App\Livewire;

use App\Models\TestSheet;
use App\Models\TestSheetAnswer;
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
}
