<?php

namespace App\Console\Commands;

use App\Models\Question;
use App\Models\TestSheet;
use Illuminate\Console\Command;

class CleanupDeletedQuestionsInTestSheets extends Command
{
    /**
     * 시험지(test_sheets)의 questions JSON 에 남아 있는 "이미 삭제된 문제" 항목을 일괄 제거.
     *
     * 어드민에서 문제를 삭제하면 Question::deleted 이벤트가 시험지의 questions 에서 자동 제거하지만,
     * 과거 이벤트가 정상 작동하지 않았던 시기에 쌓인 stale 데이터를 한 번에 정리하기 위함.
     */
    protected $signature = 'cleanup:deleted-questions-in-test-sheets {--dry-run}';

    protected $description = '시험지에 남아 있는 이미 삭제된 문제 항목을 제거';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $this->info($dryRun ? '🔍 DRY-RUN' : '🧹 실제 정리');

        $totalAffected = 0;
        $totalRemoved = 0;

        TestSheet::withoutGlobalScopes()
            ->whereNotNull('questions')
            ->chunkById(50, function ($testSheets) use (&$totalAffected, &$totalRemoved, $dryRun) {
                foreach ($testSheets as $ts) {
                    $questions = $ts->questions ?? [];
                    if (empty($questions)) continue;

                    $ids = collect($questions)->pluck('id')->filter()->unique()->values()->all();
                    if (empty($ids)) continue;

                    $existingIds = Question::withoutGlobalScopes()
                        ->whereIn('id', $ids)
                        ->pluck('id')
                        ->all();

                    $filtered = array_values(array_filter($questions, function ($q) use ($existingIds) {
                        $qid = is_array($q) ? ($q['id'] ?? null) : null;
                        return $qid !== null && in_array($qid, $existingIds);
                    }));

                    if (count($filtered) !== count($questions)) {
                        $diff = count($questions) - count($filtered);
                        $totalAffected++;
                        $totalRemoved += $diff;
                        $this->line("- TestSheet#{$ts->id} ({$ts->name}): {$diff}건 제거");

                        if (!$dryRun) {
                            $ts->timestamps = false;
                            $ts->update(['questions' => $filtered]);
                            $ts->timestamps = true;
                        }
                    }
                }
            });

        $this->info("정리 대상 시험지: {$totalAffected}건, 제거된 문제: {$totalRemoved}건");
        return Command::SUCCESS;
    }
}
