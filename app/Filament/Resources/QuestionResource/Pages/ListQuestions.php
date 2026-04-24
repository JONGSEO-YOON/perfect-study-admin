<?php

namespace App\Filament\Resources\QuestionResource\Pages;

use App\Filament\Resources\QuestionResource;
use App\Filament\Resources\QuestionResource\Widgets\BookOverview;
use App\Jobs\ConvertPdfToImagesJob;
use App\Livewire\QuestionTypeField;
use App\Models\Material;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use \Imagick;
use \ImagickPixel;
use Livewire\Attributes\Url;

class ListQuestions extends ListRecords
{
    protected static string $resource = QuestionResource::class;

    protected static ?string $title = '문제 은행';

    protected ?string $maxContentWidth = '7xl';

    #[Url]
    public $parent_id = null;


    public function getBreadcrumb(): ?string
    {
        return null;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('create-question-by-scan')
                ->icon('heroicon-m-document-magnifying-glass')
                ->label('스캔으로 등록하기')
                ->modalHeading('스캔하여 등록하기')
                ->closeModalByClickingAway(false)
                ->closeModalByEscaping(false)
                ->modalWidth('2xl')
                ->modalSubmitActionLabel('스캔하기')
                ->visible(function () {
                    if (!$this->parent_id) {
                        return true;
                    }
                    $material = Material::find($this->parent_id);
                    if (!$material) {
                        return true;
                    }
                    if ($material->type !== 'book') {
                        return false;
                    }
                    return $material->is_editable;
                })
                ->fillForm(function () {
                    if (!$this->parent_id) {
                        return [];
                    }

                    $material = Material::find($this->parent_id);
                    if ($material->type !== 'book') {
                        return [];
                    }
                    $starting_seq = $material->questions()->max('seq') + 1;
                    return [
                        'material_id' => $this->parent_id,
                        'starting_seq' => $starting_seq,
                    ];
                })
                ->form([
                    Select::make('material_id')
                        ->label('교재')
                        ->searchable()
                        ->options(
                            \App\Models\Material::where('type', 'book')
                                ->editable()
                                ->get()->pluck('name', 'id')
                        )
                        ->live()
                        ->placeholder('교재를 선택하세요.'),
                    TextInput::make('starting_seq')
                        ->label('시작 문제 번호')
                        ->numeric()
                        ->required()
                        ->visible(fn(Get $get) => $get('material_id'))
                        ->placeholder('교재를 선택하세요.'),
                    Toggle::make('is_public')
                        ->columnSpanFull()
                        ->inline(false)
                        ->visible(fn(Get $get) =>  !$get('material_id'))
                        ->label('문제 공개 (타 강사 공유)'),
                    FileUpload::make('file')
                        ->placeholder('파일을 업로드하세요')
                        ->label('스캔할 파일 업로드')
                        ->previewable(false)
                        ->downloadable(true)
                        ->columnSpanFull(),
                    // ViewField::make('progress')
                    //     ->live()
                    //     ->reactive()
                    //     ->view('filament.components.forms.scan-progress')
                ])
                ->action(function ($data) {
                    $attachment = $data['file'];
                    $attachmentName = '';
                    if ($attachment) {
                        $attachmentParts = explode('.', $attachment);
                        $attachmentName = $attachmentParts[0];
                        $outputDir = storage_path('app/public/converted-pdfs/' . $attachmentName);
                        if (!file_exists($outputDir)) {
                            mkdir($outputDir, 0777, true);
                        }
                        $attachmentPath = storage_path('app/public/' . $attachment);
                        $this->convertPdfToImages(
                            $attachmentPath,
                            $outputDir,
                            $attachmentName,
                            $data['material_id'] ?? null,
                            $data['is_public'] ?? null
                        );
                    }
                    if (!$attachmentName) {
                        Notification::make()
                            ->title('파일 업로드 실패')
                            ->danger()
                            ->send();
                    }
                    // $livewire->dispatch('onScanStarted', [
                    //     'attachmentName' => $attachmentName,
                    // ]);
                    return redirect(
                        '/admin/scanned-questions/' . $attachmentName . '?material_id=' . ($data['material_id'] ?? '')
                            . '&is_public=' . ($data['is_public'] ?? '0')
                            . '&starting_seq=' . ($data['starting_seq'] ?? '1')
                    );
                }),
            Actions\CreateAction::make()
                ->icon('heroicon-m-plus-circle')
                ->label('단일 문제 등록하기')
                ->modalHeading('문제 등록하기')
                ->modalWidth('2xl')
                ->createAnother(false)
                ->modalSubmitActionLabel('저장')
                ->visible(function () {
                    if (!$this->parent_id) {
                        return true;
                    }
                    $material = Material::find($this->parent_id);
                    if (!$material) {
                        return true;
                    }
                    if ($material->type !== 'book') {
                        return false;
                    }
                    return $material->is_editable;
                })
                ->fillForm(function () {
                    if (!$this->parent_id) {
                        return [
                            'choices' => [],
                            'choices_count' => 6,
                        ];
                    }

                    $material = Material::find($this->parent_id);
                    if ($material->type !== 'book') {
                        return [
                            'choices' => [],
                            'choices_count' => 6,
                        ];
                    }

                    return [
                        'material_id' => $this->parent_id,
                        'choices' => [],
                        'choices_count' => 6,
                    ];
                })
                ->using(function ($data) {
                    return QuestionResource::handleCreate($data);
                }),

            Actions\Action::make('share-material')
                ->label(function () {
                    $material = $this->parent_id ? Material::find($this->parent_id) : null;
                    return $material?->type === 'folder' ? '폴더 학원 공유 (하위 모두)' : '교재 학원 공유';
                })
                ->icon('heroicon-m-share')
                ->color('info')
                ->modalHeading(function () {
                    $material = $this->parent_id ? Material::find($this->parent_id) : null;
                    return $material?->type === 'folder' ? '폴더 학원 공유 설정 (하위 교재 모두 cascade)' : '교재 학원 공유 설정';
                })
                ->modalWidth('md')
                ->modalSubmitActionLabel('저장')
                ->visible(function () {
                    if (!$this->parent_id) return false;
                    $material = Material::find($this->parent_id);
                    if (!$material) return false;
                    // root_admin 또는 자기 학원 교재의 manager 이상만
                    return auth()->user()->role === 'root_admin'
                        || ($material->academy_id === auth()->user()->academy_id && auth()->user()->isRoleAbove('manager', true));
                })
                ->fillForm(function () {
                    $material = Material::find($this->parent_id);
                    return [
                        'academy_ids' => $material?->visibleAcademies()->pluck('academies.id')->toArray() ?? [],
                    ];
                })
                ->form([
                    \Filament\Forms\Components\CheckboxList::make('academy_ids')
                        ->label('공유할 학원 선택')
                        ->options(function () {
                            return \App\Models\Academy::where('id', '!=', auth()->user()->academy_id)
                                ->pluck('name', 'id')
                                ->toArray();
                        })
                        ->columns(1),
                ])
                ->action(function (array $data) {
                    $material = Material::find($this->parent_id);
                    if (!$material) return;

                    $academyIds = $data['academy_ids'] ?? [];

                    // 본인 (폴더 또는 책) 공유
                    $material->visibleAcademies()->sync($academyIds);

                    // 폴더면 하위 모두 cascade로 동일하게 sync
                    if ($material->type === 'folder') {
                        $count = self::syncDescendantMaterials($material, $academyIds);
                        \Filament\Notifications\Notification::make()
                            ->title("폴더 공유 설정이 저장되었습니다 (하위 교재 {$count}개 포함)")
                            ->success()
                            ->send();
                    } else {
                        \Filament\Notifications\Notification::make()
                            ->title('교재 공유 설정이 저장되었습니다.')
                            ->success()
                            ->send();
                    }
                }),
        ];
    }

    /**
     * 폴더의 모든 후손(폴더+책)에 동일한 학원 공유 설정을 적용 (cascade)
     */
    protected static function syncDescendantMaterials(Material $folder, array $academyIds): int
    {
        $count = 0;
        foreach ($folder->children as $child) {
            $child->visibleAcademies()->sync($academyIds);
            $count++;
            if ($child->type === 'folder') {
                $count += self::syncDescendantMaterials($child, $academyIds);
            }
        }
        return $count;
    }

    protected function getHeaderWidgets(): array
    {
        return [
            BookOverview::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int | array
    {
        return 1;
    }

    public function convertPdfToImages($pdfPath, $outputDir, $attachmentName, $materialId, $isPublic)
    {
        ConvertPdfToImagesJob::dispatch($pdfPath, $outputDir, $attachmentName, $materialId, $isPublic);
    }
}
