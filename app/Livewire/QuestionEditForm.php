<?php

namespace App\Livewire;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Infolists\Components\KeyValueEntry;
use Livewire\Attributes\On;
use Livewire\Component;
use Rupadana\FilamentSlider\Components\Concerns\InputSliderBehaviour;
use Rupadana\FilamentSlider\Components\InputSlider;
use Rupadana\FilamentSlider\Components\InputSliderGroup;

class QuestionEditForm extends Component implements HasForms
{
    use InteractsWithForms;

    public $data = [
        'auto' => false,
        'difficulty' => 5,
        'attachment' => [],
        'part' => [],
        'answer_type' => null,
        'answer' => null,
        'related' => [],
        'meta' => [],
        'memo' => null,
        'page_number' => 0,
        'id' => '',
    ];

    public $question = null;

    public function render()
    {
        return view('livewire.question-edit-form');
    }

    #[On('questionSelected')]
    public function onQuestionSelected($question)
    {
        $this->question = $question;
    }

    public function saveQuestion()
    {
        $this->question = null;
        $this->data = [
            'auto' => false,
            'difficulty' => 5,
            'attachment' => [],
            'part' => [],
            'answer_type' => null,
            'answer' => null,
            'related' => [],
            'meta' => [],
            'memo' => null,
            'page_number' => 0,
            'id' => '',
        ];
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('문제 유형')
                    ->schema([
                        Hidden::make('page_number')
                            ->default(0),
                        Select::make('part')
                            ->searchable()
                            ->options([
                                '고등 수학 (상)' => [
                                    '다항식의 덧샘과 뺄샘' => '다항식의 덧샘과 뺄샘',
                                    '곱샘 공식' => '곱샘 공식',
                                    '곱샘 공식의 변형' => '곱샘 공식의 변형',
                                ],
                                '고등 수학 (하)' => [
                                    '집합의 뜻' => '집합의 뜻',
                                    '집합의 포함관계 ' => '집합의 포함관계',
                                ],
                            ])
                            ->placeholder('범위 선택')
                            ->multiple()
                            ->label('범위'),
                        Select::make('answer_type')
                            ->label('답변 유형')
                            ->options([
                                '객관식' => '객관식',
                                '주관식 정수형 ' => '주관식 정수형',
                                '주관식 ' => '주관식',
                            ])
                            ->placeholder('답변 유형을 선택해주세요.'),
                        Toggle::make('auto')
                            ->reactive()
                            ->live()
                            ->label('자동 체점'),
                        TextInput::make('answer')
                            ->reactive()
                            ->live()
                            ->visible(function ($get) {
                                return $get('auto');
                            })
                            ->label('답변'),

                        InputSliderGroup::make()
                            ->sliders([
                                InputSlider::make('difficulty')

                                    ->default(5)
                            ])
                            ->min(1)
                            ->step(1)
                            ->max(10)
                            ->behaviour([
                                InputSliderBehaviour::DRAG,
                                InputSliderBehaviour::TAP
                            ])
                            ->label('난이도')
                            ->enableTooltips(),

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
                    ]),
                Section::make('기타')
                    ->schema([
                        Textarea::make('memo')
                            ->label('비고'),
                        KeyValue::make('meta')
                            ->label('메타 정보')
                            ->keyLabel('속성')
                            ->valueLabel('정보')
                            ->addActionLabel('속성 추가')
                    ])
            ])
            ->statePath('data');
    }
}
