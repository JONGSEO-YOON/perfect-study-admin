<?php

namespace App\Livewire\Parent;

use Livewire\Component;
use Livewire\Attributes\Layout;

class Report extends Component
{

    #[Layout('layouts.parent')]
    public function render()
    {
        return view('livewire.parent.report');
    }
}
