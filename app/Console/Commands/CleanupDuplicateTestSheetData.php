<?php

namespace App\Console\Commands;

use App\Models\TestSheet;
use App\Models\TestSheetAnswer;
use App\Models\WrongAnswerTestSheet;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanupDuplicateTestSheetData extends Command
{
    /**
     * submitAnswer의 updateOrCreate 버그로 인해 발생한 중복 데이터를 정리한다.
     *
     * 정리 대상:
     *   1. TestSheetAnswer: 동일 (test_sheet_id, user_id) 가 여러 건일 때 최신 1건만 유지
     *   2. WrongAnswerTestSheet: 동일 (original_test_sheet_id, user_id, retry_count) 가 여러 건일 때 최신 1건만 유지
     *   3. 위에서 버려진 WrongAnswerTestSheet 가 가리키던 (오답 유사 유형 / 오답 테스트) 시험지도 함께 삭제
     */
    protected $signature = 'cleanup:duplicate-testsheet-data {--dry-run : 실제 삭제 없이 대상 카운트만 표시} {--test-sheet= : 특정 원본 시험지 ID 만 처리}';

    protected $description = '중복 생성된 시험지 답안 / 오답 유사 시험지 데이터를 정리';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $singleId = $this->option('test-sheet');

        $this->info($dryRun ? '🔍 DRY-RUN 모드' : '🧹 실제 정리 모드');

        DB::transaction(function () use ($dryRun, $singleId) {
            // 1. TestSheetAnswer 중복 제거
            $answerQuery = TestSheetAnswer::query()
                ->select('test_sheet_id', 'user_id', DB::raw('COUNT(*) as cnt'))
                ->groupBy('test_sheet_id', 'user_id')
                ->having('cnt', '>', 1);

            if ($singleId) {
                $answerQuery->where('test_sheet_id', $singleId);
            }

            $duplicateAnswers = $answerQuery->get();
            $this->info("중복 TestSheetAnswer 그룹: {$duplicateAnswers->count()}");

            $deletedAnswerCount = 0;
            foreach ($duplicateAnswers as $group) {
                $rows = TestSheetAnswer::where('test_sheet_id', $group->test_sheet_id)
                    ->where('user_id', $group->user_id)
                    ->orderByDesc('id')
                    ->get();

                // 첫 번째(가장 최근)는 유지, 나머지 삭제
                $toDelete = $rows->skip(1);
                $deletedAnswerCount += $toDelete->count();

                if (!$dryRun) {
                    TestSheetAnswer::whereIn('id', $toDelete->pluck('id'))->delete();
                }
            }
            $this->info("삭제 대상 TestSheetAnswer: {$deletedAnswerCount}건");

            // 2. WrongAnswerTestSheet 중복 제거
            $watsQuery = WrongAnswerTestSheet::query()
                ->select('original_test_sheet_id', 'user_id', 'retry_count', DB::raw('COUNT(*) as cnt'))
                ->groupBy('original_test_sheet_id', 'user_id', 'retry_count')
                ->having('cnt', '>', 1);

            if ($singleId) {
                $watsQuery->where('original_test_sheet_id', $singleId);
            }

            $duplicateWats = $watsQuery->get();
            $this->info("중복 WrongAnswerTestSheet 그룹: {$duplicateWats->count()}");

            $deletedWatsCount = 0;
            $deletedTestSheetCount = 0;

            foreach ($duplicateWats as $group) {
                $rows = WrongAnswerTestSheet::where('original_test_sheet_id', $group->original_test_sheet_id)
                    ->where('user_id', $group->user_id)
                    ->where('retry_count', $group->retry_count)
                    ->orderByDesc('id')
                    ->get();

                // 첫 번째(가장 최근)는 유지, 나머지 삭제
                $toDelete = $rows->skip(1);

                foreach ($toDelete as $oldWats) {
                    $deletedWatsCount++;
                    $oldTestSheet = TestSheet::find($oldWats->test_sheet_id);
                    if ($oldTestSheet) {
                        $deletedTestSheetCount++;
                        if (!$dryRun) {
                            $oldTestSheet->delete();
                        }
                    }
                    if (!$dryRun) {
                        $oldWats->delete();
                    }
                }
            }
            $this->info("삭제 대상 WrongAnswerTestSheet: {$deletedWatsCount}건");
            $this->info("삭제 대상 오답 유사 시험지(중복 WAT): {$deletedTestSheetCount}건");

            // 3. 고아 오답 유사 유형 시험지 정리 (WAT가 더 이상 가리키지 않는 자동 생성 시험지)
            $orphanQuery = TestSheet::query()
                ->where(function ($q) {
                    $q->where('name', 'like', '%(오답 유사 유형)%')
                      ->orWhere('name', 'like', '%(오답 테스트)%');
                })
                ->whereNotIn('id', WrongAnswerTestSheet::query()->select('test_sheet_id'));

            $orphanTestSheets = $orphanQuery->get();
            $this->info("고아 오답 유사/오답 테스트 시험지: {$orphanTestSheets->count()}건");

            if (!$dryRun) {
                foreach ($orphanTestSheets as $orphan) {
                    $orphan->delete();
                }
            }

            if ($dryRun) {
                throw new \RuntimeException('DRY-RUN: 모든 변경을 롤백');
            }
        });

        if (!$dryRun) {
            $this->info('✅ 정리 완료. 영향받은 시험지에 대해 generateReport 를 다시 실행해 주세요.');
            $this->newLine();
            $this->line('예: php artisan tinker --execute="App\\Models\\TestSheet::find(<id>)->generateReport();"');
        }

        return 0;
    }
}
