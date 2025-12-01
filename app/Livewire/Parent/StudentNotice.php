<?php

namespace App\Livewire\Parent;

use Livewire\Component;
use App\Models\StudentNotice as StudentNoticeModel;
use Livewire\Attributes\Layout;

class StudentNotice extends Component
{
    public $notice;

    public function mount($id)
    {
        $this->notice = StudentNoticeModel::find($id);
    }


    #[Layout('layouts.parent')]
    public function render()
    {
        return view('livewire.parent.student-notice');
    }
}
