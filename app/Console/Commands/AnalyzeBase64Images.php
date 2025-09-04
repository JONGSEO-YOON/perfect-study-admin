<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Question;

class AnalyzeBase64Images extends Command
{
    protected $signature = 'analyze:base64-images';
    protected $description = 'Analyze questions with base64 images in content';

    public function handle()
    {
        // 메모리 제한 늘리기
        ini_set('memory_limit', '1G');
        
        $this->info('Analyzing base64 images in questions...');

        // Base64 이미지가 포함된 질문들의 총 개수 확인
        $contentCount = Question::where('content', 'LIKE', '%data:image/%base64,%')->count();
        $explanationCount = Question::where('explanation', 'LIKE', '%data:image/%base64,%')->count();
        $totalQuestions = Question::where(function($query) {
            $query->where('content', 'LIKE', '%data:image/%base64,%')
                  ->orWhere('explanation', 'LIKE', '%data:image/%base64,%');
        })->count();
        
        $this->info("Found {$totalQuestions} questions with base64 images");
        $this->info("- Content column: {$contentCount}");
        $this->info("- Explanation column: {$explanationCount}");

        if ($totalQuestions === 0) {
            $this->info('No base64 images found in questions.');
            return Command::SUCCESS;
        }

        $totalSize = 0;
        $imageCount = 0;
        $maxImagesCount = 0;
        $questionWithMostImages = null;
        $chunkSize = 50; // 메모리 부족을 방지하기 위해 청크 크기 줄임
        
        $bar = $this->output->createProgressBar($totalQuestions);
        $bar->start();

        // 청크 단위로 처리하여 메모리 사용량 줄이기
        Question::where(function($query) {
                $query->where('content', 'LIKE', '%data:image/%base64,%')
                      ->orWhere('explanation', 'LIKE', '%data:image/%base64,%');
            })
            ->select('id', 'content', 'explanation')
            ->chunk($chunkSize, function ($questions) use (&$totalSize, &$imageCount, &$maxImagesCount, &$questionWithMostImages, $bar) {
                foreach ($questions as $question) {
                    try {
                        // content 열에서 base64 이미지 처리
                        $contentImages = 0;
                        if (!empty($question->content)) {
                            $contentImages = substr_count($question->content, 'data:image/');
                            preg_match_all('/data:image\/[^;]+;base64,([^"]+)/', $question->content, $matches);
                            
                            foreach ($matches[1] as $base64Data) {
                                $estimatedSize = (strlen($base64Data) * 3) / 4;
                                $totalSize += $estimatedSize;
                                $imageCount++;
                                unset($base64Data);
                            }
                            unset($matches);
                        }
                        
                        // explanation 열에서 base64 이미지 처리
                        $explanationImages = 0;
                        if (!empty($question->explanation)) {
                            $explanationImages = substr_count($question->explanation, 'data:image/');
                            preg_match_all('/data:image\/[^;]+;base64,([^"]+)/', $question->explanation, $matches);
                            
                            foreach ($matches[1] as $base64Data) {
                                $estimatedSize = (strlen($base64Data) * 3) / 4;
                                $totalSize += $estimatedSize;
                                $imageCount++;
                                unset($base64Data);
                            }
                            unset($matches);
                        }
                        
                        $totalImagesInQuestion = $contentImages + $explanationImages;
                        if ($totalImagesInQuestion > $maxImagesCount) {
                            $maxImagesCount = $totalImagesInQuestion;
                            $questionWithMostImages = $question->id;
                        }
                        
                    } catch (\Exception $e) {
                        $this->warn("Error processing question {$question->id}: " . $e->getMessage());
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

        $this->info("Analysis complete:");
        $this->info("- Questions with base64 images: {$totalQuestions}");
        $this->info("- Total base64 images: {$imageCount}");
        $this->info("- Estimated total size: " . $this->formatBytes($totalSize));
        $this->info("- Estimated average size per image: " . $this->formatBytes($totalSize / max($imageCount, 1)));

        if ($questionWithMostImages) {
            $this->info("- Question with most images: ID {$questionWithMostImages} ({$maxImagesCount} images)");
        }

        // 메모리 사용량 정보
        $this->info("- Peak memory usage: " . $this->formatBytes(memory_get_peak_usage(true)));

        return Command::SUCCESS;
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