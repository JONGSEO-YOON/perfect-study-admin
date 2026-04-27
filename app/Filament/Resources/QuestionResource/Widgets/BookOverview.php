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

    /**
     * 본점/root_admin이 루트 레벨에서 볼 때 학원별로 그룹화된 교재 목록.
     * [{academy_id, academy_name, materials: Collection}, ...]
     */
    public $materialsByAcademy = [];

    public $isAcademyGroupingActive = false;

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

        // 본점(스터디) admin 또는 root_admin이 루트 레벨일 때만 학원별 그룹화 활성화
        $user = auth()->user();
        $canSeeAllAcademies = $user && (
            $user->role === 'root_admin'
            || ($user->role === 'admin' && $user->academy_id === 1)
        );
        $this->isAcademyGroupingActive = $canSeeAllAcademies && $parentId === null;

        if ($this->isAcademyGroupingActive) {
            $academyMap = \App\Models\Academy::pluck('name', 'id')->toArray();

            $this->materialsByAcademy = $this->materials
                ->groupBy('academy_id')
                ->map(function ($group, $academyId) use ($academyMap) {
                    return [
                        'academy_id' => (int) $academyId,
                        'academy_name' => $academyMap[$academyId] ?? '미지정',
                        'materials' => $group->values(),
                    ];
                })
                // 본점(1) 먼저, 그 다음 학원명 가나다순
                ->sortBy(fn($g) => $g['academy_id'] === 1 ? '0' : '1' . $g['academy_name'])
                ->values()
                ->toArray();
        } else {
            $this->materialsByAcademy = [];
        }
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

    /**
     * 교재 내 문제들의 번호(seq)를 등록 순서(id 기준)로 재정렬
     */
    public function resequenceQuestionsAction()
    {
        return Action::make('resequenceQuestions')
            ->label('문제 번호 재정렬')
            ->icon('heroicon-m-bars-arrow-down')
            ->color('gray')
            ->size('sm')
            ->requiresConfirmation()
            ->modalHeading('문제 번호 재정렬')
            ->modalDescription('이 책의 모든 문제 번호를 등록 순서(id 기준)대로 1, 2, 3... 으로 다시 매깁니다.')
            ->visible(function () {
                if (!$this->selectedMaterial) return false;
                if ($this->selectedMaterial->type !== 'book') return false;
                return auth()->user()->isRoleAbove('manager', true);
            })
            ->action(function () {
                $material = $this->selectedMaterial;
                if (!$material) return;

                $questions = \App\Models\Question::withoutGlobalScopes()
                    ->where('material_id', $material->id)
                    ->whereNull('parent_question_id')
                    ->orderBy('id')
                    ->get();

                $count = 0;
                foreach ($questions as $index => $q) {
                    $newSeq = $index + 1;
                    if ($q->seq !== $newSeq) {
                        \DB::table('questions')->where('id', $q->id)->update(['seq' => $newSeq]);
                        $count++;
                    }
                }

                Notification::make()
                    ->title("문제 번호가 재정렬되었습니다 (총 {$questions->count()}문제, {$count}개 변경)")
                    ->success()
                    ->send();

                redirect('/admin/questions?' . http_build_query([
                    'parent_id' => $material->id,
                ]));
            });
    }

    /**
     * 공유받은 교재(또는 폴더)를 내 학원으로 복사 (cascade: 하위 + 문제까지 모두)
     */
    public function copyMaterialAction()
    {
        return Action::make('copyMaterial')
            ->label('내 학원으로 복사')
            ->icon('heroicon-m-document-duplicate')
            ->color('warning')
            ->size('sm')
            ->requiresConfirmation()
            ->modalHeading('내 학원으로 복사')
            ->modalDescription(function () {
                if (!$this->selectedMaterial) return '';
                $type = $this->selectedMaterial->type === 'folder' ? '폴더(하위 모두)' : '교재';
                return "선택한 {$type}와 모든 문제를 내 학원으로 복사합니다.";
            })
            ->visible(function () {
                if (!$this->selectedMaterial) return false;
                // 본점(academy_id=1, '퍼펙트 스터디')의 root_admin/admin만 다른 학원 교재를 본점으로 복사 가능
                $user = auth()->user();
                $myAcademyId = $user->academy_id;
                $isParentAcademy = $user->role === 'root_admin'
                    || ($user->role === 'admin' && $user->academy_id === 1);

                return $isParentAcademy
                    && $this->selectedMaterial->academy_id !== $myAcademyId;
            })
            ->action(function () {
                $source = $this->selectedMaterial;
                if (!$source) return;

                $copied = $this->deepCopyMaterial($source, $this->folderId);

                Notification::make()
                    ->title("내 학원으로 복사되었습니다: {$copied['name']} (교재 {$copied['materials']}개, 문제 {$copied['questions']}개)")
                    ->success()
                    ->send();

                redirect('/admin/questions?' . http_build_query([
                    'parent_id' => $this->folderId,
                ]));
            });
    }

    /**
     * Material을 내 학원으로 깊은 복사 (children + questions 포함)
     * @return array ['name' => string, 'materials' => int, 'questions' => int]
     */
    protected function deepCopyMaterial(Material $source, ?int $newParentId): array
    {
        $myAcademyId = auth()->user()->academy_id;
        $stats = ['name' => $source->name, 'materials' => 0, 'questions' => 0];

        // 1. Material 자체 복사
        $newMaterial = Material::create([
            'name' => $source->name,
            'type' => $source->type,
            'parent_id' => $newParentId,
            'image_path' => $source->image_path,
            'academy_id' => $myAcademyId,
            'user_id' => auth()->id(),
        ]);
        $stats['materials']++;

        // 2. 책이면 questions 복사
        if ($source->type === 'book') {
            foreach ($source->questions as $q) {
                $newQ = $q->replicate(['material_id', 'academy_id', 'user_id']);
                $newQ->material_id = $newMaterial->id;
                $newQ->academy_id = $myAcademyId;
                $newQ->user_id = auth()->id();
                $newQ->save();

                // 문제의 choices도 복사
                foreach ($q->choices as $choice) {
                    $newChoice = $choice->replicate(['question_id']);
                    $newChoice->question_id = $newQ->id;
                    $newChoice->save();
                }

                $stats['questions']++;
            }
        }

        // 3. 폴더면 children 재귀 복사
        if ($source->type === 'folder') {
            foreach ($source->children as $child) {
                $childStats = $this->deepCopyMaterial($child, $newMaterial->id);
                $stats['materials'] += $childStats['materials'];
                $stats['questions'] += $childStats['questions'];
            }
        }

        return $stats;
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
