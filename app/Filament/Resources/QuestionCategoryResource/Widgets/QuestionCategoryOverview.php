<?php

namespace App\Filament\Resources\QuestionCategoryResource\Widgets;

use App\Models\QuestionCategory;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Get;
use Filament\Livewire\Notifications;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;
use Livewire\Attributes\Url;

class QuestionCategoryOverview extends Widget implements HasForms, HasActions
{
    use InteractsWithForms, InteractsWithActions;

    public $categories = [];

    protected static string $view = 'filament.resources.question-category-resource.widgets.question-category-overview';

    public function mount()
    {
        $this->categories = QuestionCategory::where('depth', 0)
            ->orderBy('order')
            ->get();
    }


    public function redirectTo($url, $parentId)
    {
        return redirect(
            '/admin/question-categories/' . $parentId
        );
    }
}
