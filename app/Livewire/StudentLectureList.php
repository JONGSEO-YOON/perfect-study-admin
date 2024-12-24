<?php

namespace App\Livewire;

use App\Models\Lecture;
use App\Models\Student;
use Livewire\Attributes\Layout;
use Livewire\Component;

class StudentLectureList extends Component
{

  public $lectures;

  public function mount()
  {
    $student = auth()->user()->userable;
    $lectures = Lecture::availableFor($student)->published()->get();
    $this->lectures = $lectures;
  }

  #[Layout('components.layouts.student')]
  public function render()
  {
    return view('livewire.student-lecture-list');
  }
}
