<?php

namespace App\Livewire;

use App\Models\StudentNotice;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class StudentNoticeList extends Component
{
  use WithPagination;

  #[Layout('components.layouts.student')]
  public function render()
  {
    $student = auth()->user()->userable;
    $classroomIds = $student->classrooms->pluck('id')->toArray();

    $notices = StudentNotice::with(['student.user', 'classroom'])
      ->where(function ($query) use ($student, $classroomIds) {
        // 1. 전체 공지 (student_id IS NULL AND classroom_id IS NULL)
        $query->where(function ($q) {
          $q->whereNull('student_id')
            ->whereNull('classroom_id');
        })
          // 2. 반 공지 (classroom_id IN student's classrooms)
          ->orWhereIn('classroom_id', $classroomIds)
          // 3. 학생 개인 공지 (student_id = student's id)
          ->orWhere('student_id', $student->id);
      })
      ->orderByDesc('pinned_at')
      ->orderByDesc('created_at')
      ->simplePaginate(10);

    return view('livewire.student-notice-list', [
      'notices' => $notices
    ]);
  }
}
