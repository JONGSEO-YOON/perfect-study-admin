<?php

namespace App\Livewire\Parent;

use Livewire\Component;
use Livewire\Attributes\Layout;

class RefundPolicy extends Component
{
    public function closeModal()
    {
        $this->redirect(route('parent.payment'), navigate: true);
    }

    #[Layout('layouts.parent')]
    public function render()
    {
        return view('livewire.parent.refund-policy');
    }
}
