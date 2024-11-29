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
                                'name' => 'A가 B의 부분집합일 때 두 집합의 연산',
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
                                'name' => '집합의 연산에 관한 성질을 이용하여 포함 관계 찾기',
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
        QuestionCategory::where('type', 'question_type')->get()->each(function ($category) {
            // 레벨 1~5까지
            for ($level = 1; $level <= 5; $level++) {
                // 각 조합당 5개의 문제 생성
                Question::factory()
                    ->count(10)
                    ->create([
                        'question_type_id' => $category->id,
                        'level' => $level,
                    ]);
            }
        });
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
