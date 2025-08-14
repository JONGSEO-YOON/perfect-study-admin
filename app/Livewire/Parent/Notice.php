<?php

namespace App\Livewire\Parent;

use Livewire\Component;
use App\Models\Notice as NoticeModel;

class Notice extends Component
{
    public $notice;

    public function mount($id)
    {
        $this->notice = NoticeModel::find($id);
    }

    public function render()
    {
        return view('livewire.parent.notice');
    }
}
