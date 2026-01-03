<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class MigrateBase64Images extends Command
{
    protected $signature = 'migrate:base64-images 
                           {--skip-backup : Skip backup creation}
                           {--dry-run : Run without actually converting files}
                           {--force : Skip confirmation prompts}';
    protected $description = 'Complete migration of base64 images to files';

    public function handle()
    {
        $this->info('🚀 Starting Base64 Image Migration Process');
        $this->newLine();

        // 1. 분석 단계
        $this->info('📊 Step 1: Analyzing base64 images...');
        Artisan::call('analyze:base64-images');
        $this->info(Artisan::output());

        // 2. 백업 단계 (선택사항)
        if (!$this->option('skip-backup')) {
            $this->info('💾 Step 2: Creating backup...');
            Artisan::call('backup:questions-before-conversion');
            $this->info(Artisan::output());
        } else {
            $this->warn('⚠️  Skipping backup creation as requested.');
        }

        // 3. 확인 단계
        if (!$this->option('force') && !$this->option('dry-run')) {
            $confirmed = $this->confirm('Do you want to proceed with the conversion? This will modify your database.');
            if (!$confirmed) {
                $this->info('❌ Migration cancelled by user.');
                return Command::SUCCESS;
            }
        }

        // 4. 변환 단계
        $this->info('🔄 Step 3: Converting base64 images to files...');
        $options = [];
        if ($this->option('dry-run')) {
            $options['--dry-run'] = true;
        }
        
        Artisan::call('convert:base64-to-files', $options);
        $this->info(Artisan::output());

        // 5. 완료 메시지
        $this->newLine();
        if ($this->option('dry-run')) {
            $this->info('✅ Dry run completed successfully!');
            $this->info('💡 Run without --dry-run to perform actual conversion.');
        } else {
            $this->info('✅ Base64 image migration completed successfully!');
            $this->info('💡 Your database size should now be significantly smaller.');
            
            // 후속 권장사항
            $this->newLine();
            $this->info('📝 Recommended next steps:');
            $this->info('   1. Test your application thoroughly');
            $this->info('   2. Monitor database performance');
            $this->info('   3. Consider running OPTIMIZE TABLE on the questions table');
            $this->info('   4. Update your backup strategies to include uploaded files');
        }

        return Command::SUCCESS;
    }
}