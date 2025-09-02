<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Models\Question;

class ConvertBase64ToFiles extends Command
{
    protected $signature = 'convert:base64-to-files {--dry-run : Run without actually converting files}';
    protected $description = 'Convert base64 images to files and update database';

    public function handle()
    {
        // 메모리 제한 늘리기
        ini_set('memory_limit', '1G');
        
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->info('Running in dry-run mode. No files will be created or database updated.');
        }

        $this->info('Converting base64 images to files...');

        // 먼저 Base64 이미지가 포함된 질문들의 총 개수만 확인
        $totalQuestions = Question::where('content', 'LIKE', '%data:image/%base64,%')->count();
        $this->info("Found {$totalQuestions} questions with base64 images");

        if ($totalQuestions === 0) {
            $this->info('No base64 images found in questions.');
            return Command::SUCCESS;
        }

        $convertedCount = 0;
        $errorCount = 0;
        $chunkSize = 10; // 더 작은 청크 크기로 메모리 절약
        
        $bar = $this->output->createProgressBar($totalQuestions);
        $bar->start();

        // 청크 단위로 처리하여 메모리 사용량 줄이기
        Question::where('content', 'LIKE', '%data:image/%base64,%')
            ->select('id', 'content')
            ->chunk($chunkSize, function ($questions) use (&$convertedCount, &$errorCount, $bar, $dryRun) {
                foreach ($questions as $question) {
                    try {
                        $newContent = $this->processQuestionImages($question, $dryRun);
                        
                        if ($newContent !== $question->content && !$dryRun) {
                            // DB 업데이트를 위해 새로운 쿼리 실행 (메모리 효율적)
                            Question::where('id', $question->id)->update(['content' => $newContent]);
                        }
                        
                        $convertedCount++;
                    } catch (\Exception $e) {
                        $this->error("Error processing question {$question->id}: " . $e->getMessage());
                        $errorCount++;
                    }
                    
                    $bar->advance();
                    
                    // 메모리 정리
                    unset($question);
                }
                
                // 청크 처리 후 가비지 컬렉션
                if (function_exists('gc_collect_cycles')) {
                    gc_collect_cycles();
                }
            });

        $bar->finish();
        $this->newLine();

        $this->info("Conversion complete:");
        $this->info("- Questions processed: {$convertedCount}");
        $this->info("- Errors: {$errorCount}");
        $this->info("- Peak memory usage: " . $this->formatBytes(memory_get_peak_usage(true)));

        return Command::SUCCESS;
    }

    private function processQuestionImages(Question $question, bool $dryRun): string
    {
        $content = $question->content;
        
        // base64 이미지 패턴 찾기
        $pattern = '/(<img[^>]*src=")data:image\/([^;]+);base64,([^"]+)("[^>]*>)/';
        
        $content = preg_replace_callback($pattern, function ($matches) use ($question, $dryRun) {
            $fullMatch = $matches[0];
            $imgTagPrefix = $matches[1];
            $imageType = $matches[2]; // png, jpg, etc.
            $base64Data = $matches[3];
            $imgTagSuffix = $matches[4];
            
            try {
                // 파일 확장자 결정
                $extension = $this->getFileExtension($imageType);
                
                // 파일명 생성 (question ID + 랜덤 해시)
                $filename = 'questions/' . $question->id . '_' . uniqid() . '.' . $extension;
                
                if (!$dryRun) {
                    // Base64 디코드 (실제 저장할 때만)
                    $imageData = base64_decode($base64Data);
                    
                    if ($imageData === false) {
                        throw new \Exception('Failed to decode base64 data');
                    }
                    
                    // questions 디렉토리가 없으면 생성
                    if (!Storage::disk('public')->exists('questions')) {
                        Storage::disk('public')->makeDirectory('questions');
                    }
                    
                    // 파일 저장
                    Storage::disk('public')->put($filename, $imageData);
                    
                    // 메모리 정리
                    unset($imageData);
                }
                
                // 새로운 이미지 태그 생성
                $newSrc = Storage::url($filename);
                $newImgTag = $imgTagPrefix . $newSrc . $imgTagSuffix;
                
                // 메모리 정리
                unset($base64Data);
                
                return $newImgTag;
                
            } catch (\Exception $e) {
                $this->error("Failed to process image in question {$question->id}: " . $e->getMessage());
                return $fullMatch; // 원본 반환
            }
        }, $content);
        
        return $content;
    }

    private function getFileExtension(string $mimeType): string
    {
        $extensions = [
            'png' => 'png',
            'jpg' => 'jpg',
            'jpeg' => 'jpg',
            'gif' => 'gif',
            'webp' => 'webp',
            'bmp' => 'bmp',
            'svg+xml' => 'svg'
        ];
        
        return $extensions[$mimeType] ?? 'png';
    }

    private function formatBytes($size, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }
        
        return round($size, $precision) . ' ' . $units[$i];
    }
}