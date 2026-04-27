<?php

namespace App\Console\Commands;

use App\Models\TestSheet;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RegenerateTestReports extends Command
{
    /**
     * 기존 시험지에 대해 generateReport / generateWeeklyReport 재실행.
     *
     * 사용처:
     *  - getTargetStudents 버그 수정 후 기존 시험지의 report 재생성
     *  - 학생별 즉시 노출 / 제출현황 / 주간학습표가 안 뜨는 시험지 복구
     *
     * 옵션:
     *  --status=progress|completed|both : 대상 상태 (기본 both)
     *  --since=2026-01-01              : 시작일 이후만 (기본: 4개월 전)
     *  --id=N                          : 단일 시험지만
     */
    protected $signature = 'reports:regenerate
        {--status=both}
        {--since=}
        {--id=}';

    protected $description = '기존 시험지의 report/weekly_report 재생성';

    public function handle(): int
    {
        $status = $this->option('status');
        $since = $this->option('since') ?: now()->subMonths(4)->format('Y-m-d');
        $id = $this->option('id');

        $query = TestSheet::query()->withoutGlobalScopes();

        if ($id) {
            $query->where('id', (int) $id);
        } else {
            if ($status === 'progress') {
                $query->where('status', 'progress');
            } elseif ($status === 'completed') {
                $query->where('status', 'completed');
            } else {
                $query->whereIn('status', ['progress', 'completed']);
            }
            $query->where('start_date', '>=', $since);
        }

        // original 시험지만 (오답 시트는 자기 generateReport 트리거 안함)
        $query->whereDoesntHave('wrongAnswerTestSheets');

        $total = $query->count();
        $this->info("대상 시험지: {$total}개 (status={$status}, since={$since})");

        if ($total === 0) return self::SUCCESS;

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $okCount = 0;
        $errCount = 0;
        $query->orderBy('id', 'desc')->chunk(50, function ($sheets) use (&$okCount, &$errCount, $bar) {
            foreach ($sheets as $sheet) {
                try {
                    $sheet->generateReport();
                    $sheet->generateWeeklyReport();
                    if (in_array('숙제', (array) ($sheet->tags ?? []))) {
                        $sheet->generateHomeworkWeeklyReport();
                    }
                    $okCount++;
                } catch (\Throwable $e) {
                    $errCount++;
                    $this->newLine();
                    $this->error("시험지 #{$sheet->id} 실패: {$e->getMessage()}");
                }
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine();
        $this->info("성공 {$okCount} / 실패 {$errCount}");

        return self::SUCCESS;
    }
}
