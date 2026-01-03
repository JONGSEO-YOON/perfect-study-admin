<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Question;

class SyncTestSheetsWithQuestions extends Command
{
    protected $signature = 'sync:test-sheets-questions {--dry-run : Run without actually updating}';
    protected $description = 'Sync test_sheets questions JSON with updated questions table (base64 to file links)';

    public function handle()
    {
        // 메모리 제한 늘리기
        ini_set('memory_limit', '2G');
        
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->info('Running in dry-run mode. No updates will be made.');
        }

        $this->info('Syncing test_sheets questions with updated questions table...');

        // base64가 포함된 test_sheets 개수 먼저 확인
        $totalCount = DB::table('test_sheets')
            ->where('questions', 'LIKE', '%data:image/%base64,%')
            ->count();

        $this->info("Found {$totalCount} test_sheets with base64 images");

        if ($totalCount === 0) {
            $this->info('No test_sheets with base64 images found.');
            return Command::SUCCESS;
        }

        $updatedCount = 0;
        $errorCount = 0;
        $chunkSize = 10; // 작은 청크로 메모리 절약
        
        $bar = $this->output->createProgressBar($totalCount);
        $bar->start();

        // 청크 단위로 처리
        DB::table('test_sheets')
            ->where('questions', 'LIKE', '%data:image/%base64,%')
            ->select('id', 'questions')
            ->orderBy('id')
            ->chunk($chunkSize, function ($testSheets) use (&$updatedCount, &$errorCount, $bar, $dryRun) {
                foreach ($testSheets as $testSheet) {
            try {
                $questionsData = json_decode($testSheet->questions, true);
                
                if (!is_array($questionsData)) {
                    $this->error("Invalid JSON in test_sheet {$testSheet->id}");
                    $errorCount++;
                    continue;
                }

                $updated = false;
                
                // 각 질문에 대해 최신 데이터로 업데이트
                foreach ($questionsData as &$questionData) {
                    if (isset($questionData['id'])) {
                        $questionId = $questionData['id'];
                        
                        // questions 테이블에서 최신 데이터 조회
                        $latestQuestion = Question::find($questionId);
                        
                        if ($latestQuestion) {
                            // content와 explanation만 업데이트 (base64 → 이미지 링크)
                            if ($questionData['content'] !== $latestQuestion->content) {
                                $questionData['content'] = $latestQuestion->content;
                                $updated = true;
                            }
                            
                            if ($questionData['explanation'] !== $latestQuestion->explanation) {
                                $questionData['explanation'] = $latestQuestion->explanation;
                                $updated = true;
                            }
                        }
                    }
                }
                
                if ($updated && !$dryRun) {
                    // 업데이트된 JSON을 다시 저장
                    DB::table('test_sheets')
                        ->where('id', $testSheet->id)
                        ->update(['questions' => json_encode($questionsData)]);
                }
                
                if ($updated) {
                    $updatedCount++;
                }

            } catch (\Exception $e) {
                $this->error("Error processing test_sheet {$testSheet->id}: " . $e->getMessage());
                $errorCount++;
            }
            
                    $bar->advance();
                    
                    // 메모리 정리
                    unset($testSheet);
                }
                
                // 청크 처리 후 가비지 컬렉션
                if (function_exists('gc_collect_cycles')) {
                    gc_collect_cycles();
                }
            });

        $bar->finish();
        $this->newLine();

        $this->info("Sync complete:");
        $this->info("- Test sheets updated: {$updatedCount}");
        $this->info("- Errors: {$errorCount}");
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