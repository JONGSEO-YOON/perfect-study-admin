<?php

namespace App\Livewire;

use App\Models\Lecture;
use App\Models\Student;
use Livewire\Attributes\Layout;
use Livewire\Component;

class MyReportCard extends Component
{

  public $student;
  public $classroomId;
  public $dateFrom;
  public $classrooms;
  public $weeks;

  public function mount()
  {
    $this->student = auth()->user()->userable;
    $this->classrooms = $this->student->classrooms;
    $this->classroomId = $this->classrooms->first()?->id;

    $now = now();
    $startOfWeek = $now->startOfWeek(1)->format('Y-m-d');
    $endOfWeek = $now->endOfWeek(7)->format('Y-m-d');

    $this->dateFrom = "{$startOfWeek}/{$endOfWeek}";
    $this->weeks = ReportCard::getWeekOptions();
  }

  #[Layout('components.layouts.student')]
  public function render()
  {
    return view('livewire.my-report-card');
  }
}
