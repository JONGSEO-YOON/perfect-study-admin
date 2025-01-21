<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use \Imagick;
use \ImagickPixel;

class ConvertPdfToImagesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 60 * 60;

    public function __construct(
        private string $pdfPath,
        private string $outputDir,
        private string $attachmentName,
        private ?string $materialId,
        private ?bool $isPublic
    ) {}

    public function handle()
    {
        $imagick = new Imagick();
        $imagick->readImage($this->pdfPath);
        $imagick->setResolution(600, 600);
        $imagick->setImageFormat('jpg');

        $numPages = $imagick->getNumberImages();

        Cache::put("pdf_conversion_{$this->attachmentName}", [
            'progress' => 0,
            'currentPage' => 0,
            'totalPages' => $numPages,
            'material_id' => $this->materialId,
            'is_public' => $this->isPublic,
            'is_completed' => false
        ], now()->addHours(1));

        for ($i = 0; $i < $numPages; $i++) {
            $currentPage = $i + 1;

            // 진행상황 캐시에 저장
            Cache::put("pdf_conversion_{$this->attachmentName}", [
                'progress' => ($currentPage / $numPages) * 100,
                'currentPage' => $currentPage,
                'totalPages' => $numPages,
                'material_id' => $this->materialId,
                'is_public' => $this->isPublic,
                'is_completed' => false
            ], now()->addHours(1));

            $image = new Imagick();
            $image->setResolution(300, 300);
            $image->setColorspace(Imagick::COLORSPACE_SRGB);
            $image->readImage($this->pdfPath . "[" . $i . "]");
            $image->setImageAlphaChannel(Imagick::ALPHACHANNEL_REMOVE);
            $image->mergeImageLayers(Imagick::LAYERMETHOD_FLATTEN);
            $image->setImageBackgroundColor(new ImagickPixel('white'));

            $image->setImageFormat('jpg');
            $image->writeImage($this->outputDir . "/page_" . ($i + 1) . ".jpg");
            $image->clear();
        }

        $imagick->clear();

        // 작업 완료 표시
        Cache::put("pdf_conversion_{$this->attachmentName}", [
            'progress' => 100,
            'currentPage' => $numPages,
            'totalPages' => $numPages,
            'material_id' => $this->materialId,
            'is_public' => $this->isPublic,
            'is_completed' => true
        ], now()->addHours(1));
    }
}
