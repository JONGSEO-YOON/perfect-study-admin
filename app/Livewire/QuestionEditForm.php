<?php

namespace App\Livewire;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Livewire\Component;

class QuestionEditForm extends Component implements HasForms
{
    use InteractsWithForms;

    public function render()
    {
        return view('livewire.question-edit-form');
    }


    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('문제 유형')
                    ->schema([
                        Select::make('related')
                            ->label('유사/연계 문제')
                            ->placeholder('문제를 선택해 주세요.')
                    ]),
                Section::make('해설')
                    ->schema([

                        FileUpload::make('attachment')
                            ->label('해설지 업로드')
                            ->placeholder('해설지를 올려주세요.'),
                        FileUpload::make('attachment')
                            ->label('해설 강의 영상 업로드')
                            ->placeholder('해설 강의 영상을 올려주세요.')
                    ])
            ])
            ->statePath('data');
    }
}
