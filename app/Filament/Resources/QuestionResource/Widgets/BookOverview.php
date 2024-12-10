<?php

namespace App\Filament\Resources\QuestionResource\Widgets;

use App\Models\Material;
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

class BookOverview extends Widget implements HasForms, HasActions
{
    use InteractsWithForms, InteractsWithActions;

    #[Url]
    public $parent_id;

    public $folderId = null;

    public $materials = [];

    public $material = null;

    public $hasUpperLevel = false;

    public $selectedMaterialId = null;

    protected static string $view = 'filament.resources.question-resource.widgets.book-overview';

    public function mount()
    {

        $this->fetchMaterials();
    }

    public function fetchMaterials()
    {
        $parentId = $this->parent_id;

        if ($parentId !== null) {
            $this->material = Material::findOrFail($parentId);
            if ($this->material->type === 'book') {
                $this->selectedMaterialId = $this->parent_id;
                $parentId = $this->material->parent_id;
            }
            $this->hasUpperLevel = $parentId !== null;
        }
        $this->folderId = $parentId;
        $this->materials = Material::where('parent_id', $parentId)
            ->where('user_id', auth()->id())
            ->get();
    }

    public function addNewBookOrFolderAction()
    {
        return Action::make('addNewBookOrFolder')
            ->modalWidth('sm')
            ->modalHeading('교재 추가')
            ->modalSubmitActionLabel('추가')
            ->form([
                Grid::make(1)
                    ->schema([
                        ToggleButtons::make('type')
                            ->inline()
                            ->options([
                                'book' => '교재',
                                'folder' => '폴더',
                            ])
                            ->live()
                            ->label('종류')
                            ->grouped()
                            ->required()
                            ->default('book'),
                        TextInput::make('name')
                            ->required()
                            ->label('이름'),
                        FileUpload::make('image_path')
                            ->label('사진')
                            ->image()
                            ->visible(fn(Get $get) => $get('type') === 'book')
                            ->placeholder('사진 업로드'),
                    ])
            ])
            ->action(function ($data) {

                $material = Material::create([
                    'user_id' => auth()->id(),
                    'parent_id' => $this->folderId,
                    'type' => $data['type'],
                    'name' => $data['name'],
                    'image_path' => $data['image_path'] ?? null,
                ]);
                Notification::make()
                    ->title('추가되었습니다.')
                    ->success()
                    ->send();
                $queries = [];
                $queries['parent_id'] = $material->id;
                $queries['tableFilters'] = [
                    'material_id' => [
                        'material_id' => $material->id,
                    ],
                ];
                redirect('/admin/questions?' . http_build_query($queries));
                // $this->fetchMaterials();
            });
    }

    public function deleteMaterialAction()
    {
        return Action::make('deleteMaterial')
            ->requiresConfirmation()
            ->label('선택 삭제')
            ->icon('heroicon-m-trash')
            ->modalHeading('삭제')
            ->modalDescription('연관된 모든 문제가 삭제됩니다.')
            ->color('danger')
            ->size('sm')
            ->action(function () {
                Material::findOrFail($this->selectedMaterialId)->delete();
                redirect('/admin/questions?' . http_build_query([
                    'parent_id' => $this->folderId,
                ]));
                //if ($this->parent_id === $this->selectedMaterialId) {
                //    redirect('/admin/questions?' . http_build_query([
                //        'parent_id' => $this->folderId,
                //    ]));
                //} else {
                //    $this->selectedMaterialId = null;
                //    $this->fetchMaterials();
                //}
            });
    }

    public function redirectTo($url, $parentId)
    {
        // if $parentId is 'up' then go up one level
        if ($parentId === 'up') {
            // if $this->material is a book, then go up to the parent folder
            if ($this->material->type === 'book') {
                $parent = Material::findOrFail($this->material->parent_id);
                $parentId = $parent->parent_id;
            } else {
                $parentId = $this->material->parent_id;
            }
            // $parentId = $this->material->parent_id;
        }
        $url = parse_url($url);
        parse_str($url['query'] ?? '', $queries);
        $queries['parent_id'] = $parentId;
        $queries['tableFilters'] = [
            'material_id' => [
                'material_id' => $parentId,
            ],
        ];

        return redirect(
            $url['path'] . '?' . http_build_query($queries)
        );
    }

    public function navigateToParent($parentId)
    {
        return $this->redirectRoute(request()->route()->getName(), ['parent_id' => $parentId]);
    }

    public function getBreadcrumbPath()
    {
        $path = [];
        $current = $this->material;

        while ($current) {
            array_unshift($path, $current);
            $current = $current->parent;
        }

        return $path;
    }
}
