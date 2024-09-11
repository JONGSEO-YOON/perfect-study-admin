<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class SelectPages extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $slug = 'select-pages/{id}';

    protected static string $view = 'filament.pages.select-pages';

    protected static bool $shouldRegisterNavigation = false;

    protected ?string $maxContentWidth = '4xl';

    protected static ?string $title = '문제 등록';

    public $id;

    public function mount($id)
    {
        $this->id = $id;
        dd($this->id);
    }
}
