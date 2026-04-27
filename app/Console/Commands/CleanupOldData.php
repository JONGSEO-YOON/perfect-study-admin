<?php

namespace App\Console\Commands;

use App\Models\TestSheet;
use App\Models\TestSheetAnswer;
use App\Models\WrongAnswerNote;
use App\Models\WrongAnswerTestSheet;
use App\Models\WeeklyTestReport;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CleanupOldData extends Command
{
    /**
     * 4개월 이상 지난 학생 오답노트, 학생 성적표, 출제된 문제지 데이터를 정리.
     * 매일 새벽 4시에 자동 실행 (routes/console.php).
     *
     * 보존 기간: 4개월 (123일)
     * 대상:
     *   - test_sheets : status='completed' 이고 마감(end_date) 4개월 경과
     *   - test_sheet_answers : 위 시험지의 답안 (cascade)
     *   - wrong_answer_test_sheets : 위 시험지의 오답 시트
     *   - wrong_answer_notes : 4개월 이상 된 오답노트
     *   - weekly_test_reports : 4개월 이상 된 주간 리포트
     */
    protected $signature = 'cleanup:old-data {--days=123 : 보존 기간(일)} {--dry-run : 실제 삭제 없이 대상 카운트만 표시}';

    protected $description = '4개월 이상 된 시험지/오답노트/성적표 데이터를 정리';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $dryRun = (bool) $this->option('dry-run');
        $cutoff = now()->subDays($days);

        $this->info("=== 데이터 정리 시작 ===");
        $this->info("기준일: {$cutoff->format('Y-m-d H:i:s')} 이전 (보존 {$days}일)");
        if ($dryRun) {
            $this->warn("[DRY RUN] 실제로 삭제하지 않습니다.");
        }

        $stats = [];

        DB::beginTransaction();
        try {
            // 1. 마감된 시험지 (4개월 이상 경과)
            $oldTestSheetsQuery = TestSheet::query()
                ->withoutGlobalScopes()
                ->where('status', 'completed')
                ->where(function ($q) use ($cutoff) {
                    $q->where('end_date', '<', $cutoff)
                        ->orWhere(function ($qq) use ($cutoff) {
                            $qq->whereNull('end_date')->where('updated_at', '<', $cutoff);
                        });
                });

            $oldTestSheetIds = $oldTestSheetsQuery->pluck('id')->all();
            $stats['test_sheets'] = count($oldTestSheetIds);

            if (!empty($oldTestSheetIds) && !$dryRun) {
                // 답안 → 오답 시트 → 시험지 순으로 삭제
                $answersDeleted = TestSheetAnswer::whereIn('test_sheet_id', $oldTestSheetIds)->delete();
                $stats['test_sheet_answers'] = $answersDeleted;

                $wrongSheetsDeleted = WrongAnswerTestSheet::whereIn('original_test_sheet_id', $oldTestSheetIds)
                    ->orWhereIn('test_sheet_id', $oldTestSheetIds)
                    ->delete();
                $stats['wrong_answer_test_sheets'] = $wrongSheetsDeleted;

                TestSheet::whereIn('id', $oldTestSheetIds)->delete();
            } else {
                $stats['test_sheet_answers'] = TestSheetAnswer::whereIn('test_sheet_id', $oldTestSheetIds)->count();
                $stats['wrong_answer_test_sheets'] = WrongAnswerTestSheet::whereIn('original_test_sheet_id', $oldTestSheetIds)
                    ->orWhereIn('test_sheet_id', $oldTestSheetIds)
                    ->count();
            }

            // 2. 4개월 이상 된 오답노트
            if (class_exists(WrongAnswerNote::class)) {
                $oldNotesQuery = WrongAnswerNote::query()
                    ->withoutGlobalScopes()
                    ->where('created_at', '<', $cutoff);
                $stats['wrong_answer_notes'] = $oldNotesQuery->count();
                if (!$dryRun) {
                    $oldNotesQuery->delete();
                }
            }

            // 3. 4개월 이상 된 주간 리포트
            $oldReportsQuery = WeeklyTestReport::query()
                ->where('created_at', '<', $cutoff);
            $stats['weekly_test_reports'] = $oldReportsQuery->count();
            if (!$dryRun) {
                $oldReportsQuery->delete();
            }

            if ($dryRun) {
                DB::rollBack();
            } else {
                DB::commit();
            }

            // 결과 요약
            $this->info("=== 정리 결과 ===");
            $this->table(['항목', '건수'], collect($stats)->map(fn($v, $k) => [$k, number_format($v)])->values()->all());

            Log::info('CleanupOldData executed', [
                'cutoff' => $cutoff->toIso8601String(),
                'dry_run' => $dryRun,
                'stats' => $stats,
            ]);

            return self::SUCCESS;
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error("정리 중 오류: {$e->getMessage()}");
            Log::error('CleanupOldData failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return self::FAILURE;
        }
    }
}
