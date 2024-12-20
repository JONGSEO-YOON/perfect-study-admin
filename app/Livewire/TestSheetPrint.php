<?php

namespace App\Livewire;

use App\Models\Student;
use App\Models\TestSheet;
use Livewire\Attributes\Layout;
use Livewire\Component;

class TestSheetPrint extends Component
{
  public $testSheet;

  public function mount()
  {
    $this->testSheet = TestSheet::findOrFail(request()->query('test_sheet_id'));
  }

  #[Layout('components.layouts.print')]
  public function render()
  {
    return view('livewire.test-sheet-print');
  }
}
