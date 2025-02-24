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

    protected ?string $maxContentWidth = '4xl';

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
        ];
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
