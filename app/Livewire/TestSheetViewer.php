<?php

namespace App\Livewire;

use App\Models\TestSheet;
use App\Models\TestSheetAnswer;
use Livewire\Component;

class TestSheetViewer extends Component
{

  public $id = null;
  public $testsheet = null;
  public $currentQuestionIndex = 0;
  public $questions = [];
  public $answers = [];
  public $progress = [];
  public $currentQuestion = null;
  public $currentAnswer = '';  // 현재 입력 중인 답안

  public function render()
  {
    return view('livewire.test-sheet-viewer');
  }

  public function mount($id)
  {
    $this->id = $id;
    $this->testsheet = TestSheet::find($id);
    $alreadyTaken = TestSheetAnswer::where('test_sheet_id', $id)
      ->where('user_id', auth()->id())
      ->latest()
      ->exists();
    if ($alreadyTaken) {
      return redirect("/test-sheet-result/{$id}");
    }

    $this->questions = $this->testsheet->questions;
    $this->answers = array_fill(0, count($this->questions), null);
    $this->currentQuestion = $this->questions[$this->currentQuestionIndex];
    $this->updateProgress();
  }

  public function nextQuestion()
  {
    if ($this->currentQuestionIndex < count($this->questions) - 1) {
      $this->currentQuestionIndex++;
      $this->currentQuestion = $this->questions[$this->currentQuestionIndex];
      $this->updateProgress();
    }
  }


  protected function updateProgress()
  {
    $total = count($this->questions);
    $answered = count(array_filter($this->answers, fn($answer) => $answer !== null));

    $this->progress = [
      'current' => $this->currentQuestionIndex + 1,
      'total' => $total,
      'percentage' => (($this->currentQuestionIndex + 1) / $total) * 100
    ];
  }

  public function appendNumber($number)
  {
    $this->currentAnswer = $this->currentAnswer . $number;
  }

  public function toggleSign()
  {
    if (strlen($this->currentAnswer) === 0) {
      $this->currentAnswer = '-';
    } elseif ($this->currentAnswer[0] === '-') {
      $this->currentAnswer = substr($this->currentAnswer, 1);
    } else {
      $this->currentAnswer = '-' . $this->currentAnswer;
    }
  }

  public function clear()
  {
    $this->currentAnswer = '';
  }

  public function submitAnswer()
  {
    if (strlen($this->currentAnswer) > 0) {
      $this->answers[$this->currentQuestionIndex] = intval($this->currentAnswer);
      $this->currentAnswer = '';
      $this->nextQuestion();
    }
  }

  public function goToQuestion($index)
  {
    $this->currentQuestionIndex = $index;
    $this->currentQuestion = $this->questions[$index];
    $this->currentAnswer = '';
    $this->updateProgress();
  }

  public function completeTest()
  {
    // Calculate correct answers
    $correctCount = 0;
    foreach ($this->answers as $index => $answer) {
      if ($answer === $this->questions[$index]['answer']) {
        $correctCount++;
      }
    }

    // Create test sheet answer record
    TestSheetAnswer::create([
      'test_sheet_id' => $this->testsheet->id,
      'user_id' => auth()->id(),
      'answers' => $this->answers,
      'correct_count' => $correctCount
    ]);

    // Redirect to results page
    return redirect("/test-sheet-result/{$this->testsheet->id}");
  }
}
