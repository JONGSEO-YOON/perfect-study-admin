<?php

namespace App\Livewire;

use App\Models\QuestionCategory;
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

  public $elapsedTime = 0;
  protected $lastSaveTime = 0;

  public function render()
  {
    return view('livewire.test-sheet-viewer');
  }

  public function mount($id)
  {
    $this->id = $id;
    $this->testsheet = TestSheet::find($id);

    $latestAnswer = TestSheetAnswer::where('test_sheet_id', $id)
      ->where('user_id', auth()->id())
      ->latest()
      ->first();

    if ($latestAnswer) {
      if ($latestAnswer->status === 'completed') {
        return redirect("/test-sheet-result/{$id}");
      }
      $this->answers = $latestAnswer->answers;
      $this->elapsedTime = $latestAnswer->time ?? 0;
    } else {
      $this->answers = array_fill(0, count($this->testsheet->questions), null);
    }

    $this->questions = $this->testsheet->questions;
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
    $this->currentAnswer = $this->answers[$this->currentQuestionIndex] ?? '';
  }

  public function appendNumber($number)
  {
    if ($this->currentQuestion['answer_type'] === 'multiple_choice') {
      $this->currentAnswer = $number;
    } else {

      $this->currentAnswer = $this->currentAnswer . $number;
    }
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

      // Save current progress
      TestSheetAnswer::updateOrCreate(
        [
          'test_sheet_id' => $this->testsheet->id,
          'user_id' => auth()->id(),
          'status' => 'pending'
        ],
        [
          'answers' => $this->answers,
          'correct_count' => 0 // 진행 중에는 채점하지 않음
        ]
      );

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

  public function pauseTest()
  {
    return redirect('/');
  }


  public function completeTest()
  {
    // 답안이 하나도 없는지 체크
    $hasAnyAnswer = false;
    foreach ($this->answers as $answer) {
      if ($answer !== null) {
        $hasAnyAnswer = true;
        break;
      }
    }
    if (!$hasAnyAnswer) {
      // 하나도 답을 작성하지 않은 경우
      // $this->dispatch('alert', [
      //   'type' => 'error',
      //   'message' => '최소 한 문제 이상 답안을 작성해주세요.'
      // ]);
      // return;
    }

    // Calculate correct answers
    $correctCount = 0;
    $correctCountReport = [];

    foreach ($this->answers as $index => $answer) {
      $questionType = $this->questions[$index]['question_type_id'];

      // Initialize report entry if not exists
      if (!isset($correctCountReport[$questionType])) {
        $name = QuestionCategory::find($questionType)->name;
        $correctCountReport[$questionType] = [
          'name' => $name,
          'total' => 0,
          'correct' => 0
        ];
      }

      $correctCountReport[$questionType]['total']++;

      // 정답 여부 체크
      $isCorrect = $answer === $this->questions[$index]['answer'];

      if ($isCorrect) {
        // 배점표 사용 여부에 따른 점수 계산
        if ($this->testsheet->use_score_table) {
          $score = $this->testsheet->parsed_score_table['table'][$index + 1] ?? 1;
          $correctCount += $score;
          $correctCountReport[$questionType]['correct'] += $score;
        } else {
          $correctCount++;
          $correctCountReport[$questionType]['correct']++;
        }
      }
    }

    // 만약 배점표를 사용한다면, correctCountReport의 total도 배점 기준으로 업데이트
    if ($this->testsheet->use_score_table) {
      foreach ($correctCountReport as $typeId => &$report) {
        $typeTotal = 0;
        foreach ($this->questions as $index => $question) {
          if ($question['question_type_id'] == $typeId) {
            $score = $this->testsheet->parsed_score_table['table'][$index + 1] ?? 1;
            $typeTotal += $score;
          }
        }
        $report['total'] = $typeTotal;
      }
    }

    // Update existing test sheet answer record
    TestSheetAnswer::where('test_sheet_id', $this->testsheet->id)
      ->where('user_id', auth()->id())
      ->where('status', 'pending')
      ->update([
        'answers' => $this->answers,
        'correct_count' => $correctCount,
        'correct_count_report' => $correctCountReport,
        'status' => 'completed',
        'time' => $this->elapsedTime
      ]);

    return redirect("/test-sheet-result/{$this->testsheet->id}");
  }



  public function updateTimer()
  {
    $this->elapsedTime++;

    // 5초마다 시간 저장
    if (time() - $this->lastSaveTime >= 5) {
      TestSheetAnswer::updateOrCreate(
        [
          'test_sheet_id' => $this->testsheet->id,
          'user_id' => auth()->id(),
          'status' => 'pending'
        ],
        [
          'time' => $this->elapsedTime,
          'answers' => $this->answers,
          'correct_count' => 0
        ]
      );
      $this->lastSaveTime = time();
    }
  }
}
