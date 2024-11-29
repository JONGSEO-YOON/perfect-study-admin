<?php

namespace App\Filament\Pages;

use App\Models\Question;
use App\Models\TempData;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class CreateTestSheets extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $slug = 'test-sheets/create/{id}';

    protected static string $view = 'filament.pages.create-test-sheets';

    protected static bool $shouldRegisterNavigation = false;

    protected ?string $maxContentWidth = '6xl';

    protected static ?string $title = '문제 등록';

    public $id;

    public $questions = null;

    public $summary = null;

    public function mount($id)
    {
        $query = TempData::findOrFail($id)?->value;
        // $questions = self::selectRandomQuestions($query);
        $this->questions = self::selectRandomQuestions($query);
        $this->summary = self::getDistributionSummary($this->questions);
        // dd($query, $summary, $questions);
        $this->id = $id;
    }

    public static function selectRandomQuestions(array $params): Collection
    {
        $totalQuestionCount = $params['question_count'];
        $questionTypeIds = $params['question_type_ids'];
        $isEvenDistribution = $params['is_even_distribution'];
        $result = collect();

        if ($isEvenDistribution) {
            // Even distribution case: 모든 레벨에서 동일한 수의 문제 추출
            $levels = $params['levels'];
            $questionsPerLevel = (int) floor($totalQuestionCount / count($levels));
            $remainingQuestions = $totalQuestionCount % count($levels);

            foreach ($levels as $levelIndex => $level) {
                // 이 레벨에서 가져올 총 문제 수
                $levelQuestionCount = $questionsPerLevel + ($levelIndex < $remainingQuestions ? 1 : 0);

                // 각 타입별로 가져올 문제 수 계산
                $questionsPerType = (int) floor($levelQuestionCount / count($questionTypeIds));
                $remainingTypeQuestions = $levelQuestionCount % count($questionTypeIds);

                foreach ($questionTypeIds as $typeIndex => $typeId) {
                    $typeQuestionCount = $questionsPerType + ($typeIndex < $remainingTypeQuestions ? 1 : 0);

                    if ($typeQuestionCount > 0) {
                        $questions = Question::where('question_type_id', $typeId)
                            ->where('level', $level)
                            ->with('questionType')
                            ->inRandomOrder()
                            ->take($typeQuestionCount)
                            ->get();

                        $result = $result->concat($questions);
                    }
                }
            }
        } else {
            // Weighted distribution case: 가중치에 따라 문제 추출
            $levelWeights = $params['level'];
            $totalWeight = array_sum($levelWeights);

            // 각 레벨별 문제 수 계산 (전체 문제 수에서 가중치 비율대로)
            $levelQuestionCounts = [];
            $assignedQuestions = 0;

            foreach ($levelWeights as $level => $weight) {
                $levelCount = (int) round($totalQuestionCount * ($weight / $totalWeight));
                $levelQuestionCounts[$level] = $levelCount;
                $assignedQuestions += $levelCount;
            }

            // 반올림으로 인한 차이 보정
            $diff = $totalQuestionCount - $assignedQuestions;
            if ($diff != 0) {
                $maxWeightLevel = array_keys($levelWeights, max($levelWeights))[0];
                $levelQuestionCounts[$maxWeightLevel] += $diff;
            }

            // 각 레벨별로 문제 추출
            foreach ($levelQuestionCounts as $level => $count) {
                if ($count > 0) {
                    // 이 레벨에서 각 타입별로 가져올 문제 수 계산
                    $questionsPerType = (int) floor($count / count($questionTypeIds));
                    $remainingTypeQuestions = $count % count($questionTypeIds);

                    foreach ($questionTypeIds as $typeIndex => $typeId) {
                        $typeQuestionCount = $questionsPerType + ($typeIndex < $remainingTypeQuestions ? 1 : 0);

                        if ($typeQuestionCount > 0) {
                            $questions = Question::where('question_type_id', $typeId)
                                ->with('questionType')
                                ->where('level', $level)
                                ->inRandomOrder()
                                ->take($typeQuestionCount)
                                ->get();

                            $result = $result->concat($questions);
                        }
                    }
                }
            }
        }

        return $result;
    }

    /**
     * 각 레벨과 타입별로 실제 추출된 문제 수를 계산
     */
    public static function getDistributionSummary(Collection $questions): array
    {
        $summary = [
            'by_answer_type' => [],
            'by_type' => [],
            'by_level' => [],
            'by_type_and_level' => []
        ];

        foreach ($questions as $question) {
            // 타입별 카운트
            if (!isset($summary['by_type'][$question->question_type_id])) {
                $summary['by_type'][$question->question_type_id] = 0;
            }
            $summary['by_type'][$question->question_type_id]++;

            // 레벨별 카운트
            if (!isset($summary['by_level'][$question->level])) {
                $summary['by_level'][$question->level] = 0;
            }
            $summary['by_level'][$question->level]++;

            // 정답 유형별 카운트
            if (!isset($summary['by_answer_type'][$question->answer_type])) {
                $summary['by_answer_type'][$question->answer_type] = 0;
            }
            $summary['by_answer_type'][$question->answer_type]++;

            // 타입과 레벨 조합별 카운트
            if (!isset($summary['by_type_and_level'][$question->question_type_id])) {
                $summary['by_type_and_level'][$question->question_type_id] = [];
            }
            if (!isset($summary['by_type_and_level'][$question->question_type_id][$question->level])) {
                $summary['by_type_and_level'][$question->question_type_id][$question->level] = 0;
            }
            $summary['by_type_and_level'][$question->question_type_id][$question->level]++;
        }

        return $summary;
    }
}
