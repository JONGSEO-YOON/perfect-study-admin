<?php

namespace App\Services;

/**
 * 한국 수능/모의고사 학년·년도·월별 사용 가능한 수학 선택과목을 매핑.
 *
 * 한국 수능 과목 체계 변천:
 * - ~2004년: 7차 (가/나형 + 선택과목 多)
 * - 2005~2008년 (수능 학년도): 가형(이과)/나형(문과). 가형엔 미적분/확률과 통계/이산수학 선택
 * - 2009~2013년: 가/나형. (대중적으로 문과/이과로 표기)
 * - 2014~2016년: A형(쉬운)/B형(어려운)
 * - 2017~2020년: 가형(이과)/나형(문과)
 * - 2021학년도 (2020년 시행 모의고사): 가형/나형
 * - 2022~ (2022학년도 수능부터): 통합형. 공통수학 + 선택 1택 (확률과 통계 / 미적분 / 기하)
 */
class ExamSubjectResolver
{
    /**
     * 학년·년도(·월)별 사용 가능한 수학 과목 옵션 반환
     *
     * @param string $grade 예: "고1", "고2", "고3"
     * @param int $year 시험 년도 (예: 2007)
     * @param int|null $month 월 (보통 6, 9, 11)
     * @return array<int, array{value:string,label:string}>
     */
    public static function getSubjects(string $grade, int $year, ?int $month = null): array
    {
        // 고1, 고2 모의고사: 학년에 맞는 단일 진도라서 별도 선택과목 없음
        if (in_array($grade, ['고1', '고2'])) {
            return self::asGridItems(self::getNonChoiceSubjects($grade, $year));
        }

        // 고3 + 재수: 시기별 분기
        if (in_array($grade, ['고3', '재수'])) {
            return self::asGridItems(self::getGoSamSubjects($year));
        }

        return [];
    }

    /**
     * 고3 (수능 응시 학년도 기준) 시기별 선택과목
     */
    protected static function getGoSamSubjects(int $year): array
    {
        // 2022학년도 수능부터: 공통 + 선택과목 (확률과 통계/미적분/기하)
        if ($year >= 2022) {
            return ['확률과 통계', '미적분', '기하'];
        }

        // 2017~2021: 가형(이과)/나형(문과)
        if ($year >= 2017) {
            return ['가형(이과)', '나형(문과)'];
        }

        // 2014~2016: A형/B형
        if ($year >= 2014) {
            return ['A형', 'B형'];
        }

        // 2009~2013: 가/나형 → 문과/이과 표기
        if ($year >= 2009) {
            return ['문과', '이과'];
        }

        // 2005~2008: 가/나형 + 가형 선택과목 (확률과 통계/미분과 적분/이산수학)
        if ($year >= 2005) {
            return ['문과', '이과', '확률과 통계', '미분과 적분', '이산수학'];
        }

        // ~2004: 7차 이전 (구체 분류 미정 — 통합 표기)
        return ['공통', '인문', '자연'];
    }

    /**
     * 고1, 고2 모의고사 — 단일 진도이므로 빈 배열 반환 (선택과목 없음)
     * 단, "전체" 의미로 하나만 선택할 수 있게 단일 옵션 제공할 수도 있음.
     */
    protected static function getNonChoiceSubjects(string $grade, int $year): array
    {
        // 학년 진도 기반 단일 표기 (필요 시 확장)
        return [];
    }

    /**
     * ['확률과 통계', '미적분'] → [['value'=>'확률과 통계','label'=>'확률과 통계'], ...]
     */
    protected static function asGridItems(array $subjects): array
    {
        return array_map(
            fn($s) => ['value' => $s, 'label' => $s],
            $subjects
        );
    }

    /**
     * 여러 (grade, year, month) 조합에서 사용 가능한 과목들의 합집합 반환
     * 모달 form에서 사용자가 학년·년도·월을 복수 선택했을 때, 각 조합의 과목을 합쳐서 보여줌
     *
     * @param array $grades ["고3", "고2"]
     * @param array $years [2007, 2011, 2013]
     * @param array $months [6, 9]
     * @return array<int, array{value:string,label:string}>
     */
    public static function getSubjectsForCombinations(array $grades, array $years, array $months = []): array
    {
        if (empty($grades) || empty($years)) return [];

        $combined = [];
        foreach ($grades as $g) {
            foreach ($years as $y) {
                if (!empty($months)) {
                    foreach ($months as $m) {
                        foreach (self::getSubjects((string)$g, (int)$y, (int)$m) as $item) {
                            $combined[$item['value']] = $item;
                        }
                    }
                } else {
                    foreach (self::getSubjects((string)$g, (int)$y) as $item) {
                        $combined[$item['value']] = $item;
                    }
                }
            }
        }

        return array_values($combined);
    }

    /**
     * 학년·년도 조합별 과목을 (예: "고3 | 2007년 6월" 헤더 + 과목 리스트) 그룹화하여 반환
     * 사용자가 보여준 이미지처럼 회차별로 분리 표시할 때 사용
     *
     * @return array<string, array{header:string, subjects:array<int,string>}>
     */
    public static function getGroupedSubjects(array $grades, array $years, array $months = []): array
    {
        $groups = [];
        $monthsToUse = !empty($months) ? $months : [null];

        foreach ($grades as $g) {
            foreach ($years as $y) {
                foreach ($monthsToUse as $m) {
                    $subjects = array_column(self::getSubjects((string)$g, (int)$y, $m ? (int)$m : null), 'value');
                    if (empty($subjects)) continue;

                    $header = "{$g} | {$y}년" . ($m ? " {$m}월" : '');
                    $key = "{$g}_{$y}_" . ($m ?? 'all');
                    $groups[$key] = [
                        'header' => $header,
                        'grade' => (string)$g,
                        'year' => (int)$y,
                        'month' => $m ? (int)$m : null,
                        'subjects' => $subjects,
                    ];
                }
            }
        }

        return $groups;
    }
}
