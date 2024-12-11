<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TestSheet;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class UpdateTestSheetStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-test-sheet-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';


    public function handle()
    {
        $now = Carbon::now();

        // pending -> progress 상태 업데이트
        TestSheet::query()
            ->where('is_auto', true)
            ->where('status', 'pending')
            ->whereNotNull('start_date')
            ->where('start_date', '<=', $now)
            ->update(['status' => 'progress']);

        // progress -> completed 상태 업데이트
        // 완료 대상 시험지들 조회
        $testSheets = TestSheet::query()
            ->where('is_auto', true)
            ->where('status', 'progress')
            ->whereNotNull('end_date')
            ->where('end_date', '<=', $now)
            ->get();

        // 각 시험지별로 complete() 메서드 호출
        foreach ($testSheets as $testSheet) {
            try {
                $testSheet->complete();
                Log::info("TestSheet #{$testSheet->id} auto completed successfully");
            } catch (\Exception $e) {
                Log::error("Failed to auto complete TestSheet #{$testSheet->id}: " . $e->getMessage());
                // 선택적: 실패 처리 로직 추가
            }
        }
        $this->info('시험지 상태가 성공적으로 업데이트되었습니다.');
    }
}
