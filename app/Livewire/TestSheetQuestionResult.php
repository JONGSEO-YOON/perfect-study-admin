<?php

namespace App\Livewire;

use App\Models\TestSheet;
use App\Models\TestSheetAnswer;
use Livewire\Component;

class TestSheetQuestionResult extends Component
{
  public $testsheet;
  public $questionNo;

  public function mount($id, $questionNo)
  {
    $this->testsheet = TestSheet::findOrFail($id);
    $this->questionNo = $questionNo;
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
