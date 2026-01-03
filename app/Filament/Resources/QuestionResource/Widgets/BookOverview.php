<?php

namespace App\Filament\Resources\QuestionResource\Widgets;

use App\Models\Material;
use App\Models\Teacher;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
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
    public $selectedMaterial = null;

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
        if ($this->selectedMaterialId) {
            $this->selectedMaterial = Material::findOrFail($this->selectedMaterialId);
        }
        $this->folderId = $parentId;
        $this->materials = Material::where('parent_id', $parentId)
            ->visible()
            ->get();
    }

    public function setSelectedMaterial($materialId)
    {
        $this->selectedMaterialId = $materialId;
        $this->selectedMaterial = Material::findOrFail($materialId);
    }

    public function materialForm()
    {
        return [
            Hidden::make('id')
                ->default(null),
            Grid::make(1)
                ->schema([
                    ToggleButtons::make('type')
                        ->inline()
                        ->options([
                            'book' => '교재',
                            'folder' => '폴더',
                        ])
                        ->live()
                        ->visible(fn(Get $get) => $get('id') === null)
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
                    Select::make('visible_user_ids')
                        ->label('공유')
                        ->visible(function () {
                            return auth()->user()->isRoleAbove('manager', true);
                        })
                        ->multiple()
                        ->options(function (Get $get) {
                            return User::where('userable_type', Teacher::class)
                                ->when($get('id'), function ($query, $id) {
                                    return $query->where('id', '!=', $this->selectedMaterial->user_id);
                                })
                                ->when($get('id') === null, function ($query) {
                                    return $query->where('id', '!=', auth()->id());
                                })
                                ->get()->pluck('name', 'id');
                        })
                ])

        ];
    }

    public function addNewBookOrFolderAction()
    {
        return Action::make('addNewBookOrFolder')
            ->modalWidth('sm')
            ->modalHeading('교재 추가')
            ->modalSubmitActionLabel('추가')
            ->form([
                ...$this->materialForm(),
            ])
            ->action(function ($data) {
                $material = Material::create([
                    'user_id' => auth()->id(),
                    'parent_id' => $this->folderId,
                    'type' => $data['type'],
                    'name' => $data['name'],
                    'image_path' => $data['image_path'] ?? null,
                ]);
                $material->visibleUsers()->sync($data['visible_user_ids'] ?? []);
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
            });
    }

    public function editMaterialAction()
    {
        return Action::make('editMaterial')
            ->label('선택 수정')
            ->modalWidth('sm')
            ->icon('heroicon-m-pencil-square')
            ->size('sm')
            ->fillForm(function () {
                return [
                    ...$this->selectedMaterial->toArray(),
                    'visible_user_ids' => $this->selectedMaterial->visibleUsers->pluck('id')->toArray(),
                ];
            })
            ->form([
                ...$this->materialForm(),
            ])
            ->action(function ($data) {
                $material = Material::findOrFail($data['id']);
                $material->update([
                    'name' => $data['name'],
                    'image_path' => $data['image_path'] ?? null,
                ]);
                $material->visibleUsers()->sync($data['visible_user_ids']);
                redirect('/admin/questions?' . http_build_query([
                    'parent_id' => $this->folderId,
                ]));
                Notification::make()
                    ->title('수정되었습니다.')
                    ->success()
                    ->send();
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
