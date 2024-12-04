<?php

namespace App\Livewire;

use Ijpatricio\Mingle\Concerns\InteractsWithMingles;
use Ijpatricio\Mingle\Contracts\HasMingles;
use Illuminate\Support\Collection;
use Livewire\Component;

class ExamPrintLayout extends Component implements HasMingles
{
    use InteractsWithMingles;

    public $scale = 1;

    public function component(): string
    {
        return 'resources/js/ExamPrintLayout.js';
    }

    public function mingleData(): array
    {
        return [
            'scale' => $this->scale,
            'message' => 'Message in a bottle 🍾',
        ];
    }

    public function doubleIt($amount)
    {
        return $amount * 2;
    }

    // public function onPageSelected($data)
    // {
    //     dd($data);
    // }
}
