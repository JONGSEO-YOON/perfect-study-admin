<?php

namespace App\Filament\Pages;

use App\Filament\Resources\QuestionResource;
use App\Models\QuestionCategory;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Livewire\Attributes\Url;

class ScannedQuestions extends Page
{

    protected static ?string $slug = 'scanned-questions/{id}';

    protected static string $view = 'filament.pages.scanned-questions';

    protected static bool $shouldRegisterNavigation = false;

    protected ?string $maxContentWidth = '4xl';

    protected static ?string $title = '문제 등록';

    public $id;

    #[Url]
    public $material_id = null;

    public $arguments = [];

    public function mount($id)
    {
        $this->id = $id;
    }

    public function deleteQuestionAction(): Action
    {
        return Action::make('deleteQuestion')
            ->requiresConfirmation()
            ->modalHeading('문제 삭제')
            ->action(function () {
                $this->dispatch('delete-question');
            });
    }

    public function addQuestionsAction(): Action
    {
        return Action::make('addQuestions')
            ->requiresConfirmation()
            ->modalHeading('문제 등록')
            ->action(function () {
                $this->dispatch('add-questions');
            });
    }

    public function editQuestionAction(): Action
    {
        return Action::make('editQuestion')
            ->action(function ($arguments) {
                $this->arguments = $arguments;
                $this->replaceMountedAction('editQuestionInternal');
            });
    }

    public function editQuestionInternalAction(): Action
    {
        $url = null;
        if ($this->arguments['url'] ?? false) {
            $url = explode('/storage', $this->arguments['url'])[1];
        }
        return Action::make('editQuestionInternal')
            ->modalHeading('문제 편집하기')
            ->modalWidth('2xl')
            ->fillForm([
                'question_display_type' => 'image',
                'image_path' => $url,
                'answer_type' => 'multiple_choice',
                'choices_display_type' => 'in_question',
                'choices' => [],
                'choices_count' => 4,
                'material_id' => $this->material_id,
                ...$this->arguments,
            ])
            ->form(QuestionResource::_form())
            ->action(function ($data) {
                if ($data['question_type_id'] ?? false) {
                    $questionCategory = QuestionCategory::find($data['question_type_id'])->toArray();
                    $data['questionCategory'] = $questionCategory;
                }
                $this->dispatch('edit-question', $data);
                // $questionCategory = QuestionCategory::find($data['question_type_id'])->toArray();
            })
            ->modalCancelActionLabel('닫기');
    }
}
