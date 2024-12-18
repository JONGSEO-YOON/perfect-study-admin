<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Question;
use App\Models\QuestionCategory;
use Illuminate\Support\Facades\DB;

class QuestionCategorySeeder extends Seeder
{
    protected $categories = [];

    protected static function generateElementaryCategories()
    {
        $categories = [];
        for ($grade = 1; $grade <= 6; $grade++) {
            for ($semester = 1; $semester <= 2; $semester++) {
                $categories[] = [
                    'name' => "초 {$grade}-{$semester}",
                    'type' => 'scope',
                    'depth' => 1,
                    'order' => ($grade - 1) * 2 + $semester
                ];
            }
        }
        return $categories;
    }

    protected static function generateMiddleSchoolCategories()
    {
        $categories = [];
        for ($grade = 1; $grade <= 3; $grade++) {
            for ($semester = 1; $semester <= 2; $semester++) {
                $children = [];
                // 중1-1일 경우에만 소인수분해 단원 추가
                if ($grade === 1 && $semester === 1) {
                    $children = [
                        [
                            'name' => '소인수분해',
                            'type' => 'scope',
                            'depth' => 2,
                            'order' => 1,
                            'children' => [
                                [
                                    'name' => '소인수분해',
                                    'type' => 'scope',
                                    'depth' => 3,
                                    'order' => 1,
                                    'children' => [
                                        [
                                            'name' => '약수와 배수의 성질',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 1,
                                        ],
                                        [
                                            'name' => '소수와 합성수',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 2,
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ];
                }

                $categories[] = [
                    'name' => "중 {$grade}-{$semester}",
                    'type' => 'scope',
                    'depth' => 1,
                    'order' => ($grade - 1) * 2 + $semester,
                    'children' => $children
                ];
            }
        }
        return $categories;
    }

    protected static function generateHighSchool3Categories()
    {
        return [
            [
                'name' => '수학 1',
                'type' => 'scope',
                'depth' => 1,
                'order' => 3,
                'children' => [
                    [
                        'name' => '지수와로그',
                        'type' => 'scope',
                        'depth' => 2,
                        'order' => 1,
                        'children' => [
                            [
                                'name' => '지수와 로그의 단순계산',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 1,
                            ],
                            [
                                'name' => '지수 법칙의 연산',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 2,
                            ],
                            [
                                'name' => '제곱근',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 3,
                            ],
                            [
                                'name' => '자연수,정수,유리수가 될 조건',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 4,
                            ],
                            [
                                'name' => '로그 계산의 활용',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 5,
                            ],
                            [
                                'name' => '식을 이용한 지수, 로그의 풀이',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 6,
                            ],
                            [
                                'name' => '지수의 실생활의 활용',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 7,
                            ],
                        ]
                    ],
                    [
                        'name' => '지수함수,로그함수',
                        'type' => 'scope',
                        'depth' => 2,
                        'order' => 2,
                        'children' => [
                            [
                                'name' => '지수,로그함수 그래프 정의',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 1,
                            ],
                            [
                                'name' => '지수,로그함수 그래프 이용(1)-교점이용',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 2,
                            ],
                            [
                                'name' => '지수,로그함수 그래프 이용(2)-직선, 역함수, 평행이동 이용',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 3,
                            ],
                            [
                                'name' => '지수,로그함수 그래프 이용(3)-최대,최소',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 4,
                            ],
                            [
                                'name' => '지수,로그함수 그래프의 활용-길이',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 5,
                            ],
                            [
                                'name' => '지수,로그함수 그래프의 활용-넓이',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 6,
                            ],
                            [
                                'name' => '지수,로그함수의 그래프 이용하기-점의 개수 구하기',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 7,
                            ],
                            [
                                'name' => '지수, 로그 방정식',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 8,
                            ],
                            [
                                'name' => '지수, 로그 부등식',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 9,
                            ],
                            [
                                'name' => '지수, 로그함수의 대소관계',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 10,
                            ],
                            [
                                'name' => '지수, 로그함수의 대소관계-그래프이용',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 11,
                            ],
                        ]
                    ],
                    [
                        'name' => '삼각함수',
                        'type' => 'scope',
                        'depth' => 2,
                        'order' => 3,
                        'children' => [
                            [
                                'name' => '삼각비를 이용한 단순계산',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 1,
                            ],
                            [
                                'name' => '도형을 이용한 삼각비 계산',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 2,
                            ],
                            [
                                'name' => '부채꼴의 호의 길이, 넓이',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 3,
                            ],
                            [
                                'name' => '삼각함수 그래프(기본)',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 4,
                            ],
                            [
                                'name' => '삼각함수의 최대, 최소',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 5,
                            ],
                            [
                                'name' => '삼각함수의 방정식(특수각)',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 6,
                            ],
                            [
                                'name' => '삼각함수의 방정식(특수각이 아닌경우)',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 7,
                            ],
                            [
                                'name' => '삼각함수 그래프의 교점의 개수',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 8,
                            ],
                            [
                                'name' => '삼각함수의 부등식',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 9,
                            ],
                            [
                                'name' => '삼각함수 그래프의 활용',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 10,
                            ],
                            [
                                'name' => '사인법칙, 코사인법칙',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 11,
                            ],
                            [
                                'name' => '삼각함수 넓이의 활용',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 12,
                            ],
                        ]
                    ],
                    [
                        'name' => '수열',
                        'type' => 'scope',
                        'depth' => 2,
                        'order' => 4,
                        'children' => [
                            [
                                'name' => '등차,등비의 일반항 이용',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 1,
                            ],
                            [
                                'name' => '등차,등비의 합',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 2,
                            ],
                            [
                                'name' => '시그마 성질 및 단순계산',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 3,
                            ],
                            [
                                'name' => '합과 일반항의 관계',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 4,
                            ],
                            [
                                'name' => '시그마의 여러가지 계산',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 5,
                            ],
                            [
                                'name' => '등차,등비 및 시그마의 활용',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 6,
                            ],
                            [
                                'name' => '점화식',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 7,
                            ],
                            [
                                'name' => '수학적귀납법',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 8,
                            ],
                            [
                                'name' => '좌표를 이용한 수열 구하기',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 9,
                            ],
                            [
                                'name' => '도형을 이용한 수열 구하기',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 10,
                            ],
                        ]
                    ],
                ]
            ],
            [
                'name' => '수학 2',
                'type' => 'scope',
                'depth' => 1,
                'order' => 4,
                'children' => [
                    [
                        'name' => '다항함수의극한과연속',
                        'type' => 'scope',
                        'depth' => 2,
                        'order' => 1,
                        'children' => [
                            [
                                'name' => '극한 단순 계산 문제',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 1,
                            ],
                            [
                                'name' => '극한의 변형문제',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 2,
                            ],
                            [
                                'name' => '극한의 미정계수 구하기',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 3,
                            ],
                            [
                                'name' => '함수값을 이용한 극한값 계산',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 4,
                            ],
                            [
                                'name' => '샌드위치 정리',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 5,
                            ],
                            [
                                'name' => '극한의 활용',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 6,
                            ],
                            [
                                'name' => '연속일 조건',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 7,
                            ],
                            [
                                'name' => '두 함수의 곱이 연속일 조건',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 8,
                            ],
                            [
                                'name' => '합성함수의 연속',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 9,
                            ],
                            [
                                'name' => '연속과 불연속의 활용',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 10,
                            ],
                            [
                                'name' => '함수의 극한과 연속 합답형',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 11,
                            ],
                            [
                                'name' => '그래프를 이용한 함수의 극한',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 12,
                            ],
                            [
                                'name' => '그래프를 이용한 함수의 연속',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 13,
                            ],
                            [
                                'name' => '그래프를 이용한 함수의 극한과 연속(합성 함수)',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 14,
                            ],
                            [
                                'name' => '사잇값 정리',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 15,
                            ],
                        ]
                    ],
                    [
                        'name' => '다항함수 미분',
                        'type' => 'scope',
                        'depth' => 2,
                        'order' => 2,
                        'children' => [
                            [
                                'name' => '평균변화율, 미분계수 이용',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 1,
                            ],
                            [
                                'name' => '미분법 공식을 이용한 단순계산',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 2,
                            ],
                            [
                                'name' => '미분 가능조건',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 3,
                            ],
                        ]
                    ],
                    [
                        'name' => '다항함수 미분(도함수 활용1)',
                        'type' => 'scope',
                        'depth' => 2,
                        'order' => 3,
                        'children' => [
                            [
                                'name' => '접선의 방정식',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 1,
                            ],
                            [
                                'name' => '접선의 방정식의 활용',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 2,
                            ],
                            [
                                'name' => '증가와 감소',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 3,
                            ],
                            [
                                'name' => '극대와 극소',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 4,
                            ],
                            [
                                'name' => '그래프 개형을 이용한 극대, 극소',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 5,
                            ],
                        ]
                    ],
                    [
                        'name' => '다항함수 미분(도함수 활용2)',
                        'type' => 'scope',
                        'depth' => 2,
                        'order' => 4,
                        'children' => [
                            [
                                'name' => '그래프의 최대, 최소',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 1,
                            ],
                            [
                                'name' => '그래프의 최대, 최소(범위가 문자)',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 2,
                            ],
                            [
                                'name' => '그래프(절댓값을 포함한 미분가능)',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 3,
                            ],
                            [
                                'name' => '그래프의 근의 개수',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 4,
                            ],
                            [
                                'name' => '범위가 쪼개어져 있는 그래프의 근의개수',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 5,
                            ],
                            [
                                'name' => '그래프 개형을 이용한 활용문제',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 6,
                            ],
                            [
                                'name' => '그래프 개형을 이용한 미분가능',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 7,
                            ],
                            [
                                'name' => '조건을 이용한 그래프의 유추',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 8,
                            ],
                            [
                                'name' => '거리,속도, 가속도',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 9,
                            ],
                            [
                                'name' => '미분의 활용',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 10,
                            ],
                            [
                                'name' => '미분의 합답형',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 11,
                            ],
                            [
                                'name' => '조건을 이용한 미분의 합답형',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 12,
                            ],
                        ]
                    ],
                    [
                        'name' => '다항함수 적분(도함수 활용2)',
                        'type' => 'scope',
                        'depth' => 2,
                        'order' => 5,
                        'children' => [
                            [
                                'name' => '단순적분계산',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 1,
                            ],
                            [
                                'name' => '정적분의 계산',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 2,
                            ],
                            [
                                'name' => '적분과 미분의 계산 활용',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 3,
                            ],
                            [
                                'name' => '넓이 및 부피',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 4,
                            ],
                            [
                                'name' => '평행이동 및 주기, 대칭성을 이용한 적분값 계산',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 5,
                            ],
                            [
                                'name' => '조건을 이용한 적분값 계산',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 6,
                            ],
                            [
                                'name' => '합답형',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 7,
                            ],
                            [
                                'name' => '그래프 개형을 이용한 적분값 계산',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 8,
                            ],
                            [
                                'name' => '거리와 속도',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 9,
                            ],
                        ]
                    ]
                ]
            ],
            [
                'name' => '확률과통계',
                'type' => 'scope',
                'depth' => 1,
                'order' => 5,
                'children' => [
                    [
                        'name' => '경우의수',
                        'type' => 'scope',
                        'depth' => 2,
                        'order' => 1,
                        'children' => [
                            [
                                'name' => '원순열',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 1,
                            ],
                            [
                                'name' => '중복순열',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 2,
                            ],
                            [
                                'name' => '같은것을 포함한 순열(1) - 줄세우기',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 3,
                            ],
                            [
                                'name' => '같은것을 포함한 순열(2) - 일정한규칙',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 4,
                            ],
                            [
                                'name' => '같은것을 포함한 순열(3) - 최단경로',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 5,
                            ],
                            [
                                'name' => '같은것을 포함한 순열(4) - 경로찾기',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 6,
                            ],
                            [
                                'name' => '중복조합(1) – 방정식',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 7,
                            ],
                            [
                                'name' => '중복조합(2) – 식의 활용',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 8,
                            ],
                            [
                                'name' => '중복조합(3) - 활용문제',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 9,
                            ],
                            [
                                'name' => '함수문제',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 10,
                            ],
                            [
                                'name' => '이항정리(1) – 계수구하기',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 11,
                            ],
                            [
                                'name' => '이항정리(2) - 이항정리의 성질',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 12,
                            ],
                            [
                                'name' => '수학적귀납법(경우의수)',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 13,
                            ],
                        ]
                    ],
                    [
                        'name' => '확률',
                        'type' => 'scope',
                        'depth' => 2,
                        'order' => 2,
                        'children' => [
                            [
                                'name' => '확률 단순계산(1)',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 1,
                            ],
                            [
                                'name' => '확률 단순계산(2) - 조건부확률',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 2,
                            ],
                            [
                                'name' => '확률 단순계산(3) - 독립사건',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 3,
                            ],
                            [
                                'name' => '수학적 확률',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 4,
                            ],
                            [
                                'name' => '조건부 확률',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 5,
                            ],
                            [
                                'name' => '독립시행',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 6,
                            ],
                            [
                                'name' => '독립사건이 될 조건',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 7,
                            ],
                        ]
                    ],
                    [
                        'name' => '통계',
                        'type' => 'scope',
                        'depth' => 2,
                        'order' => 3,
                        'children' => [
                            [
                                'name' => '확률분포',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 1,
                            ],
                            [
                                'name' => '이항분포',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 2,
                            ],
                            [
                                'name' => '확률밀도함수',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 3,
                            ],
                            [
                                'name' => '정규분포',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 4,
                            ],
                            [
                                'name' => '표준정규분포',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 5,
                            ],
                            [
                                'name' => '이항분포와 정규분포',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 6,
                            ],
                            [
                                'name' => '통계적추정(1) - 모평균의 신뢰구간',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 7,
                            ],
                            [
                                'name' => '통계적추정(2) - 계산 및 성질',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 8,
                            ],
                            [
                                'name' => '통계적추정(5) - 모비율의 신뢰구간',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 9,
                            ],
                            [
                                'name' => '통계적추정(3) - 모비율의 계산 및 성질',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 10,
                            ],
                            [
                                'name' => '통계적추정(3) - 활용',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 11,
                            ],
                            [
                                'name' => '통계적추정(4) - 수학적귀납법',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 12,
                            ],
                        ]
                    ]
                ]
            ],
            [
                'name' => '미적분',
                'type' => 'scope',
                'depth' => 1,
                'order' => 6,
                'children' => [
                    [
                        'name' => '수열의 극한',
                        'type' => 'scope',
                        'depth' => 2,
                        'order' => 1,
                        'children' => [
                            [
                                'name' => '수열의 극한 단순 계산',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 1,
                            ],
                            [
                                'name' => '극한의 합과 일반항의 관계',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 2,
                            ],
                            [
                                'name' => '무한급수',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 3,
                            ],
                            [
                                'name' => '무한등비급수 – 도형',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 4,
                            ]
                        ]
                    ],
                    [
                        'name' => '삼각함수(2)',
                        'type' => 'scope',
                        'depth' => 2,
                        'order' => 2,
                        'children' => [
                            [
                                'name' => '삼각함수 단순계산',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 1,
                            ],
                            [
                                'name' => '삼각함수 계산 - 도형',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 2,
                            ],
                            [
                                'name' => '삼각함수 계산 – 그래프',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 3,
                            ]
                        ]
                    ],
                    [
                        'name' => '초월함수의극한과연속',
                        'type' => 'scope',
                        'depth' => 2,
                        'order' => 3,
                        'children' => [
                            [
                                'name' => '극한 단순 계산 문제',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 1,
                            ],
                            [
                                'name' => '초월함수 극한(1) – 삼각극한',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 2,
                            ],
                            [
                                'name' => '초월함수 극한(2) - 지수로그함수극한',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 3,
                            ],
                            [
                                'name' => '초월함수의 연속일 조건',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 4,
                            ],
                            [
                                'name' => '초월함수 극한, 연속의 합답형',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 5,
                            ]
                        ]
                    ],
                    [
                        'name' => '초월함수 미분',
                        'type' => 'scope',
                        'depth' => 2,
                        'order' => 4,
                        'children' => [
                            [
                                'name' => '미분법 공식을 이용한 단순계산',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 1,
                            ],
                            [
                                'name' => '미분 가능조건',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 2,
                            ]
                        ]
                    ],
                    [
                        'name' => '초월함수 미분(도함수 활용1)',
                        'type' => 'scope',
                        'depth' => 2,
                        'order' => 5,
                        'children' => [
                            [
                                'name' => '접선의 방정식',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 1,
                            ],
                            [
                                'name' => '접선의 방정식의 활용',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 2,
                            ],
                            [
                                'name' => '증가와 감소',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 3,
                            ],
                            [
                                'name' => '극대와 극소',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 4,
                            ],
                            [
                                'name' => '그래프 개형을 이용한 극대, 극소',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 5,
                            ],
                            [
                                'name' => '그래프 개형을 이용한 아래로볼록,위로볼록',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 6,
                            ]
                        ]
                    ],
                    [
                        'name' => '초월함수 미분(도함수 활용2)',
                        'type' => 'scope',
                        'depth' => 2,
                        'order' => 6,
                        'children' => [
                            [
                                'name' => '그래프의 최대, 최소',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 1,
                            ],
                            [
                                'name' => '그래프의 최대, 최소(범위가 문자)',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 2,
                            ],
                            [
                                'name' => '그래프(절댓값을 포함한 미분가능)',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 3,
                            ],
                            [
                                'name' => '그래프의 근의 개수',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 4,
                            ],
                            [
                                'name' => '범위가 쪼개어져 있는 그래프의 근의개수',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 5,
                            ],
                            [
                                'name' => '그래프 개형을 이용한 활용문제',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 6,
                            ],
                            [
                                'name' => '그래프 개형을 이용한 미분가능',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 7,
                            ],
                            [
                                'name' => '조건을 이용한 그래프의 유추',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 8,
                            ],
                            [
                                'name' => '거리,속도, 가속도',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 9,
                            ],
                            [
                                'name' => '미분의 활용',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 13,
                            ],
                            [
                                'name' => '미분의 합답형',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 14,
                            ],
                            [
                                'name' => '조건을 이용한 미분의 합답형',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 15,
                            ]
                        ]
                    ],
                    [
                        'name' => '초월함수 적분(도함수 활용2)',
                        'type' => 'scope',
                        'depth' => 2,
                        'order' => 7,
                        'children' => [
                            [
                                'name' => '단순적분계산',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 1,
                            ],
                            [
                                'name' => '정적분의 계산',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 2,
                            ],
                            [
                                'name' => '적분과 미분의 계산 활용',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 3,
                            ],
                            [
                                'name' => '넓이 및 부피',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 4,
                            ],
                            [
                                'name' => '평행이동 및 주기, 대칭성을 이용한 적분값 계산',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 5,
                            ],
                            [
                                'name' => '조건을 이용한 적분값 계산',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 6,
                            ],
                            [
                                'name' => '합답형',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 7,
                            ],
                            [
                                'name' => '그래프 개형을 이용한 적분값 계산',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 8,
                            ],
                            [
                                'name' => '거리와 속도, 곡선의 길이',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 9,
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    protected function generateHighSchoolCategories()
    {
        return [
            [
                'name' => '수학(상)',
                'type' => 'scope',
                'depth' => 1,
                'order' => 1,
                'children' => [
                    [
                        'name' => '집합의 연산',
                        'type' => 'scope',
                        'depth' => 2,
                        'order' => 1,
                        'children' => [
                            [
                                'name' => '집합의 뜻',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 1,
                            ],
                            [
                                'name' => '조건제시법으로 나타내어진 집합',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 2,
                            ],
                            [
                                'name' => '조건을 만족하는 집합',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 3,
                            ],
                            [
                                'name' => '두 집합 사이의 포함 관계',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 4,
                            ],
                            [
                                'name' => '세 집합 사이의 포함 관계',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 5,
                            ],
                            [
                                'name' => '서로 같은 집합',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 6,
                            ],
                            [
                                'name' => '집합을 원소로 갖는 집합',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 7,
                            ],
                            [
                                'name' => '부분집합을 원소로 갖는 집합',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 8,
                            ],
                            [
                                'name' => '부분집합의 개수',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 9,
                            ],
                            [
                                'name' => '특정한 원소를 갖거나 갖지 않는 부분집합의 개수',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 10,
                            ],
                            [
                                'name' => '집합의 연산',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 11,
                            ],
                            [
                                'name' => '서로소인 집합',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 12,
                            ],
                            [
                                'name' => '집합의 연산과 벤 다이어그램',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 13,
                            ],
                            [
                                'name' => '연산을 만족하는 부분집합의 개수',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 14,
                            ],
                            [
                                'name' => '집합의 연산과 미정계수의 결정',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 15,
                            ],
                            [
                                'name' => 'A가 B의 부분집합일 때 A, B 두 집합의 연산',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 16,
                            ],
                            [
                                'name' => '배수의 집합에서의 연산',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 17,
                            ],
                            [
                                'name' => '집합의 연산에 관한 성질',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 18,
                            ],
                            [
                                'name' => '집합의 연산에 관한 성질을 이용하여 같은 집합 찾기',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 19,
                            ],
                            [
                                'name' => '주어진 조건을 이용한 집합의 연산',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 20,
                            ],
                            [
                                'name' => '집합의 연산에 관한 성질을 이용하여 A,B의 포함 관계 찾기',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 21,
                            ],
                            [
                                'name' => '집합의 연산을 이용하여 집합의 원소 구하기',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 22,
                            ],
                            [
                                'name' => '새롭게 정의된 집합의 연산',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 23,
                            ],
                            [
                                'name' => '유한집합의 원소의 개수',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 24,
                            ],
                            [
                                'name' => '유한집합의 원소의 개수의 활용',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 25,
                            ]
                        ]
                    ],
                    [
                        'name' => '명제',
                        'type' => 'scope',
                        'depth' => 2,
                        'order' => 2,
                        'children' => [
                            [
                                'name' => '명제와 조건의 부정',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 1,
                            ],
                            [
                                'name' => '진리집합',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 2,
                            ],
                            [
                                'name' => '거짓인 명제의 반례',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 3,
                            ],
                            [
                                'name' => '명제의 참, 거짓',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 4,
                            ],
                            [
                                'name' => '\'모든\', \'어떤\'을 포함한 명제',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 5,
                            ],
                            [
                                'name' => '\'모든\'이 숨어 있는 명제',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 6,
                            ],
                            [
                                'name' => '명제의 참, 거짓과 집합의 포함 관계',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 7,
                            ],
                            [
                                'name' => '명제가 참이 되도록 하는 미정계수 구하기',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 8,
                            ],
                            [
                                'name' => '명제와 역, 이, 대우의 참, 거짓',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 9,
                            ],
                            [
                                'name' => '명제의 대우를 이용하여 미정계수 구하기',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 10,
                            ],
                            [
                                'name' => '대우를 이용한 명제의 증명',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 11,
                            ],
                            [
                                'name' => '삼단논법',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 12,
                            ],
                            [
                                'name' => '필요조건과 충분조건',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 13,
                            ],
                            [
                                'name' => '필요충분조건',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 14,
                            ],
                            [
                                'name' => '필요조건, 충분조건과 명제의 참, 거짓',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 15,
                            ],
                            [
                                'name' => '집합에서의 필요충분조건',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 16,
                            ],
                            [
                                'name' => '필요조건을 만족하는 미정계수 구하기',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 17,
                            ],
                            [
                                'name' => '충분조건을 만족하는 미정계수 구하기',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 18,
                            ],
                            [
                                'name' => '필요, 충분조건을 만족하는 미정계수 구하기',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 19,
                            ],
                            [
                                'name' => '진리집합 사이의 관계(1)',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 20,
                            ]
                        ]
                    ],
                    [
                        'name' => '실수와 복소수',
                        'type' => 'scope',
                        'depth' => 2,
                        'order' => 3,
                        'children' => [
                            [
                                'name' => '수학적 기초 ; 제곱근의 뜻과 성질',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 1,
                            ],
                            [
                                'name' => '실수의 체계',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 2,
                            ],
                            [
                                'name' => '무리수의 증명',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 3,
                            ],
                            [
                                'name' => '사칙연산에 대하여 닫혀 있는 집합',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 4,
                            ],
                            [
                                'name' => '실수의 연산법칙',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 5,
                            ],
                            [
                                'name' => '사칙연산에 대한 항등원과 역원',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 6,
                            ],
                            [
                                'name' => '일반적인 연산',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 7,
                            ],
                            [
                                'name' => '일반적인 연산에 대한 연산법칙',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 8,
                            ],
                            [
                                'name' => '일반적인 연산에 대한 항등원',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 9,
                            ],
                            [
                                'name' => '일반적인 연산에 대한 역원',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 10,
                            ],
                            [
                                'name' => '역원이 존재하지 않는 실수 구하기',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 11,
                            ],
                            [
                                'name' => '표로 나타내어진 연산',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 12,
                            ],
                            [
                                'name' => '실수의 대소 관계',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 13,
                            ],
                            [
                                'name' => '실수의 성질',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 14,
                            ],
                            [
                                'name' => '절댓값 기호를 포함한 식의 계산',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 15,
                            ],
                            [
                                'name' => '절댓값의 성질',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 16,
                            ],
                            [
                                'name' => '복소수의 정의',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 17,
                            ],
                            [
                                'name' => '복소수의 사칙연산',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 18,
                            ],
                            [
                                'name' => '복소수의 정의와 복소수의 연산',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 19,
                            ],
                            [
                                'name' => '복소수가 주어졌을 때의 식의 값',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 20,
                            ],
                            [
                                'name' => '복소수의 연산에 대한 항등원과 역원',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 21,
                            ],
                            [
                                'name' => '복소수가 서로 같을 조건',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 22,
                            ],
                            [
                                'name' => '켤레복소수와 연산',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 23,
                            ],
                            [
                                'name' => '켤레복소수의 성질',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 24,
                            ],
                            [
                                'name' => '복소수의 일반적인 연산',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 25,
                            ],
                            [
                                'name' => '복소수를 임의의 식으로 놓고 풀기',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 26,
                            ],
                            [
                                'name' => '복소수의 거듭제곱의 합',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 27,
                            ],
                            [
                                'name' => '복소수의 거듭제곱',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 28,
                            ],
                            [
                                'name' => '음수의 제곱근의 성질',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 29,
                            ],
                            [
                                'name' => '음수의 제곱근의 계산',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 30,
                            ],
                        ]
                    ],
                    [
                        'name' => '다항식과 나머지 정리',
                        'type' => 'scope',
                        'depth' => 2,
                        'order' => 4,
                        'children' => [
                            [
                                'name' => '다항식의 덧셈과 뺄셈',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 1,
                            ],
                            [
                                'name' => '다항식의 나눗셈',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 2,
                            ],
                            [
                                'name' => '다항식의 나눗셈식의 변형',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 3,
                            ],
                            [
                                'name' => '다항식의 전개식에서 계수 구하기',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 4,
                            ],
                            [
                                'name' => '공통부분을 치환하여 전개하기',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 5,
                            ],
                            [
                                'name' => '곱셈 공식',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 6,
                            ],
                            [
                                'name' => '곱셈 공식의 변형',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 7,
                            ],
                            [
                                'name' => '곱셈 공식의 활용',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 8,
                            ],
                            [
                                'name' => '항등식에서 미정계수 구하기',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 9,
                            ],
                            [
                                'name' => '조건을 만족하는 항등식',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 10,
                            ],
                            [
                                'name' => '항등식에서 계수의 합 구하기',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 11,
                            ],
                            [
                                'name' => '나머지정리 ; 일차식으로 나눌 때',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 12,
                            ],
                            [
                                'name' => '나머지정리 ; 이차식으로 나눌 때',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 13,
                            ],
                            [
                                'name' => '나머지정리 ; 삼차식으로 나눌 때',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 14,
                            ],
                            [
                                'name' => '나머지정리 ; 미정계수 구하기',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 15,
                            ],
                            [
                                'name' => '나머지정리의 변형',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 16,
                            ],
                            [
                                'name' => '나머지정리의 활용',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 17,
                            ],
                            [
                                'name' => '인수정리',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 18,
                            ],
                            [
                                'name' => '조립제법',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 19,
                            ],
                        ]
                    ],
                    [
                        'name' => '인수분해, 약수와 배수',
                        'type' => 'scope',
                        'depth' => 2,
                        'order' => 5,
                        'children' => [
                            [
                                'name' => '도형을 이용한 인수분해 공식의 유도',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 1,
                            ],
                            [
                                'name' => '인수분해의 기초',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 2,
                            ],
                            [
                                'name' => '치환을 이용한 인수분해',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 3,
                            ],
                            [
                                'name' => '복이차식의 인수분해',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 4,
                            ],
                            [
                                'name' => <<<EOF
                                    <p><math xmlns="http://www.w3.org/1998/Math/MathML"><msup><mi>A</mi><mn>2</mn></msup><mo>-</mo><msup><mi>B</mi><mn>2</mn></msup></math> 으로 변형하여 인수분해하기</p>
                                EOF,
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 5,
                            ],
                            [
                                'name' => '내림차순으로 정리하여 인수분해하기',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 6,
                            ],
                            [
                                'name' => '인수정리를 이용하여 인수분해하기',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 7,
                            ],
                            [
                                'name' => '계수가 대칭인 사차식의 인수분해',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 8,
                            ],
                            [
                                'name' => 'a, b, c 순환의 꼴의 인수분해',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 9,
                            ],
                            [
                                'name' => '조건이 주어진 다항식의 인수분해',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 10,
                            ],
                            [
                                'name' => '새로운 연산으로 정의된 식의 인수분해',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 11,
                            ],
                            [
                                'name' => '인수정리를 이용하여 미정계수 구하기',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 12,
                            ],
                            [
                                'name' => '인수분해의 삼각형에의 활용',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 13,
                            ],
                            [
                                'name' => '인수분해를 이용하여 식의 값 구하기(1)',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 14,
                            ],
                            [
                                'name' => '인수분해를 이용한 수의 계산',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 15,
                            ],
                            [
                                'name' => '정수의 분류',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 16,
                            ],
                            [
                                'name' => '배수의 판정',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 17,
                            ],
                            [
                                'name' => '정수의 최대공약수와 최소공배수',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 18,
                            ],
                            [
                                'name' => '자연수의 양의 약수의 개수',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 19,
                            ],
                            [
                                'name' => '자연수의 양의 약수의 총합',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 20,
                            ],
                            [
                                'name' => '다항식의 최대공약수',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 21,
                            ],
                            [
                                'name' => '다항식의 최소공배수',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 22,
                            ],
                            [
                                'name' => '다항식 구하기 ; 최대공약수와 최소공배수가 주어진 경우',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 23,
                            ],
                            [
                                'name' => '다항식 구하기 ; 최대공약수와 최소공배수의 곱이 주어진 경우',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 24,
                            ],
                            [
                                'name' => '다항식 구하기 ; 다항식의 합과 최소공배수 가 주어진 경우',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 25,
                            ],
                            [
                                'name' => '다항식이 일차의 최대공약수를 갖는 경우',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 26,
                            ],
                            [
                                'name' => <<<EOF
                                    몫과 나머지 형태(<img src="data:image/svg+xml;charset=utf8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20xmlns%3Awrs%3D%22http%3A%2F%2Fwww.wiris.com%2Fxml%2Fmathml-extension%22%20height%3D%2220%22%20width%3D%2282%22%20wrs%3Abaseline%3D%2216%22%3E%3C!--MathML%3A%20%3Cmath%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F1998%2FMath%2FMathML%22%3E%3Cmi%3EA%3C%2Fmi%3E%3Cmo%3E%3D%3C%2Fmo%3E%3Cmi%3EB%3C%2Fmi%3E%3Cmi%3EQ%3C%2Fmi%3E%3Cmo%3E%2B%3C%2Fmo%3E%3Cmi%3ER%3C%2Fmi%3E%3C%2Fmath%3E--%3E%3Cdefs%3E%3Cstyle%20type%3D%22text%2Fcss%22%3E%40font-face%7Bfont-family%3A'math1564b4c0e54101ac57a0cb68c16'%3Bsrc%3Aurl(data%3Afont%2Ftruetype%3Bcharset%3Dutf-8%3Bbase64%2CAAEAAAAMAIAAAwBAT1MvMi7iBBMAAADMAAAATmNtYXDEvmKUAAABHAAAADxjdnQgDVUNBwAAAVgAAAA6Z2x5ZoPi2VsAAAGUAAABK2hlYWQQC2qxAAACwAAAADZoaGVhCGsXSAAAAvgAAAAkaG10eE2rRkcAAAMcAAAADGxvY2EAHTwYAAADKAAAABBtYXhwBT0FPgAAAzgAAAAgbmFtZaBxlY4AAANYAAABn3Bvc3QB9wD6AAAE%2BAAAACBwcmVwa1uragAABRgAAAAUAAADSwGQAAUAAAQABAAAAAAABAAEAAAAAAAAAQEAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACAgICAAAAAg1UADev96AAAD6ACWAAAAAAACAAEAAQAAABQAAwABAAAAFAAEACgAAAAGAAQAAQACACsAPf%2F%2FAAAAKwA9%2F%2F%2F%2F1v%2FFAAEAAAAAAAAAAAFUAywAgAEAAFYAKgJYAh4BDgEsAiwAWgGAAoAAoADUAIAAAAAAAAAAKwBVAIAAqwDVAQABKwAHAAAAAgBVAAADAAOrAAMABwAAMxEhESUhESFVAqv9qwIA%2FgADq%2FxVVQMAAAEAgABVAtUCqwALAEkBGLIMAQEUExCxAAP2sQEE9bAKPLEDBfWwCDyxBQT1sAY8sQ0D5gCxAAATELEBBuSxAQETELAFPLEDBOWxCwX1sAc8sQkE5TEwEyERMxEhFSERIxEhgAEAVQEA%2FwBV%2FwABqwEA%2FwBW%2FwABAAACAIAA6wLVAhUAAwAHAGUYAbAIELAG1LAGELAF1LAIELAB1LABELAA1LAGELAHPLAFELAEPLABELACPLAAELADPACwCBCwBtSwBhCwB9SwBxCwAdSwARCwAtSwBhCwBTywBxCwBDywARCwADywAhCwAzwxMBMhNSEdASE1gAJV%2FasCVQHAVdVVVQAAAQAAAAEAANV4zkFfDzz1AAMEAP%2F%2F%2F%2F%2FWOhNz%2F%2F%2F%2F%2F9Y6E3MAAP8gBIADqwAAAAoAAgABAAAAAAABAAAD6P9qAAAXcAAA%2F7YEgAABAAAAAAAAAAAAAAAAAAAAAwNSAFUDVgCAA1YAgAAAAAAAAAAoAAAAoQAAASsAAQAAAAMAXgAFAAAAAAACAIAEAAAAAAAEAADeAAAAAAAAABUBAgAAAAAAAAABABIAAAAAAAAAAAACAA4AEgAAAAAAAAADADAAIAAAAAAAAAAEABIAUAAAAAAAAAAFABYAYgAAAAAAAAAGAAkAeAAAAAAAAAAIABwAgQABAAAAAAABABIAAAABAAAAAAACAA4AEgABAAAAAAADADAAIAABAAAAAAAEABIAUAABAAAAAAAFABYAYgABAAAAAAAGAAkAeAABAAAAAAAIABwAgQADAAEECQABABIAAAADAAEECQACAA4AEgADAAEECQADADAAIAADAAEECQAEABIAUAADAAEECQAFABYAYgADAAEECQAGAAkAeAADAAEECQAIABwAgQBNAGEAdABoACAARgBvAG4AdABSAGUAZwB1AGwAYQByAE0AYQB0AGgAcwAgAEYAbwByACAATQBvAHIAZQAgAE0AYQB0AGgAIABGAG8AbgB0AE0AYQB0AGgAIABGAG8AbgB0AFYAZQByAHMAaQBvAG4AIAAxAC4AME1hdGhfRm9udABNAGEAdABoAHMAIABGAG8AcgAgAE0AbwByAGUAAAMAAAAAAAAB9AD6AAAAAAAAAAAAAAAAAAAAAAAAAAC5BxEAAI2FGACyAAAAFRQTsQABPw%3D%3D)format('truetype')%3Bfont-weight%3Anormal%3Bfont-style%3Anormal%3B%7D%3C%2Fstyle%3E%3C%2Fdefs%3E%3Ctext%20font-family%3D%22Arial%22%20font-size%3D%2216%22%20font-style%3D%22italic%22%20text-anchor%3D%22middle%22%20x%3D%225.5%22%20y%3D%2216%22%3EA%3C%2Ftext%3E%3Ctext%20font-family%3D%22math1564b4c0e54101ac57a0cb68c16%22%20font-size%3D%2216%22%20text-anchor%3D%22middle%22%20x%3D%2220.5%22%20y%3D%2216%22%3E%3D%3C%2Ftext%3E%3Ctext%20font-family%3D%22Arial%22%20font-size%3D%2216%22%20font-style%3D%22italic%22%20text-anchor%3D%22middle%22%20x%3D%2234.5%22%20y%3D%2216%22%3EB%3C%2Ftext%3E%3Ctext%20font-family%3D%22Arial%22%20font-size%3D%2216%22%20font-style%3D%22italic%22%20text-anchor%3D%22middle%22%20x%3D%2246.5%22%20y%3D%2216%22%3EQ%3C%2Ftext%3E%3Ctext%20font-family%3D%22math1564b4c0e54101ac57a0cb68c16%22%20font-size%3D%2216%22%20text-anchor%3D%22middle%22%20x%3D%2260.5%22%20y%3D%2216%22%3E%2B%3C%2Ftext%3E%3Ctext%20font-family%3D%22Arial%22%20font-size%3D%2216%22%20font-style%3D%22italic%22%20text-anchor%3D%22middle%22%20x%3D%2274.5%22%20y%3D%2216%22%3ER%3C%2Ftext%3E%3C%2Fsvg%3E" />)에서  B와 R의 최대공약수
                                EOF,
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 27,
                            ],
                            [
                                'name' => '최대공약수와 최소공배수 구하기 - 새로운 연산 기호가 주어진 경우',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 28,
                            ],
                            [
                                'name' => '최대공약수와 최소공배수 구하기-합답형',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 29,
                            ],
                            [
                                'name' => '조건을 만족하는 다항식 구하기',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 30,
                            ],
                        ]
                    ],
                    [
                        'name' => '유리식과 무리식',
                        'type' => 'scope',
                        'depth' => 2,
                        'order' => 6,
                        'children' => [
                            [
                                'name' => '분수식의 덧셈과 뺄셈',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 1,
                            ],
                            [
                                'name' => '분수식의 곱셈과 나눗셈',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 2,
                            ],
                            [
                                'name' => '분수식과 항등식',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 3,
                            ],
                            [
                                'name' => '부분분수 계산',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 4,
                            ],
                            [
                                'name' => '번분수식',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 5,
                            ],
                            [
                                'name' => '분수를 번분수로 나타내기',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 6,
                            ],
                            [
                                'name' => '조건이 주어졌을 때 분수식의 값 구하기',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 7,
                            ],
                            [
                                'name' => '분수식의 활용 ; 백분율',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 8,
                            ],
                            [
                                'name' => '조건이 비례식일 때 식의 값 구하기',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 9,
                            ],
                            [
                                'name' => '조건이 연립방정식일 때 식의 값 구하기',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 10,
                            ],
                            [
                                'name' => '가비의 리',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 11,
                            ],
                            [
                                'name' => '비례식의 활용',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 12,
                            ],
                            [
                                'name' => '제곱근의 계산',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 13,
                            ],
                            [
                                'name' => '제곱근의 성질',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 14,
                            ],
                            [
                                'name' => '분모의 유리화',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 15,
                            ],
                            [
                                'name' => '이중근호 간단히 하기',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 16,
                            ],
                            [
                                'name' => '무리수의 정수 부분과 소수 부분',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 17,
                            ],
                            [
                                'name' => '복잡한 무리식의 값 구하기',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 18,
                            ],
                            [
                                'name' => <<<EOF
                                    <p><math xmlns="http://www.w3.org/1998/Math/MathML"><mi>x</mi><mo>=</mo><msqrt><mi>a</mi></msqrt><mo>+</mo><msqrt><mi>b</mi></msqrt><mo>,</mo><mo>&#160;</mo><mi>y</mi><mo>=</mo><msqrt><mi>a</mi></msqrt><mo>-</mo><msqrt><mi>b</mi></msqrt></math> 꼴이 주어졌을 때 식의 값 구하기</p>
                                EOF,
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 19,
                            ],
                            [
                                'name' => <<<EOF
                                    <p><math xmlns="http://www.w3.org/1998/Math/MathML"><mi>x</mi><mo>=</mo><mi>a</mi><mo>+</mo><msqrt><mi>b</mi></msqrt></math> 꼴이 주어졌을 때 식의 값 구하기</p>
                                EOF,
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 20,
                            ],
                            [
                                'name' => '이중근호를 가진 식의 값 구하기',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 21,
                            ],
                            [
                                'name' => '무리수가 서로 같을 조건',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 22,
                            ],
                        ]
                    ],
                    [
                        'name' => '이차방정식',
                        'type' => 'scope',
                        'depth' => 2,
                        'order' => 7,
                        'children' => [
                            [
                                'name' => '이차방정식의 풀이',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 1,
                            ],
                            [
                                'name' => '이차방정식의 한 근이 주어졌을 때 상수 구하기',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 2,
                            ],
                            [
                                'name' => '이차방정식을 변형하여 식의 값 구하기',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 3,
                            ],
                            [
                                'name' => '이차항의 계수가 무리수인 이차방정식',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 4,
                            ],
                            [
                                'name' => '이차항의 계수가 허수인 이차방정식',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 5,
                            ],
                            [
                                'name' => '절댓값 기호를 포함한 이차방정식',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 6,
                            ],
                            [
                                'name' => '가우스 기호를 포함한 이차방정식',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 7,
                            ],
                            [
                                'name' => '집합의 연산과 이차방정식',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 8,
                            ],
                            [
                                'name' => '이차방정식의 도형에의 활용',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 9,
                            ],
                            [
                                'name' => '이차방정식의 실생활에의 활용',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 10,
                            ],
                            [
                                'name' => '이차방정식이 실근을 가질 조건',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 11,
                            ],
                            [
                                'name' => '이차방정식이 중근을 가질 조건',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 12,
                            ],
                            [
                                'name' => '이차방정식이 허근을 가질 조건',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 13,
                            ],
                            [
                                'name' => '계수가 문자인 이차방정식의 근의 판별',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 14,
                            ],
                            [
                                'name' => '이차방정식의 판별식의 활용',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 15,
                            ],
                            [
                                'name' => '계수가 허수인 이차방정식의 근',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 16,
                            ],
                            [
                                'name' => '근과 계수의 관계를 이용하여 식의 값 구하기',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 17,
                            ],
                            [
                                'name' => '근과 계수의 관계의 활용',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 18,
                            ],
                            [
                                'name' => '근과 계수의 관계를 이용한 미정계수의 결정',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 19,
                            ],
                            [
                                'name' => '이차방정식의 근의 공식을 이용하여 이차식 인수분해하기',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 20,
                            ],
                            [
                                'name' => '두 수를 근으로 갖는 이차방정식',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 21,
                            ],
                            [
                                'name' => '잘못 보고 푼 이차방정식의 작성',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 22,
                            ],
                            [
                                'name' => <<<EOF
                                    <p><math xmlns="http://www.w3.org/1998/Math/MathML"><mi>f</mi><mo>(</mo><mi>x</mi><mo>)</mo><mo>=</mo><mn>0</mn></math>&nbsp;의 두 근을 이용하여&nbsp;<math xmlns="http://www.w3.org/1998/Math/MathML"><mi>f</mi><mo>(</mo><mi>a</mi><mi>x</mi><mo>+</mo><mi>b</mi><mo>)</mo><mo>=</mo><mn>0</mn></math> 의 근의 합 또는 곱 구하기</p>
                                EOF,
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 23,
                            ],
                            [
                                'name' => '이차방정식의 켤레근',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 24,
                            ],
                            [
                                'name' => '이차방정식의 실근의 부호',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 25,
                            ],
                        ]
                    ],
                    [
                        'name' => '고차방정식과 연립방정식',
                        'type' => 'scope',
                        'depth' => 2,
                        'order' => 8,
                        'children' => [
                            [
                                'name' => '삼차방정식의 풀이',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 1,
                            ],
                            [
                                'name' => '삼차방정식의 근의 의미',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 2,
                            ],
                            [
                                'name' => '삼차방정식의 해의 조건',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 3,
                            ],
                            [
                                'name' => '인수정리를 이용한 사차방정식의 풀이',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 4,
                            ],
                            [
                                'name' => '치환을 이용한 사차방정식의 풀이',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 5,
                            ],
                            [
                                'name' => '상반 대칭 사차방정식 꼴의 방정식',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 6,
                            ],
                            [
                                'name' => '복이차방정식의 풀이',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 7,
                            ],
                            [
                                'name' => '복이차방정식이 실근을 가질 조건',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 8,
                            ],
                            [
                                'name' => '사차방정식의 근의 의미',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 9,
                            ],
                            [
                                'name' => '삼차방정식의 근과 계수의 관계',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 10,
                            ],
                            [
                                'name' => '계수가 유리수인 방정식의 켤레근',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 11,
                            ],
                            [
                                'name' => '계수가 실수인 방정식의 켤레근',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 12,
                            ],
                            [
                                'name' => <<<EOF
                                    <p>방정식&nbsp;<math xmlns="http://www.w3.org/1998/Math/MathML"><msup><mi>x</mi><mn>3</mn></msup><mo>=</mo><mn>1</mn></math> 의 허근의 성질</p>
                                EOF,
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 13,
                            ],
                            [
                                'name' => '고차방정식의 활용',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 14,
                            ],
                            [
                                'name' => '미지수가 개인 연립일차방정식',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 15,
                            ],
                            [
                                'name' => '미지수가 개인 연립일차방정식',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 16,
                            ],
                            [
                                'name' => '미지수가 개인 순환형의 연립일차방정식',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 17,
                            ],
                            [
                                'name' => '연립일차방정식의 해의 조건',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 18,
                            ],
                            [
                                'name' => '연립일차방정식의 활용',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 19,
                            ],
                            [
                                'name' => '일차방정식과 이차방정식으로 이루어진 방정식',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 20,
                            ],
                            [
                                'name' => '두 이차방정식으로 이루어진 연립방정식 : 인수분해',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 21,
                            ],
                            [
                                'name' => '두 이차방정식으로 이루어진 연립방정식 : 상수항 소거',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 22,
                            ],
                            [
                                'name' => '두 이차방정식으로 이루어진 연립방정식 : 이차항 소거',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 23,
                            ],
                            [
                                'name' => '대칭형의 연립방정식',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 24,
                            ],
                            [
                                'name' => '연립이차방정식의 해의 조건',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 25,
                            ],
                            [
                                'name' => '정수 조건의 부정방정식',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 26,
                            ],
                            [
                                'name' => '실수 조건의 부정방정식 ; 판별식 이용',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 27,
                            ],
                            [
                                'name' => '실수 조건의 부정방정식 ;   ',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 28,
                            ],
                            [
                                'name' => '공통근을 갖는 방정식',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 29,
                            ],
                        ]
                    ],
                    [
                        'name' => '부등식',
                        'type' => 'scope',
                        'depth' => 2,
                        'order' => 9,
                        'children' => [
                            [
                                'name' => '부등식의 기본 성질',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 1,
                            ],
                            [
                                'name' => '조건을 이용하여 식의 값의 범위 구하기(1)',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 2,
                            ],
                            [
                                'name' => '조건을 이용하여 식의 값의 범위 구하기(2)',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 3,
                            ],
                            [
                                'name' => '일차부등식의 미정계수의 결정',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 4,
                            ],
                            [
                                'name' => '부등식   의 해가 없거나 무수히 많은 경우',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 5,
                            ],
                            [
                                'name' => '절댓값을 포함한 일차 부등식(1)     ,      꼴의 부등식',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 6,
                            ],
                            [
                                'name' => '절대값을 포함한 일차 부등식(2)      꼴의 부등식',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 7,
                            ],
                            [
                                'name' => '절댓값 기호를 두 개 포함한 부등식',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 8,
                            ],
                            [
                                'name' => '차를 이용한 대소 비교(1)',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 9,
                            ],
                            [
                                'name' => '제곱의 차를 이용한 대소 비교',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 10,
                            ],
                            [
                                'name' => '비를 이용한 대소 비교',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 11,
                            ],
                            [
                                'name' => '이차부등식의 해',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 12,
                            ],
                            [
                                'name' => '이차부등식의 미정계수의 결정',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 13,
                            ],
                            [
                                'name' => '절댓값 기호를 포함한 이차부등식',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 14,
                            ],
                            [
                                'name' => '가우스 기호를 포함한 이차부등식',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 15,
                            ],
                            [
                                'name' => '이차방정식의 근의 판별과 부등식',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 16,
                            ],
                            [
                                'name' => '이차부등식의 활용',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 17,
                            ],
                            [
                                'name' => '부등식   의 해를 이용하여 부등식   의 해 구하기',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 18,
                            ],
                            [
                                'name' => '해가 없는 이차부등식',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 19,
                            ],
                            [
                                'name' => '한 개의 실근을 갖는 이차부등식',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 20,
                            ],
                            [
                                'name' => '모든 실수에 대하여 성립하는 이차부등식',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 21,
                            ],
                            [
                                'name' => '연립이차부등식의 풀이',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 22,
                            ],
                            [
                                'name' => '연립이차부등식의 미정계수의 결정',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 23,
                            ],
                            [
                                'name' => '절댓값 기호를 포함한 연립부등식',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 24,
                            ],
                            [
                                'name' => '부등식의 해를 집합으로 표시하기',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 25,
                            ],
                            [
                                'name' => '부등식과 집합의 포함 관계',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 26,
                            ],
                            [
                                'name' => '부등식으로 나타내어진 집합의 연산',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 27,
                            ],
                            [
                                'name' => '이차방정식의 근의 판별과 연립이차부등식',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 28,
                            ],
                            [
                                'name' => '연립이차부등식의 활용',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 29,
                            ],
                        ]
                    ],
                    [
                        'name' => '절대부등식',
                        'type' => 'scope',
                        'depth' => 2,
                        'order' => 10,
                        'children' => [
                            [
                                'name' => '실수의 성질을 이용한 부등식의 증명(1)',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 1,
                            ],
                            [
                                'name' => '절대부등식 찾기',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 2,
                            ],
                            [
                                'name' => '실수의 성질을 이용한 부등식의 증명(2)',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 3,
                            ],
                            [
                                'name' => '절대부등식이 되기 위한 조건',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 4,
                            ],
                            [
                                'name' => '절댓값 기호를 포함한 절대부등식',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 5,
                            ],
                            [
                                'name' => '절대부등식의 활용',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 6,
                            ],
                            [
                                'name' => '산술평균과 기하평균의 관계를 이용한부등식의 증명',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 7,
                            ],
                            [
                                'name' => '산술평균과 기하평균의 관계 ; 합의 최소 : 전개',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 8,
                            ],
                            [
                                'name' => '산술평균과 기하평균의 관계 ; 합의 최소 : 식의 변형',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 9,
                            ],
                            [
                                'name' => '산술평균과 기하평균의 관계 ; 합의 최소 : 조건 대입',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 10,
                            ],
                            [
                                'name' => '산술평균과 기하평균의 관계 ; 합이 일정할 때, 곱의 최대',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 11,
                            ],
                            [
                                'name' => '산술평균과 기하평균의 관계 ; 식의 최솟값 구하기',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 12,
                            ],
                            [
                                'name' => '산술평균과 기하평균의 관계의 활용',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 13,
                            ],
                            [
                                'name' => '코시-슈바르츠의 부등식 ; 의 최대・최소',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 14,
                            ],
                            [
                                'name' => '코시-슈바르츠의 부등식 ;    의 최대․최소',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 15,
                            ],
                            [
                                'name' => '코시-슈바르츠의 부등식(이차다항식형태) ;              ≥ ',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 16,
                            ],
                            [
                                'name' => '코시-슈바르츠의 부등식의 활용',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 17,
                            ],
                        ]
                    ]
                ]
            ]
        ];
    }

    public function run()
    {
        $this->categories = [
            [
                'name' => '초',
                'type' => 'scope',
                'depth' => 0,
                'order' => 1,
                'children' => self::generateElementaryCategories()
            ],
            [
                'name' => '중',
                'type' => 'scope',
                'depth' => 0,
                'order' => 2,
                'children' => self::generateMiddleSchoolCategories()
            ],
            [
                'name' => '고',
                'type' => 'scope',
                'depth' => 0,
                'order' => 3,
                'children' => self::generateHighSchoolCategories()
            ],
            [
                'name' => '고3',
                'type' => 'scope',
                'depth' => 0,
                'order' => 4,
                'children' => self::generateHighSchool3Categories()
            ]
        ];
        // 기존 데이터 삭제
        // DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        // DB::table('question_category_closure')->truncate();
        // QuestionCategory::truncate();
        // DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 카테고리 트리 생성
        foreach ($this->categories as $categoryData) {
            $this->createCategoryTree($categoryData);
        }

        // 모든 QuestionCategory에 대해
        // QuestionCategory::where('type', 'question_type')->get()->each(function ($category) {
        //     // 레벨 1~5까지
        //     for ($level = 1; $level <= 5; $level++) {
        //         // 각 조합당 5개의 문제 생성
        //         Question::factory()
        //             ->count(10)
        //             ->create([
        //                 'question_type_id' => $category->id,
        //                 'level' => $level,
        //             ]);
        //     }
        // });
    }

    protected function createCategoryTree($data, $parentId = null)
    {
        $category = QuestionCategory::create([
            'name' => $data['name'],
            'type' => $data['type'],
            'depth' => $data['depth'],
            'order' => $data['order'] ?? 0
        ]);

        // 1. 자기 자신과의 관계 추가 (depth = 0)
        $this->createClosure($category->id, $category->id, 0);

        if ($parentId) {
            // 2. 직접적인 부모와의 관계 추가
            $this->createClosure($parentId, $category->id, 1);

            // 3. 부모의 모든 조상과의 관계 추가
            $parentClosures = DB::table('question_category_closure')
                ->where('descendant_id', $parentId)
                ->where('ancestor_id', '!=', $parentId)
                ->get();

            foreach ($parentClosures as $closure) {
                $this->createClosure($closure->ancestor_id, $category->id, $closure->depth + 1);
            }
        }

        // 4. 자식 카테고리들 생성
        if (isset($data['children'])) {
            foreach ($data['children'] as $child) {
                $this->createCategoryTree($child, $category->id);
            }
        }

        return $category;
    }

    protected function createClosure($ancestorId, $descendantId, $depth)
    {
        // 중복 체크 후 생성
        $exists = DB::table('question_category_closure')
            ->where('ancestor_id', $ancestorId)
            ->where('descendant_id', $descendantId)
            ->exists();

        if (!$exists) {
            DB::table('question_category_closure')->insert([
                'ancestor_id' => $ancestorId,
                'descendant_id' => $descendantId,
                'depth' => $depth
            ]);
        }
    }
}
