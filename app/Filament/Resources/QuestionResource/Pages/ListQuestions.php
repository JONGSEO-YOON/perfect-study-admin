<?php

namespace App\Filament\Resources\QuestionResource\Pages;

use App\Filament\Resources\QuestionResource;
use App\Filament\Resources\QuestionResource\Widgets\BookOverview;
use App\Livewire\QuestionTypeField;
use App\Models\Material;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
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
                    return [
                        'material_id' => $this->parent_id,
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
                        $this->convertPdfToImages($attachmentPath, $outputDir);
                    }
                    if (!$attachmentName) {
                        Notification::make()
                            ->title('파일 업로드 실패')
                            ->danger()
                            ->send();
                    }
                    return redirect('/admin/scanned-questions/' . $attachmentName . '?material_id=' . ($data['material_id'] ?? '') . '&is_public=' . ($data['is_public'] ?? '0'));
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
                        return [];
                    }

                    $material = Material::find($this->parent_id);
                    if ($material->type !== 'book') {
                        return [];
                    }

                    return [
                        'material_id' => $this->parent_id,
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

    public function convertPdfToImages($pdfPath, $outputDir)
    {
        $imagick = new Imagick();
        $imagick->readImage($pdfPath);
        $imagick->setResolution(600, 600);
        $imagick->setImageFormat('jpg');

        $numPages = $imagick->getNumberImages();

        for ($i = 0; $i < $numPages; $i++) {
            $image = new Imagick();
            $image->setResolution(300, 300);
            $image->setColorspace(Imagick::COLORSPACE_SRGB);
            $image->readImage($pdfPath . "[" . $i . "]");
            $image->setImageAlphaChannel(Imagick::ALPHACHANNEL_REMOVE);
            $image->mergeImageLayers(Imagick::LAYERMETHOD_FLATTEN);
            $image->setImageBackgroundColor(new ImagickPixel('white'));

            // $geo = $image->getImageGeometry();
            // $width = $geo['width'];
            // $height = $geo['height'];
            // 76픽셀만큼 상하좌우를 crop
            // $cropWidth = $width; //- 152;  // 좌우 각각 76픽셀
            // $cropHeight = $height; // - 152;  // 상하 각각 76픽셀
            // $image->cropImage($cropWidth, $cropHeight, 76, 76);
            // $image->trimImage(0);

            $image->setImageFormat('jpg');
            $image->writeImage($outputDir . "/page_" . ($i + 1) . ".jpg");
            $image->clear();

            // $this->progress = ($i + 1) / $numPages * 100;
            // $this->dispatch('progressUpdated');
        }

        $imagick->clear();
    }
}
