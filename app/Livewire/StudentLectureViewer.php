<?php

namespace App\Livewire;

use App\Models\Lecture;
use App\Models\LectureVideoViewHistory;
use App\Models\Student;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

class StudentLectureViewer extends Component
{

  public $lecture;
  public $histories;

  public function mount($id)
  {
    $this->lecture = Lecture::find($id);
    $this->histories = LectureVideoViewHistory::where('lecture_id', $this->lecture->id)
      ->where('user_id', auth()->user()->id)
      ->get();
  }

  #[Layout('components.layouts.student')]
  public function render()
  {
    return view('livewire.student-lecture-viewer');
  }

  #[On('videoSelected')]
  public function videoSelected($video)
  {
    //firstorcreate
    $history =
      LectureVideoViewHistory::firstOrCreate([
        'lecture_id' => $this->lecture->id,
        'video_id' => $video['id'],
        'user_id' => auth()->user()->id,
      ]);
  }
}
