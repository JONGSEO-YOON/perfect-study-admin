<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TestSheet;
use Carbon\Carbon;

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
        TestSheet::query()
            ->where('is_auto', true)
            ->where('status', 'progress')
            ->whereNotNull('end_date')
            ->where('end_date', '<=', $now)
            ->update(['status' => 'completed']);

        $this->info('시험지 상태가 성공적으로 업데이트되었습니다.');
    }
}
