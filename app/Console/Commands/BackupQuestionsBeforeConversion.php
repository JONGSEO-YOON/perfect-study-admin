<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Question;

class BackupQuestionsBeforeConversion extends Command
{
    protected $signature = 'backup:questions-before-conversion';
    protected $description = 'Backup questions with base64 images before conversion';

    public function handle()
    {
        // 메모리 제한 늘리기
        ini_set('memory_limit', '1G');
        
        $this->info('Creating backup of questions with base64 images...');

        // 먼저 Base64 이미지가 포함된 질문들의 총 개수만 확인
        $totalQuestions = Question::where('content', 'LIKE', '%data:image/%base64,%')->count();
        $this->info("Found {$totalQuestions} questions with base64 images");

        if ($totalQuestions === 0) {
            $this->info('No base64 images found in questions. No backup needed.');
            return Command::SUCCESS;
        }

        // 백업 파일 생성
        $timestamp = now()->format('Y-m-d_H-i-s');
        $backupFilename = "question_backup_base64_{$timestamp}.json";
        
        // 백업 테이블 생성
        $this->createBackupTable($timestamp, $totalQuestions);

        // JSON 백업도 생성 (청크 단위로)
        $this->createJsonBackup($backupFilename, $totalQuestions);

        return Command::SUCCESS;
    }

    private function createBackupTable($timestamp, $totalQuestions)
    {
        $tableName = "questions_backup_" . str_replace('-', '_', $timestamp);
        
        // 백업 테이블 생성 (questions 테이블 구조 복사)
        DB::statement("CREATE TABLE {$tableName} AS SELECT * FROM questions WHERE 1=0");
        
        $this->info("Creating database backup table: {$tableName}");
        
        $bar = $this->output->createProgressBar($totalQuestions);
        $bar->start();
        
        // 청크 단위로 데이터 삽입
        Question::where('content', 'LIKE', '%data:image/%base64,%')
            ->select('id', 'content', 'created_at', 'updated_at', 'user_id', 'question_type_id', 'material_id', 'parent_question_id', 'seq', 'title', 'difficulty', 'tags', 'metadata', 'is_public')
            ->chunk(50, function ($questions) use ($tableName, $bar) {
                $insertData = [];
                
                foreach ($questions as $question) {
                    $insertData[] = [
                        'id' => $question->id,
                        'content' => $question->content,
                        'created_at' => $question->created_at,
                        'updated_at' => $question->updated_at,
                        'user_id' => $question->user_id,
                        'question_type_id' => $question->question_type_id,
                        'material_id' => $question->material_id,
                        'parent_question_id' => $question->parent_question_id,
                        'seq' => $question->seq,
                        'title' => $question->title,
                        'difficulty' => $question->difficulty,
                        'tags' => $question->tags,
                        'metadata' => $question->metadata,
                        'is_public' => $question->is_public
                    ];
                    $bar->advance();
                }
                
                if (!empty($insertData)) {
                    DB::table($tableName)->insert($insertData);
                }
                
                // 메모리 정리
                unset($insertData);
                
                if (function_exists('gc_collect_cycles')) {
                    gc_collect_cycles();
                }
            });
        
        $bar->finish();
        $this->newLine();
        $this->info("Database backup table created: {$tableName}");
    }

    private function createJsonBackup($backupFilename, $totalQuestions)
    {
        $this->info("Creating JSON backup file...");
        
        // 백업 디렉토리 생성
        if (!Storage::disk('local')->exists('backups')) {
            Storage::disk('local')->makeDirectory('backups');
        }
        
        $backupData = [
            'backup_date' => now()->toISOString(),
            'total_questions' => $totalQuestions,
            'questions' => []
        ];
        
        $bar = $this->output->createProgressBar($totalQuestions);
        $bar->start();
        
        // 청크 단위로 JSON 백업 생성
        Question::where('content', 'LIKE', '%data:image/%base64,%')
            ->select('id', 'content', 'created_at', 'updated_at')
            ->chunk(50, function ($questions) use (&$backupData, $bar) {
                foreach ($questions as $question) {
                    $backupData['questions'][] = [
                        'id' => $question->id,
                        'content' => $question->content,
                        'created_at' => $question->created_at,
                        'updated_at' => $question->updated_at
                    ];
                    
                    $bar->advance();
                }
                
                if (function_exists('gc_collect_cycles')) {
                    gc_collect_cycles();
                }
            });
        
        $bar->finish();
        $this->newLine();
        
        // JSON 파일 저장
        Storage::disk('local')->put("backups/{$backupFilename}", json_encode($backupData, JSON_PRETTY_PRINT));
        
        $this->info("JSON backup created: storage/app/backups/{$backupFilename}");
        $this->info("Backup size: " . $this->formatBytes(Storage::disk('local')->size("backups/{$backupFilename}")));
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