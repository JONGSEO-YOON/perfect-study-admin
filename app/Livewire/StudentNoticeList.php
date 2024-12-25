<?php

namespace App\Livewire;

use App\Models\Lecture;
use App\Models\Notice;
use App\Models\Student;
use Livewire\Attributes\Layout;
use Livewire\Component;

class StudentNoticeList extends Component
{
  public $notices;

  public function mount()
  {
    $this->notices = Notice::where(function ($query) {
      $query->whereJsonContains('target_groups', '학생')
        ->orWhereJsonContains('target_groups', '"학생"');
    })
      ->orderByDesc('pinned_at')  // First order by pinned status (null values will come last)
      ->orderByDesc('created_at') // Then order by creation date
      ->get();
  }

  #[Layout('components.layouts.student')]
  public function render()
  {
    return view('livewire.student-notice-list');
  }
}
