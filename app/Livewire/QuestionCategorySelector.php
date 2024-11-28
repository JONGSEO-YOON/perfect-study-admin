<?php

namespace App\Livewire;

use App\Models\Lecture;
use App\Models\QuestionCategory;
use Ijpatricio\Mingle\Concerns\InteractsWithMingles;
use Ijpatricio\Mingle\Contracts\HasMingles;
use Illuminate\Support\Collection;
use Livewire\Component;

class QuestionCategorySelector extends Component implements HasMingles
{
    use InteractsWithMingles;

    public $selectedId = null;

    public function component(): string
    {
        return 'resources/js/QuestionCategorySelector.js';
    }

    public function mingleData(): array
    {
        $questionCategories = QuestionCategory::getFullTree();
        return [
            'questionCategories' => $questionCategories,
            'selectedId' => $this->selectedId,
        ];
    }
}
