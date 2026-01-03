<?php

namespace App\Livewire\Parent;

use Livewire\Component;
use App\Models\Notice as NoticeModel;
use Livewire\Attributes\Layout;

class Notice extends Component
{
    public $notice;

    public function mount($id)
    {
        $this->notice = NoticeModel::find($id);
    }


    #[Layout('layouts.parent')]
    public function render()
    {
        return view('livewire.parent.notice');
    }
}
