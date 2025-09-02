<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SimpleBackupQuestions extends Command
{
    protected $signature = 'backup:questions-simple';
    protected $description = 'Create a simple backup of questions with base64 images using SQL';

    public function handle()
    {
        $this->info('Creating simple SQL backup of questions with base64 images...');

        // 백업 테이블명 생성
        $timestamp = now()->format('Y_m_d_H_i_s');
        $tableName = "questions_backup_{$timestamp}";

        try {
            // SQL을 사용해서 직접 백업 테이블 생성
            $this->info("Creating backup table: {$tableName}");
            
            $sql = "CREATE TABLE {$tableName} AS 
                    SELECT * FROM questions 
                    WHERE content LIKE '%data:image/%base64,%'";
            
            DB::statement($sql);
            
            // 백업된 레코드 수 확인
            $backupCount = DB::table($tableName)->count();
            
            $this->info("✅ Backup completed successfully!");
            $this->info("📋 Table name: {$tableName}");
            $this->info("📊 Records backed up: {$backupCount}");
            
            // 복원 방법 안내
            $this->newLine();
            $this->info("🔄 To restore if needed:");
            $this->info("   UPDATE questions q1 ");
            $this->info("   JOIN {$tableName} q2 ON q1.id = q2.id ");
            $this->info("   SET q1.content = q2.content;");
            
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("❌ Backup failed: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}