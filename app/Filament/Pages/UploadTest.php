<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;

class UploadTest extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-m-arrow-up-on-square-stack';

    protected static ?string $navigationLabel = '문제 등록';

    //title
    protected static ?string $title = '문제 등록';

    protected static string $view = 'filament.pages.upload-test';

    protected ?string $maxContentWidth = '4xl';

    public ?array $data = [];

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                FileUpload::make('attachment')
                    ->label('교재/시험지 업로드')
                    ->placeholder('한글(hwp) / PDF / 이미지를 올려주세요!')
            ])
            ->statePath('data');
    }
}
