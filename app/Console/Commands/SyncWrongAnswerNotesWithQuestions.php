<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Question;

class SyncWrongAnswerNotesWithQuestions extends Command
{
    protected $signature = 'sync:wrong-answer-notes {--dry-run : Run without actually updating}';
    protected $description = 'Sync wrong_answer_notes with updated questions table (base64 to file links)';

    public function handle()
    {
        // 메모리 제한 늘리기
        ini_set('memory_limit', '2G');
        
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->info('Running in dry-run mode. No updates will be made.');
        }

        $this->info('Syncing wrong_answer_notes with updated questions table...');

        // base64가 포함된 wrong_answer_notes 개수 먼저 확인
        $totalCount = DB::table('wrong_answer_notes')
            ->where('question', 'LIKE', '%data:image/%base64,%')
            ->count();

        $this->info("Found {$totalCount} wrong_answer_notes with base64 images");

        if ($totalCount === 0) {
            $this->info('No wrong_answer_notes with base64 images found.');
            return Command::SUCCESS;
        }

        $updatedCount = 0;
        $errorCount = 0;
        $chunkSize = 50; // 작은 청크로 메모리 절약
        
        $bar = $this->output->createProgressBar($totalCount);
        $bar->start();

        // 청크 단위로 처리
        DB::table('wrong_answer_notes')
            ->where('question', 'LIKE', '%data:image/%base64,%')
            ->select('id', 'question')
            ->orderBy('id')
            ->chunk($chunkSize, function ($wrongAnswerNotes) use (&$updatedCount, &$errorCount, $bar, $dryRun) {
                foreach ($wrongAnswerNotes as $wrongAnswerNote) {
                    try {
                        $questionData = json_decode($wrongAnswerNote->question, true);
                        
                        if (!is_array($questionData)) {
                            $this->error("Invalid JSON in wrong_answer_note {$wrongAnswerNote->id}");
                            $errorCount++;
                            continue;
                        }

                        $updated = false;
                        
                        // 질문의 ID가 있으면 최신 데이터로 업데이트
                        if (isset($questionData['id'])) {
                            $questionId = $questionData['id'];
                            
                            // questions 테이블에서 최신 데이터 조회
                            $latestQuestion = Question::find($questionId);
                            
                            if ($latestQuestion) {
                                // content와 explanation만 업데이트 (base64 → 이미지 링크)
                                if (isset($questionData['content']) && $questionData['content'] !== $latestQuestion->content) {
                                    $questionData['content'] = $latestQuestion->content;
                                    $updated = true;
                                }
                                
                                if (isset($questionData['explanation']) && $questionData['explanation'] !== $latestQuestion->explanation) {
                                    $questionData['explanation'] = $latestQuestion->explanation;
                                    $updated = true;
                                }
                            }
                        }
                        
                        if ($updated && !$dryRun) {
                            // 업데이트된 JSON을 다시 저장
                            DB::table('wrong_answer_notes')
                                ->where('id', $wrongAnswerNote->id)
                                ->update(['question' => json_encode($questionData)]);
                        }
                        
                        if ($updated) {
                            $updatedCount++;
                        }

                    } catch (\Exception $e) {
                        $this->error("Error processing wrong_answer_note {$wrongAnswerNote->id}: " . $e->getMessage());
                        $errorCount++;
                    }
                    
                    $bar->advance();
                    
                    // 메모리 정리
                    unset($wrongAnswerNote);
                }
                
                // 청크 처리 후 가비지 컬렉션
                if (function_exists('gc_collect_cycles')) {
                    gc_collect_cycles();
                }
            });

        $bar->finish();
        $this->newLine();

        $this->info("Sync complete:");
        $this->info("- Wrong answer notes updated: {$updatedCount}");
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