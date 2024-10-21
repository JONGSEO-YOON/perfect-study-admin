<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;

use \Imagick;
use \ImagickPixel;
use Livewire\Attributes\Computed;

class UploadTest extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-m-arrow-up-on-square-stack';

    protected static ?string $navigationLabel = '문제 등록';

    protected static ?string $title = '문제 등록';

    protected static string $view = 'filament.pages.upload-test';

    protected ?string $maxContentWidth = '4xl';

    public bool $isUploading = false;

    public int $progress = 0;

    public ?array $data = [
        'attachment' => []
    ];

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

    public function create(): void
    {
        $this->isUploading = true;
        $this->progress = 0;
        $attachment = $this->form->getState()['attachment'];
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
        $this->isUploading = false;
        redirect('/admin/select-pages/' . $attachmentName);
    }

    public function convertPdfToImages($pdfPath, $outputDir)
    {
        $imagick = new Imagick();
        $imagick->readImage($pdfPath);
        $imagick->setResolution(300, 300);
        $imagick->setImageFormat('jpg');

        $numPages = $imagick->getNumberImages();

        for ($i = 0; $i < $numPages; $i++) {
            $image = new Imagick();
            $image->setResolution(150, 150);
            // $image->setImageBackgroundColor(new ImagickPixel('white'));
            $image->setColorspace(Imagick::COLORSPACE_SRGB);
            $image->readImage($pdfPath . "[" . $i . "]");
            $geo = $image->getImageGeometry();
            $width = $geo['width'];
            $height = $geo['height'];

            // 76픽셀만큼 상하좌우를 crop
            $cropWidth = $width - 152;  // 좌우 각각 76픽셀
            $cropHeight = $height - 152;  // 상하 각각 76픽셀
            $image->cropImage($cropWidth, $cropHeight, 76, 76);

            // $image->trimImage(0);
            $image->setImageFormat('jpg');
            $image->writeImage($outputDir . "/page_" . ($i + 1) . ".jpg");
            $image->clear();

            // $this->progress = ($i + 1) / $numPages * 100;
            // $this->dispatch('progressUpdated');
        }

        $imagick->clear();
    }

    #[Computed()]
    public function showProgressBar(): bool
    {
        return $this->isUploading;
    }
}
