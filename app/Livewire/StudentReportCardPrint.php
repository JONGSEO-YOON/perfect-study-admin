<?php

namespace App\Livewire;

use App\Models\Student;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Attributes\Title;

class StudentReportCardPrint extends Component
{
  public $student;
  public $classroomId;
  public $dateFrom;
  public $dateUntil;

  public function mount()
  {
    $this->student = Student::findOrFail(request()->query('student_id'));
    $this->classroomId = request()->query('classroom_id');
    $this->dateFrom = request()->query('date_from');
    $this->dateUntil = request()->query('date_until');
  }

  #[Title('성적표 출력')]
  #[Layout('components.layouts.print')]
  public function render()
  {
    return view('livewire.student-report-card-print');
  }
}
