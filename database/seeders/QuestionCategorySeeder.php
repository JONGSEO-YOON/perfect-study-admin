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
                                        ],
                                        [
                                            'name' => '소수의 성질',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 3,
                                        ],
                                        [
                                            'name' => '거듭제곱으로 나타내기',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 4,
                                        ],
                                        [
                                            'name' => '소인수분해 하기',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 5,
                                        ],
                                        [
                                            'name' => '소인수 구하기',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 6,
                                        ],
                                        [
                                            'name' => '제곱인 수 만들기',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 7,
                                        ],
                                        [
                                            'name' => '소인수분해를 이용하여 약수 구하기',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 8,
                                        ],
                                        [
                                            'name' => '약수의 개수',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 9,
                                        ],
                                        [
                                            'name' => '약수의 개수가 주어질 때 가능한 수 찾기',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 10,
                                        ],
                                    ],
                                ],
                                [
                                    'name' => '최대공약수와 최소공배수',
                                    'type' => 'scope',
                                    'depth' => 3,
                                    'order' => 2,
                                    'children' => [
                                        [
                                            'name' => '공약수와 최대공약수',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 1,
                                        ],
                                        [
                                            'name' => '서로소',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 2,
                                        ],
                                        [
                                            'name' => '공배수와 최소공배수',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 3,
                                        ],
                                        [
                                            'name' => '소인수분해를 이용한 최대공약수, 최소공배수',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 4,
                                        ],
                                        [
                                            'name' => '최대공약수, 최소공배수 활용(1) - 실생활의 활용',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 5,
                                        ],
                                        [
                                            'name' => '최대공약수, 최소공배수 활용(2) - 나눗셈',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 6,
                                        ],
                                        [
                                            'name' => '최대공약수, 최소공배수 활용(3) - 사각형, 정육면체',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 7,
                                        ],
                                        [
                                            'name' => '최대공약수, 최소공배수 활용(4) - 두 수 구하기',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 8,
                                        ],
                                        [
                                            'name' => '두 분수를 자연수로 만들기',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 9,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'name' => '정수와 유리수',
                            'type' => 'scope',
                            'depth' => 2,
                            'order' => 2,
                            'children' => [
                                [
                                    'name' => '정수와 그 계산',
                                    'type' => 'scope',
                                    'depth' => 3,
                                    'order' => 1,
                                    'children' => [
                                        [
                                            'name' => '정수 찾기',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 1,
                                        ],
                                        [
                                            'name' => '절댓값의 뜻과 성질',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 2,
                                        ],
                                        [
                                            'name' => '절댓값의 대소관계',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 3,
                                        ],
                                        [
                                            'name' => '절댓값이 같고 부호가 반대인 두 수',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 4,
                                        ],
                                        [
                                            'name' => '절댓값의 활용',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 5,
                                        ],
                                        [
                                            'name' => '정수의 대소관계',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 6,
                                        ],
                                        [
                                            'name' => '부등호를 사용하여 나타내기',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 7,
                                        ],
                                        [
                                            'name' => '주어진 범위에 속하는 수',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 8,
                                        ],
                                        [
                                            'name' => '정수의 덧셈과 뺄셈',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 9,
                                        ],
                                        [
                                            'name' => '덧셈에 대한 계산법칙',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 10,
                                        ],
                                        [
                                            'name' => '덧셈과 뺄셈의 혼합계산',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 11,
                                        ],
                                        [
                                            'name' => '○보다 □만큼 큰(작은) 수',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 12,
                                        ],
                                        [
                                            'name' => '덧셈과 뺄셈의 활용',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 13,
                                        ],
                                        [
                                            'name' => '정수의 곱셈',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 14,
                                        ],
                                        [
                                            'name' => '곱셈에 대한 계산법칙',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 15,
                                        ],
                                        [
                                            'name' => '거듭제곱',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 16,
                                        ],
                                        [
                                            'name' => '정수의 나눗셈',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 17,
                                        ],
                                        [
                                            'name' => '정수의 혼합계산',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 18,
                                        ],
                                    ],
                                ],
                                [
                                    'name' => '유리수와 그 계산',
                                    'type' => 'scope',
                                    'depth' => 3,
                                    'order' => 2,
                                    'children' => [
                                        [
                                            'name' => '유리수 찾기',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 1,
                                        ],
                                        [
                                            'name' => '정수와 유리수',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 2,
                                        ],
                                        [
                                            'name' => '수를 수직선 위에 나타내기',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 3,
                                        ],
                                        [
                                            'name' => '절댓값과 대소관계',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 4,
                                        ],
                                        [
                                            'name' => '절댓값의 활용',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 5,
                                        ],
                                        [
                                            'name' => '유리수의 대소관계',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 6,
                                        ],
                                        [
                                            'name' => '유리수의 덧셈, 뺄셈',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 7,
                                        ],
                                        [
                                            'name' => '유리수의 덧셈과 뺄셈의 혼합계산',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 8,
                                        ],
                                        [
                                            'name' => '유리수의 곱셈',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 9,
                                        ],
                                        [
                                            'name' => '유리수의 곱셈에 대한 계산법칙',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 10,
                                        ],
                                        [
                                            'name' => '유리수의 거듭제곱',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 11,
                                        ],
                                        [
                                            'name' => '역수',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 12,
                                        ],
                                        [
                                            'name' => '유리수의 나눗셈',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 13,
                                        ],
                                        [
                                            'name' => '문자의 대소관계',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 14,
                                        ],
                                        [
                                            'name' => '유리수의 혼합계산',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 15,
                                        ],
                                        [
                                            'name' => '유리수의 활용',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 16,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'name' => '방정식',
                            'type' => 'scope',
                            'depth' => 2,
                            'order' => 3,
                            'children' => [
                                [
                                    'name' => '문자의 사용과 식',
                                    'type' => 'scope',
                                    'depth' => 3,
                                    'order' => 1,
                                    'children' => [
                                        [
                                            'name' => '곱셈 기호와 나눗셈 기호의 생략',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 1,
                                        ],
                                        [
                                            'name' => '문자를 사용한 식: 비율, 단위, 수',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 2,
                                        ],
                                        [
                                            'name' => '문자를 사용한 식 : 도형',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 3,
                                        ],
                                        [
                                            'name' => '문자를 사용한 식 : 가격',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 4,
                                        ],
                                        [
                                            'name' => '문자를 사용한 식 : 거리, 속력, 시간',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 5,
                                        ],
                                        [
                                            'name' => '문자를 사용한 식 : 농도',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 6,
                                        ],
                                        [
                                            'name' => '문자를 사용한 식 : 종합',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 7,
                                        ],
                                        [
                                            'name' => '식의 값 구하기',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 8,
                                        ],
                                        [
                                            'name' => '식의 값의 활용 : 식이 주어진 경우',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 9,
                                        ],
                                        [
                                            'name' => '식의 값의 활용 : 식이 주어지지 않은 경우',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 10,
                                        ],
                                        [
                                            'name' => '다항식',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 11,
                                        ],
                                        [
                                            'name' => '일차식',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 12,
                                        ],
                                        [
                                            'name' => '일차식과 수의 곱셈, 나눗셈',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 13,
                                        ],
                                        [
                                            'name' => '동류항',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 14,
                                        ],
                                        [
                                            'name' => '일차식의 덧셈과 뺄셈',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 15,
                                        ],
                                        [
                                            'name' => '괄호가 여러개인 일차식의 덧셈과 뺄셈',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 16,
                                        ],
                                        [
                                            'name' => '분수꼴인 일차식의 덧셈과 뺄셈',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 17,
                                        ],
                                        [
                                            'name' => '일차식의 덧셈과 뺄셈의 활용',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 18,
                                        ],
                                        [
                                            'name' => '문자에 일차식을 대입하기',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 19,
                                        ],
                                        [
                                            'name' => '어떤 식 구하기',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 20,
                                        ],
                                        [
                                            'name' => '바르게 계산한 식 구하기',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 21,
                                        ],
                                    ],
                                ],
                                [
                                    'name' => '일차방정식의 풀이',
                                    'type' => 'scope',
                                    'depth' => 3,
                                    'order' => 2,
                                    'children' => [
                                        [
                                            'name' => '문장을 등식으로 나타내기',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 1,
                                        ],
                                        [
                                            'name' => '방정식의 해',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 2,
                                        ],
                                        [
                                            'name' => '항등식',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 3,
                                        ],
                                        [
                                            'name' => '항등식이 되기 위한 조건',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 4,
                                        ],
                                        [
                                            'name' => '등식의 성질',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 5,
                                        ],
                                        [
                                            'name' => '등식의 성질을 이용한 방정식의 풀이',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 6,
                                        ],
                                        [
                                            'name' => '이항',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 7,
                                        ],
                                        [
                                            'name' => '일차방정식',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 8,
                                        ],
                                        [
                                            'name' => '괄호가 있는 일차방정식의 풀이',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 9,
                                        ],
                                        [
                                            'name' => '계수가 소수인 일차방정식의 풀이',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 10,
                                        ],
                                        [
                                            'name' => '계수가 분수인 일차방정식의 풀이',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 11,
                                        ],
                                        [
                                            'name' => '여러가지 일차방정식의 풀이',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 12,
                                        ],
                                        [
                                            'name' => '비례식으로 주어진 일차방정식의 풀이',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 13,
                                        ],
                                        [
                                            'name' => '일차방정식의 해가 주어진 경우',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 14,
                                        ],
                                        [
                                            'name' => '두 일차방정식의 해가 같은 경우',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 15,
                                        ],
                                        [
                                            'name' => '일차방정식의 해에 대한 조건이 주어진 경우',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 16,
                                        ],
                                        [
                                            'name' => '특수한 해를 갖는 방정식',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 17,
                                        ],
                                    ],
                                ],
                                [
                                    'name' => '일차방정식의 활용',
                                    'type' => 'scope',
                                    'depth' => 3,
                                    'order' => 3,
                                    'children' => [
                                        [
                                            'name' => '어떤 수에 대한 문제',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 1,
                                        ],
                                        [
                                            'name' => '연속하는 자연수에 대한 문제',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 2,
                                        ],
                                        [
                                            'name' => '자리의 숫자에 대한 문제',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 3,
                                        ],
                                        [
                                            'name' => '합이 일정한 문제',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 4,
                                        ],
                                        [
                                            'name' => '나이에 대한 문제',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 5,
                                        ],
                                        [
                                            'name' => '도형에 대한 문제',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 6,
                                        ],
                                        [
                                            'name' => '예금에 대한 문제',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 7,
                                        ],
                                        [
                                            'name' => '원가와 정가에 대한 문제',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 8,
                                        ],
                                        [
                                            'name' => '증가, 감소에 대한 문제',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 9,
                                        ],
                                        [
                                            'name' => '과부족에 대한 문제',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 10,
                                        ],
                                        [
                                            'name' => '비율에 대한 문제',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 11,
                                        ],
                                        [
                                            'name' => '일에 대한 문제',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 12,
                                        ],
                                        [
                                            'name' => '거리, 속력, 시간에 대한 문제',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 13,
                                        ],
                                        [
                                            'name' => '농도에 대한 문제',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 14,
                                        ],
                                        [
                                            'name' => '시계에 대한 문제',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 15,
                                        ],
                                        [
                                            'name' => '규칙이 있는 수에 대한 문제',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 16,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'name' => '그래프와 비례',
                            'type' => 'scope',
                            'depth' => 2,
                            'order' => 4,
                            'children' => [
                                [
                                    'name' => '좌표평면과 그래프',
                                    'type' => 'scope',
                                    'depth' => 3,
                                    'order' => 1,
                                    'children' => [
                                        [
                                            'name' => '순서쌍',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 1,
                                        ],
                                        [
                                            'name' => '좌표평면 위의 점의 좌표',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 2,
                                        ],
                                        [
                                            'name' => '좌표축 위의 점의 좌표',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 3,
                                        ],
                                        [
                                            'name' => '좌표평면 위의 도형의 넓이',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 4,
                                        ],
                                        [
                                            'name' => '사분면',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 5,
                                        ],
                                        [
                                            'name' => '사분면의 결정',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 6,
                                        ],
                                        [
                                            'name' => '대칭인 점의 좌표',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 7,
                                        ],
                                        [
                                            'name' => '그래프 해석하기',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 8,
                                        ],
                                        [
                                            'name' => '상황에 맞는 그래프 찾기',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 9,
                                        ],
                                        [
                                            'name' => '물통의 모양과 그래프',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 10,
                                        ],
                                    ],
                                ],
                                [
                                    'name' => '정비례와 반비례',
                                    'type' => 'scope',
                                    'depth' => 3,
                                    'order' => 2,
                                    'children' => [
                                        [
                                            'name' => '정비례 관계',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 1,
                                        ],
                                        [
                                            'name' => '정비례 관계의 식 구하기',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 2,
                                        ],
                                        [
                                            'name' => '정비례 관계의 활용',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 3,
                                        ],
                                        [
                                            'name' => '정비례 관계 y = kx의 그래프',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 4,
                                        ],
                                        [
                                            'name' => '정비례 관계 y = kx의 그래프가 지나는 점',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 5,
                                        ],
                                        [
                                            'name' => '그래프가 주어질 때 식 구하기 : 정비례 관계',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 6,
                                        ],
                                        [
                                            'name' => '정비례의 활용',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 7,
                                        ],
                                        [
                                            'name' => '반비례 관계',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 8,
                                        ],
                                        [
                                            'name' => '반비례 관계의 식 구하기',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 9,
                                        ],
                                        [
                                            'name' => '반비례 관계의 활용',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 10,
                                        ],
                                        [
                                            'name' => '반비례 관계 y = k/x의 그래프',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 11,
                                        ],
                                        [
                                            'name' => '반비례 관계 y = k/x의 그래프가 지나는 점',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 12,
                                        ],
                                        [
                                            'name' => '그래프가 주어질 때 식 구하기 : 반비례 관계',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 13,
                                        ],
                                        [
                                            'name' => '반비례 그래프의 활용',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 14,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ];
                } else if ($grade === 1 && $semester === 2) {
                    $children = [
                        [
                            'name' => '기본도형',
                            'type' => 'scope',
                            'depth' => 2, // depth 시작을 2로 설정
                            'order' => 1,
                            'children' => [
                                [
                                    'name' => '기본도형',
                                    'type' => 'scope',
                                    'depth' => 3, // depth 증가 (2 -> 3)
                                    'order' => 1,
                                    'children' => [
                                        [
                                            'name' => '교점과 교선',
                                            'type' => 'question_type',
                                            'depth' => 4, // depth 증가 (3 -> 4)
                                            'order' => 1,
                                        ],
                                        [
                                            'name' => '직선, 반직선, 선분',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 2,
                                        ],
                                        [
                                            'name' => '직선, 반직선, 선분의 개수',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 3,
                                        ],
                                        [
                                            'name' => '선분의 중점',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 4,
                                        ],
                                        [
                                            'name' => '두 점 사이의 거리',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 5,
                                        ],
                                        [
                                            'name' => '각의 크기 : 직각',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 6,
                                        ],
                                        [
                                            'name' => '각의 크기 : 평각',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 7,
                                        ],
                                        [
                                            'name' => '각의 크기의 비가 주어진 경우',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 8,
                                        ],
                                        [
                                            'name' => '각의 크기 사이의 조건이 주어진 경우',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 9,
                                        ],
                                        [
                                            'name' => '시침과 분침이 이루는 각의 크기',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 10,
                                        ],
                                        [
                                            'name' => '맞꼭지각',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 11,
                                        ],
                                        [
                                            'name' => '맞꼭지각의 쌍의 개수',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 12,
                                        ],
                                        [
                                            'name' => '수직과 수선',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 13,
                                        ]
                                    ]
                                ],
                                [
                                    'name' => '위치관계',
                                    'type' => 'scope',
                                    'depth' => 3, // depth 증가 (2 -> 3)
                                    'order' => 2,
                                    'children' => [
                                        [
                                            'name' => '점과 직선, 점과 평면의 위치관계',
                                            'type' => 'question_type',
                                            'depth' => 4, // depth 증가 (3 -> 4)
                                            'order' => 1,
                                        ],
                                        [
                                            'name' => '평면에서 두 직선의 위치관계',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 2,
                                        ],
                                        [
                                            'name' => '꼬인위치',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 3,
                                        ],
                                        [
                                            'name' => '공간에서 두 직선의 위치관계',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 4,
                                        ],
                                        [
                                            'name' => '평면이 정해질 조건',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 5,
                                        ],
                                        [
                                            'name' => '공간에서 직선과 평면의 위치관계',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 6,
                                        ],
                                        [
                                            'name' => '점과 평면 사이의 거리',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 7,
                                        ],
                                        [
                                            'name' => '공간에서 두 평면의 위치관계',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 8,
                                        ],
                                        [
                                            'name' => '일부가 잘린 입체도형에서의 위치관계',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 9,
                                        ],
                                        [
                                            'name' => '전개도가 주어진 입체도형에서의 위치관계',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 10,
                                        ],
                                        [
                                            'name' => '여러가지 위치 관계',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 11,
                                        ]
                                    ]
                                ],
                                [
                                    'name' => '평행선',
                                    'type' => 'scope',
                                    'depth' => 3, // depth 증가 (2 -> 3)
                                    'order' => 3,
                                    'children' => [
                                        [
                                            'name' => '동위각과 엇각',
                                            'type' => 'question_type',
                                            'depth' => 4, // depth 증가 (3 -> 4)
                                            'order' => 1,
                                        ],
                                        [
                                            'name' => '평행선의 성질',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 2,
                                        ],
                                        [
                                            'name' => '두 직선이 평행할 조건',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 3,
                                        ],
                                        [
                                            'name' => '두 쌍의 평행선',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 4,
                                        ],
                                        [
                                            'name' => '평행선과 삼각형 모양',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 5,
                                        ],
                                        [
                                            'name' => '평행선과 꺾인 직선',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 6,
                                        ],
                                        [
                                            'name' => '평행선에서의 활용',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 7,
                                        ],
                                        [
                                            'name' => '종이접기',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 8,
                                        ]
                                    ]
                                ],
                                [
                                    'name' => '작도와 합동',
                                    'type' => 'scope',
                                    'depth' => 3, // depth 증가 (2 -> 3)
                                    'order' => 4,
                                    'children' => [
                                        [
                                            'name' => '작도',
                                            'type' => 'question_type',
                                            'depth' => 4, // depth 증가 (3 -> 4)
                                            'order' => 1,
                                        ],
                                        [
                                            'name' => '길이가 같은 선분의 작도',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 2,
                                        ],
                                        [
                                            'name' => '크기와 같은 각의 작도',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 3,
                                        ],
                                        [
                                            'name' => '평행선의 작도',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 4,
                                        ],
                                        [
                                            'name' => '삼각형의 세 변의 길이 사이의 관계',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 5,
                                        ],
                                        [
                                            'name' => '삼각형의 작도',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 6,
                                        ],
                                        [
                                            'name' => '삼각형이 정해질 조건',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 7,
                                        ],
                                        [
                                            'name' => '도형의 합동',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 8,
                                        ],
                                        [
                                            'name' => '합동인 삼각형 찾기',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 9,
                                        ],
                                        [
                                            'name' => '두 삼각형이 합동이 되기 위해 추가로 필요한 조건',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 10,
                                        ],
                                        [
                                            'name' => '합동조건1 : SSS합동',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 11,
                                        ],
                                        [
                                            'name' => '합동조건2 : SAS합동',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 12,
                                        ],
                                        [
                                            'name' => '합동조건3 : ASA합동',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 13,
                                        ],
                                        [
                                            'name' => '삼각형의 합동의 활용 : 정삼각형',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 14,
                                        ],
                                        [
                                            'name' => '삼각형의 합동의 활용 : 정사각형',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 15,
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        [
                            'name' => '평면도형',
                            'type' => 'scope',
                            'depth' => 2,
                            'order' => 2,
                            'children' => [
                                [
                                    'name' => '다각형',
                                    'type' => 'scope',
                                    'depth' => 3,
                                    'order' => 1,
                                    'children' => [
                                        [
                                            'name' => '다각형',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 1,
                                        ],
                                        [
                                            'name' => '다각형의 내각과 외각',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 2,
                                        ],
                                        [
                                            'name' => '정다각형',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 3,
                                        ],
                                        [
                                            'name' => '삼각형의 세 내각의 크기의 합',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 4,
                                        ],
                                        [
                                            'name' => '삼각형의 내각과 외각의 관계',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 5,
                                        ],
                                        [
                                            'name' => '삼각형의 내각의 크기의 합의 활용',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 6,
                                        ],
                                        [
                                            'name' => '삼각형의 내각과 외각의 관계의 활용 : 한 내각과 한 외각의 이등분선',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 7,
                                        ],
                                        [
                                            'name' => '삼각형의 내각과 외각의 관계의 활용 : 이등변삼각형',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 8,
                                        ],
                                        [
                                            'name' => '삼각형의 내각과 외각의 관계의 활용 : 별모양',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 9,
                                        ],
                                        [
                                            'name' => '다각형의 한 꼭짓점에서 그을 수 있는 대각선의 개수',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 10,
                                        ],
                                        [
                                            'name' => '다각형의 대각선의 개수',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 11,
                                        ],
                                        [
                                            'name' => '다각형의 내각의 크기의 합',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 12,
                                        ],
                                        [
                                            'name' => '다각형의 내각의 크기',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 13,
                                        ],
                                        [
                                            'name' => '다각형의 외각의 크기의 합',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 14,
                                        ],
                                        [
                                            'name' => '다각형의 내각의 크기의 합의 활용',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 15,
                                        ],
                                        [
                                            'name' => '다각형의 외각의 크기의 합의 활용',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 16,
                                        ],
                                        [
                                            'name' => '정다각형의 한 내각과 한 외각의 크기',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 17,
                                        ],
                                        [
                                            'name' => '정다각형의 한 내각의 크기의 활용',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 18,
                                        ],
                                        [
                                            'name' => '정다각형의 한 외각의 크기의 활용',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 19,
                                        ]
                                    ]
                                ],
                                [
                                    'name' => '원과 부채꼴',
                                    'type' => 'scope',
                                    'depth' => 3,
                                    'order' => 2,
                                    'children' => [
                                        [
                                            'name' => '원과 부채꼴',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 1,
                                        ],
                                        [
                                            'name' => '중심각의 크기와 호의 길이',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 2,
                                        ],
                                        [
                                            'name' => '중심각의 크기 구하기 : 호의 길이의 비가 주어진 경우',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 3,
                                        ],
                                        [
                                            'name' => '중심각의 크기 구하기 : 평행선이 주어진 경우',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 4,
                                        ],
                                        [
                                            'name' => '호의 길이 구하기 : 부채꼴이 주어지지 않은 경우',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 5,
                                        ],
                                        [
                                            'name' => '호의 길이 구하기 : 지름과 현의 연장선',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 6,
                                        ],
                                        [
                                            'name' => '중심각의 크기와 부채꼴의 넓이',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 7,
                                        ],
                                        [
                                            'name' => '중심각의 크기와 현의 길이',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 8,
                                        ],
                                        [
                                            'name' => '중심각의 크기에 정비례 하는 것',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 9,
                                        ],
                                        [
                                            'name' => '원의 둘레의 길이와 넓이',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 10,
                                        ],
                                        [
                                            'name' => '부채꼴의 호의 길이와 넓이',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 11,
                                        ],
                                        [
                                            'name' => '색칠한 부분의 둘레의 길이 구하기',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 12,
                                        ],
                                        [
                                            'name' => '색칠한 부분의 넓이 구하기',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 13,
                                        ],
                                        [
                                            'name' => '색칠한 부분의 넓이가 같은 경우',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 14,
                                        ],
                                        [
                                            'name' => '원을 묶은 끈의 길이',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 15,
                                        ],
                                        [
                                            'name' => '원이 지나간 자리의 넓이',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 16,
                                        ],
                                        [
                                            'name' => '도형을 회전시켰을 때 점이 움직인 거리',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 17,
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        [
                            'name' => '입체도형',
                            'type' => 'scope',
                            'depth' => 2,
                            'order' => 3,
                            'children' => [
                                [
                                    'name' => '다면체',
                                    'type' => 'scope',
                                    'depth' => 3,
                                    'order' => 1,
                                    'children' => [
                                        [
                                            'name' => '다면체',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 1,
                                        ],
                                        [
                                            'name' => '다면체의 면, 모서리, 꼭짓점의 개수',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 2,
                                        ],
                                        [
                                            'name' => '다면체의 면, 모서리, 꼭짓점의 개수의 활용',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 3,
                                        ],
                                        [
                                            'name' => '다면체의 옆면의 모양',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 4,
                                        ],
                                        [
                                            'name' => '다면체의 이해',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 5,
                                        ],
                                        [
                                            'name' => '조건을 만족시키는 다면체 찾기',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 6,
                                        ],
                                        [
                                            'name' => '정다면체의 이해',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 7,
                                        ],
                                        [
                                            'name' => '조건을 만족시키는 정다면체 찾기',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 8,
                                        ],
                                        [
                                            'name' => '정다면체의 면, 모서리, 꼭짓점의 개수',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 9,
                                        ],
                                        [
                                            'name' => '정다면체의 전개도',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 10,
                                        ],
                                        [
                                            'name' => '정다면체의 면, 모서리, 꼭짓점의 개수 : 전개도',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 11,
                                        ],
                                        [
                                            'name' => '정다면체의 각 면의 한가운데 점을 연결하여 만든 입체도형',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 12,
                                        ],
                                        [
                                            'name' => '정다면체의 단면',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 13,
                                        ]
                                    ]
                                ],
                                [
                                    'name' => '회전체',
                                    'type' => 'scope',
                                    'depth' => 3,
                                    'order' => 2,
                                    'children' => [
                                        [
                                            'name' => '회전체',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 1,
                                        ],
                                        [
                                            'name' => '평면도형과 회전체',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 2,
                                        ],
                                        [
                                            'name' => '회전축',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 3,
                                        ],
                                        [
                                            'name' => '회전체의 단면의 모양',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 4,
                                        ],
                                        [
                                            'name' => '회전체의 단면의 넓이와 둘레의 길이',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 5,
                                        ],
                                        [
                                            'name' => '회전체의 전개도',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 6,
                                        ],
                                        [
                                            'name' => '원기둥의 전개도의 성질',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 7,
                                        ],
                                        [
                                            'name' => '원뿔, 원뿔대의 전개도의 성질',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 8,
                                        ],
                                        [
                                            'name' => '회전체의 이해',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 9,
                                        ]
                                    ]
                                ],
                                [
                                    'name' => '입체도형의 겉넓이와 부피',
                                    'type' => 'scope',
                                    'depth' => 3,
                                    'order' => 3,
                                    'children' => [
                                        [
                                            'name' => '각기둥의 겉넓이',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 1,
                                        ],
                                        [
                                            'name' => '원기둥의 겉넓이',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 2,
                                        ],
                                        [
                                            'name' => '각기둥의 부피',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 3,
                                        ],
                                        [
                                            'name' => '원기둥의 부피',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 4,
                                        ],
                                        [
                                            'name' => '밑면이 부채꼴인 기둥의 겉넓이와 부피',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 5,
                                        ],
                                        [
                                            'name' => '구멍이 뚫린 기둥의 겉넓이와 부피',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 6,
                                        ],
                                        [
                                            'name' => '일부분을 잘라 낸 기둥의 겉넓이와 부피',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 7,
                                        ],
                                        [
                                            'name' => '회전체의 겉넓이와 부피',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 8,
                                        ],
                                        [
                                            'name' => '각뿔의 겉넓이',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 9,
                                        ],
                                        [
                                            'name' => '원뿔의 겉넓이',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 10,
                                        ],
                                        [
                                            'name' => '원뿔의 겉넓이 : 전개도',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 11,
                                        ],
                                        [
                                            'name' => '뿔대의 겉넓이',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 12,
                                        ],
                                        [
                                            'name' => '각뿔의 부피',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 13,
                                        ],
                                        [
                                            'name' => '직육면체의 내부에 있는 각뿔의 부피',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 14,
                                        ],
                                        [
                                            'name' => '각기둥에서 잘라 낸 각뿔의 부피',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 15,
                                        ],
                                        [
                                            'name' => '그릇에 담긴 물의 부피',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 16,
                                        ],
                                        [
                                            'name' => '원뿔의 부피',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 17,
                                        ],
                                        [
                                            'name' => '뿔대의 부피',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 18,
                                        ],
                                        [
                                            'name' => '회전체의 겉넓이와 부피 : 원뿔, 원뿔대',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 19,
                                        ],
                                        [
                                            'name' => '구의 겉넓이',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 20,
                                        ],
                                        [
                                            'name' => '구의 부피',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 21,
                                        ],
                                        [
                                            'name' => '회전체의 겉넓이와 부피 : 구',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 22,
                                        ],
                                        [
                                            'name' => '원기둥에 꼭 맞게 들어가는 구, 원뿔',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 23,
                                        ],
                                        [
                                            'name' => '입체도형에 꼭 맞게 들어가는 입체도형',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 24,
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        [
                            'name' => '통계',
                            'type' => 'scope',
                            'depth' => 2,
                            'order' => 4,
                            'children' => [
                                [
                                    'name' => '도수분포표',
                                    'type' => 'scope',
                                    'depth' => 3,
                                    'order' => 1,
                                    'children' => [
                                        [
                                            'name' => '평균',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 1,
                                        ],
                                        [
                                            'name' => '평균의 활용',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 2,
                                        ],
                                        [
                                            'name' => '중앙값',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 3,
                                        ],
                                        [
                                            'name' => '최빈값',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 4,
                                        ],
                                        [
                                            'name' => '적절한 대표값 찾기',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 5,
                                        ],
                                        [
                                            'name' => '대표값이 주어질 때 변량 구하기',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 6,
                                        ],
                                        [
                                            'name' => '줄기와 잎 그림',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 7,
                                        ],
                                        [
                                            'name' => '도수분포표',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 8,
                                        ],
                                        [
                                            'name' => '특정 계급의 백분율',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 9,
                                        ],
                                        [
                                            'name' => '히스토그램',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 10,
                                        ],
                                        [
                                            'name' => '히스토그램에서 직사각형의 넓이',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 11,
                                        ],
                                        [
                                            'name' => '도수분포다각형',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 12,
                                        ],
                                        [
                                            'name' => '찢어진 히스토그램과 도수분포다각형',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 13,
                                        ],
                                        [
                                            'name' => '두 도수분포다각형의 비교',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 14,
                                        ]
                                    ]
                                ],
                                [
                                    'name' => '상대도수',
                                    'type' => 'scope',
                                    'depth' => 3,
                                    'order' => 2,
                                    'children' => [
                                        [
                                            'name' => '상대도수',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 1,
                                        ],
                                        [
                                            'name' => '상대도수의 분포표',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 2,
                                        ],
                                        [
                                            'name' => '찢어진 상대도수의 분포표',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 3,
                                        ],
                                        [
                                            'name' => '상대도수의 분포를 나타낸 그래프 : 도수의 총합이 주어진 경우',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 4,
                                        ],
                                        [
                                            'name' => '상대도수의 분포를 나타낸 그래프 : 도수의 총합이 주어지지 않은 경우',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 5,
                                        ],
                                        [
                                            'name' => '상대도수의 분포를 나타낸 그래프가 찢어진 경우',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 6,
                                        ],
                                        [
                                            'name' => '도수의 총합이 다른 두 자료의 상대도수',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 7,
                                        ],
                                        [
                                            'name' => '도수의 총합이 다른 두 자료의 상대도수의 비',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 8,
                                        ],
                                        [
                                            'name' => '도수의 총합이 다른 두 자료의 비교',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 9,
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ];
                } else if ($grade === 2 && $semester === 1) {
                    $children = [
                        [
                            'name' => '유리수와 순환소수',
                            'type' => 'scope',
                            'depth' => 2,
                            'order' => 1,
                            'children' => [
                                [
                                    'name' => '유리수와 순환소수',
                                    'type' => 'scope',
                                    'depth' => 3,
                                    'order' => 1,
                                    'children' => [
                                        [
                                            'name' => '거듭제곱을 이용한 소수로 나타내기',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 1,
                                        ],
                                        [
                                            'name' => '유한소수 판별하기',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 2,
                                        ],
                                        [
                                            'name' => '순환소수의 표현',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 3,
                                        ],
                                        [
                                            'name' => '유한소수가 되도록 하는 미지수의 값 구하기',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 4,
                                        ],
                                        [
                                            'name' => '유한소수가 되도록 하는 수를 찾고 기약분수로 나타내기',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 5,
                                        ],
                                        [
                                            'name' => '순환마디',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 6,
                                        ],
                                        [
                                            'name' => '순환소수의 소숫점 아래 자릿수 구하기',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 7,
                                        ],
                                        [
                                            'name' => '순환소수로 나타내어지는 분수',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 8,
                                        ],
                                        [
                                            'name' => '순환소수와 부등식',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 9,
                                        ],
                                        [
                                            'name' => '순환소수의 연산',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 10,
                                        ],
                                        [
                                            'name' => '순환소수의 활용',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 11,
                                        ],
                                        [
                                            'name' => '소수의 이해',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 12,
                                        ],
                                    ]
                                ]
                            ]
                        ],
                        [
                            'name' => '식의 계산',
                            'type' => 'scope',
                            'depth' => 2,
                            'order' => 2,
                            'children' => [
                                [
                                    'name' => '단항식의 계산',
                                    'type' => 'scope',
                                    'depth' => 3,
                                    'order' => 1,
                                    'children' => [
                                        [
                                            'name' => '지수법칙',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 1,
                                        ],
                                        [
                                            'name' => '지수법칙을 이용한 대소관계',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 2,
                                        ],
                                        [
                                            'name' => '지수법칙의 활용',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 3,
                                        ],
                                        [
                                            'name' => '단항식의 곱셈, 나눗셈',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 4,
                                        ],
                                        [
                                            'name' => '단항식의 활용',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 5,
                                        ],
                                    ]
                                ],
                                [
                                    'name' => '다항식의 계산',
                                    'type' => 'scope',
                                    'depth' => 3,
                                    'order' => 2,
                                    'children' => [
                                        [
                                            'name' => '다항식의 덧셈과 뺄셈',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 1,
                                        ],
                                        [
                                            'name' => '여러가지 괄호가 있는 식의 계산',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 2,
                                        ],
                                        [
                                            'name' => '단항식의 다항식의 곱셈과 나눗셈',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 3,
                                        ],
                                        [
                                            'name' => '사칙연산의 혼합계산',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 4,
                                        ],
                                        [
                                            'name' => '다항식의 연산의 활용',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 5,
                                        ],
                                        [
                                            'name' => '식의 값 구하기',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 6,
                                        ],
                                        [
                                            'name' => '계수 구하기',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 7,
                                        ],
                                        [
                                            'name' => '곱셈공식',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 8,
                                        ],
                                        [
                                            'name' => '곱셈공식의 활용',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 9,
                                        ],
                                        [
                                            'name' => '곱셈공식을 이용한 수의계산',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 10,
                                        ],
                                        [
                                            'name' => '식의 값 구하기',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 11,
                                        ],
                                        [
                                            'name' => '식의 대입',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 12,
                                        ],
                                        [
                                            'name' => '등식을 변형하여 다른 식에 대입하기',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 13,
                                        ],
                                        [
                                            'name' => '등식의 변형의 활용',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 14,
                                        ],
                                    ]
                                ]
                            ]
                        ],
                        [
                            'name' => '일차부등식',
                            'type' => 'scope',
                            'depth' => 2,
                            'order' => 3,
                            'children' => [
                                [
                                    'name' => '일차부등식',
                                    'type' => 'scope',
                                    'depth' => 3,
                                    'order' => 1,
                                    'children' => [
                                        [
                                            'name' => '부등식의 뜻',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 1,
                                        ],
                                        [
                                            'name' => '부등식의 참, 거짓',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 2,
                                        ],
                                        [
                                            'name' => '부등식의 해',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 3,
                                        ],
                                        [
                                            'name' => '부등식의 성질',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 4,
                                        ],
                                        [
                                            'name' => '부등식의 해를 수직선 위에 나타내기',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 5,
                                        ],
                                        [
                                            'name' => '분수, 소수가 포함된 일차부등식 풀이',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 6,
                                        ],
                                        [
                                            'name' => '미지수를 포함한 일차부등식',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 7,
                                        ],
                                        [
                                            'name' => '해가 주어진 일차부등식',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 8,
                                        ],
                                        [
                                            'name' => '해가 존재하지 않을 조건',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 9,
                                        ],
                                    ]
                                ],
                                [
                                    'name' => '일차부등식의 활용',
                                    'type' => 'scope',
                                    'depth' => 3,
                                    'order' => 2,
                                    'children' => [
                                        [
                                            'name' => '부등식의 활용(1) – 수에 대한 문제',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 1,
                                        ],
                                        [
                                            'name' => '부등식의 활용(2) – 최대 개수에 대한 문제',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 2,
                                        ],
                                        [
                                            'name' => '부등식의 활용(3) – 금액에 대한 문제',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 3,
                                        ],
                                        [
                                            'name' => '부등식의 활용(4) – 도형에 대한 문제',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 4,
                                        ],
                                        [
                                            'name' => '부등식의 활용(5) – 거리,속력',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 5,
                                        ],
                                        [
                                            'name' => '부등식의 활용(6) – 소금물 농도',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 6,
                                        ],
                                    ]
                                ]
                            ]
                        ],
                        [
                            'name' => '연립일차방정식',
                            'type' => 'scope',
                            'depth' => 2,
                            'order' => 4,
                            'children' => [
                                [
                                    'name' => '연립일차방정식',
                                    'type' => 'scope',
                                    'depth' => 3,
                                    'order' => 1,
                                    'children' => [
                                        [
                                            'name' => '미지수가 2개인 일차방정식의 해',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 1,
                                        ],
                                        [
                                            'name' => '연립일차방정식',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 2,
                                        ],
                                        [
                                            'name' => '연립일차방정식의 미지수 구하기',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 3,
                                        ],
                                        [
                                            'name' => '조건이 주어진 연립일차방정식',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 4,
                                        ],
                                        [
                                            'name' => '분수,소수로 이루어진 연립일차방정식',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 5,
                                        ],
                                        [
                                            'name' => '     꼴의 연립일차방정식',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 6,
                                        ],
                                        [
                                            'name' => '해가 무수히 많다',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 7,
                                        ],
                                        [
                                            'name' => '해가 없다',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 8,
                                        ],
                                    ]
                                ],
                                [
                                    'name' => '연립방정식의 활용',
                                    'type' => 'scope',
                                    'depth' => 3,
                                    'order' => 2,
                                    'children' => [
                                        [
                                            'name' => '연립방정식의 활용(1) - 몫, 나머지',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 1,
                                        ],
                                        [
                                            'name' => '연립방정식의 활용(2) - 자연수',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 2,
                                        ],
                                        [
                                            'name' => '연립방정식의 활용(3) - 나이',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 3,
                                        ],
                                        [
                                            'name' => '연립방정식의 활용(4) - 비율',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 4,
                                        ],
                                        [
                                            'name' => '연립방정식의 활용(5) - 길이',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 5,
                                        ],
                                        [
                                            'name' => '연립방정식의 활용(6) - 계단',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 6,
                                        ],
                                        [
                                            'name' => '연립방정식의 활용(7) - 증가,감소율',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 7,
                                        ],
                                        [
                                            'name' => '연립방정식의 활용(8) - 이익,할인',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 8,
                                        ],
                                        [
                                            'name' => '연립방정식의 활용(9) - 일과 양',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 9,
                                        ],
                                        [
                                            'name' => '연립방정식의 활용(10) - 속력과 거리',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 10,
                                        ],
                                        [
                                            'name' => '연립방정식의 활용(11) - 소금물, 소금, 농도',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 11,
                                        ],
                                    ]
                                ]
                            ]
                        ],
                        [
                            'name' => '일차함수',
                            'type' => 'scope',
                            'depth' => 2,
                            'order' => 5,
                            'children' => [
                                [
                                    'name' => '일차함수',
                                    'type' => 'scope',
                                    'depth' => 3,
                                    'order' => 1,
                                    'children' => [
                                        [
                                            'name' => '일차함수 찾기',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 1,
                                        ],
                                        [
                                            'name' => '일차함수의 함숫값',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 2,
                                        ],
                                        [
                                            'name' => '일차함수의 그래프 위의 점',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 3,
                                        ],
                                        [
                                            'name' => '일차함수의 평행이동',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 4,
                                        ],
                                        [
                                            'name' => '일차함수 그래프의 절편, 절편, 기울기',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 5,
                                        ],
                                        [
                                            'name' => '세 점이 한직선 위에 있을 조건',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 6,
                                        ],
                                        [
                                            'name' => '일차함수 그래프',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 7,
                                        ],
                                        [
                                            'name' => '일차함수     의 그래프의 성질',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 8,
                                        ],
                                        [
                                            'name' => '일차함수     의 그래프의 절편, 절편, 기울기',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 9,
                                        ],
                                        [
                                            'name' => '일차함수 그래프의 일치, 평행',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 10,
                                        ],
                                        [
                                            'name' => '일차함수 구하기',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 11,
                                        ],
                                        [
                                            'name' => '일차함수 위의 점 구하기',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 12,
                                        ],
                                        [
                                            'name' => '일차방정식의 미지수의 값 구하기',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 13,
                                        ],
                                        [
                                            'name' => '일차함수의 그래프를 이용한 미지수 구하기',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 14,
                                        ],
                                        [
                                            'name' => '축에 평행한 직선의 방정식',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 15,
                                        ],
                                        [
                                            'name' => '축에 평행한 직선의 미지수 결정',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 16,
                                        ],
                                        [
                                            'name' => '한 점에서 만나는 세 직선',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 17,
                                        ],
                                        [
                                            'name' => '교점을 지나는 직선의 방정식',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 18,
                                        ],
                                        [
                                            'name' => '일차함수 그래프의 활용',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 19,
                                        ],
                                    ]
                                ]
                            ]
                        ]
                    ];
                } else if ($grade === 2 && $semester === 2) {
                    $children = [
                        [
                            'name' => '삼각형과 사각형의 성질',
                            'type' => 'scope',
                            'depth' => 2,
                            'order' => 1,
                            'children' => [
                                [
                                    'name' => '삼각형의 성질',
                                    'type' => 'scope',
                                    'depth' => 3,
                                    'order' => 1,
                                    'children' => [
                                        [
                                            'name' => '이등변삼각형의 성질의 확인',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 1,
                                        ],
                                        [
                                            'name' => '이등변삼각형의 성질',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 2,
                                        ],
                                        [
                                            'name' => '이등변삼각형의 성질-이웃한 이등변삼각형',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 3,
                                        ],
                                        [
                                            'name' => '이등변삼각형의 성질-각의 이등분선',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 4,
                                        ],
                                        [
                                            'name' => '이등변삼각형이 되는 조건',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 5,
                                        ],
                                        [
                                            'name' => '이등변삼각형이 되는 조건',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 6,
                                        ],
                                        [
                                            'name' => '이등변삼각형에서 합동인 삼각형 찾기',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 7,
                                        ],
                                        [
                                            'name' => '이등변삼각형의 성질',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 8,
                                        ],
                                        [
                                            'name' => '직각삼각형의 합동조건',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 9,
                                        ],
                                        [
                                            'name' => '직각삼각형의 합동조건 - RHS합동',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 10,
                                        ],
                                        [
                                            'name' => '직각삼각형의 합동조건 – RHA합동',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 11,
                                        ],
                                        [
                                            'name' => '각의 이등분선의 성질',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 12,
                                        ],
                                        [
                                            'name' => '삼각형의 외심',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 13,
                                        ],
                                        [
                                            'name' => '직각삼각형의 외심',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 14,
                                        ],
                                        [
                                            'name' => '둔각삼각형의 외심',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 15,
                                        ],
                                        [
                                            'name' => '삼각형의 외심의 활용',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 16,
                                        ],
                                        [
                                            'name' => '삼각형의 내심',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 17,
                                        ],
                                        [
                                            'name' => '삼각형의 내심의 활용',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 18,
                                        ],
                                        [
                                            'name' => '삼각형의 내심과 외심',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 19,
                                        ],
                                        [
                                            'name' => '삼각형의 내접원과 접선',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 20,
                                        ],
                                        [
                                            'name' => '삼각형의 내심과 평행선',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 21,
                                        ],
                                        [
                                            'name' => '직각삼각형의 외접원과 내접원',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 22,
                                        ],
                                    ]
                                ],
                                [
                                    'name' => '사각형의 성질',
                                    'type' => 'scope',
                                    'depth' => 3,
                                    'order' => 2,
                                    'children' => [
                                        [
                                            'name' => '평행사변형의 뜻',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 1,
                                        ],
                                        [
                                            'name' => '평행사변형의 성질',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 2,
                                        ],
                                        [
                                            'name' => '평행사변형이 될 조건',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 3,
                                        ],
                                        [
                                            'name' => '평행사변형의 활용',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 4,
                                        ],
                                        [
                                            'name' => '직사각형의 뜻',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 5,
                                        ],
                                        [
                                            'name' => '직사각형의 성질',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 6,
                                        ],
                                        [
                                            'name' => '직사각형이 될 조건',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 7,
                                        ],
                                        [
                                            'name' => '마름모의 뜻',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 8,
                                        ],
                                        [
                                            'name' => '마름모의 성질',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 9,
                                        ],
                                        [
                                            'name' => '마름모가 될 조건',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 10,
                                        ],
                                        [
                                            'name' => '정사각형의 뜻',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 11,
                                        ],
                                        [
                                            'name' => '정사각형의 성질',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 12,
                                        ],
                                        [
                                            'name' => '정사각형이 될 조건',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 13,
                                        ],
                                        [
                                            'name' => '등변사다리꼴의 뜻',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 14,
                                        ],
                                        [
                                            'name' => '등변사다리꼴의 성질',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 15,
                                        ],
                                        [
                                            'name' => '등변사다리꼴의 활용',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 16,
                                        ],
                                        [
                                            'name' => '여러가지 사각형',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 17,
                                        ],
                                        [
                                            'name' => '여러가지 사각형의 관계',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 18,
                                        ],
                                        [
                                            'name' => '여러가지 사각형의 대각선의 성질',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 19,
                                        ],
                                        [
                                            'name' => '평행선과 삼각형의 넓이',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 20,
                                        ],
                                        [
                                            'name' => '새로운 사각형이 평행사변형이 될 조건',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 21,
                                        ],
                                        [
                                            'name' => '평행사변형과 넓이',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 22,
                                        ],
                                        [
                                            'name' => '사각형의 각 변의 중점을 연결하여 만든 사각형',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 23,
                                        ],
                                        [
                                            'name' => '높이가 같은 두 삼각형의 넓이',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 24,
                                        ],
                                        [
                                            'name' => '평행사변형에서 높이가 같은 삼각형의 넓이',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 25,
                                        ],
                                        [
                                            'name' => '사다리꼴에서 높이가 같은 삼각형의 넓이',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 26,
                                        ],
                                    ]
                                ]
                            ]
                        ],
                        [
                            'name' => '도형의 닮음',
                            'type' => 'scope',
                            'depth' => 2,
                            'order' => 2,
                            'children' => [
                                [
                                    'name' => '도형의 닮음',
                                    'type' => 'scope',
                                    'depth' => 3,
                                    'order' => 1,
                                    'children' => [
                                        [
                                            'name' => '닮은 도형',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 1,
                                        ],
                                        [
                                            'name' => '항상 닮은 도형',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 2,
                                        ],
                                        [
                                            'name' => '평면도형에서의 닮음의 성질',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 3,
                                        ],
                                        [
                                            'name' => '평면도형에서의 닮음비의 활용',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 4,
                                        ],
                                        [
                                            'name' => '입체도형에서의 닮음의 성질',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 5,
                                        ],
                                        [
                                            'name' => '입체도형에서의 닮음비의 응용 –원기둥',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 6,
                                        ],
                                        [
                                            'name' => '입체도형에서의 닮음비의 응용 –원뿔',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 7,
                                        ],
                                        [
                                            'name' => '삼각형의 닮음 조건',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 8,
                                        ],
                                        [
                                            'name' => '삼각형의 닮음 조건 – SAS 닮음',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 9,
                                        ],
                                        [
                                            'name' => '삼각형의 닮음 조건 – AA 닮음',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 10,
                                        ],
                                        [
                                            'name' => '삼각형의 닮음의 응용',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 11,
                                        ],
                                        [
                                            'name' => '직각삼각형의 닮음 – 공통인 각이 있는 경우',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 12,
                                        ],
                                        [
                                            'name' => '직각삼각형의 닮음 – 한 예각의 크기가 같은 경우',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 13,
                                        ],
                                        [
                                            'name' => '직각삼각형의 닮음의 응용',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 14,
                                        ],
                                        [
                                            'name' => '접은 도형에서의 닮은 삼각형',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 15,
                                        ],
                                    ]
                                ],
                                [
                                    'name' => '닮음의 활용',
                                    'type' => 'scope',
                                    'depth' => 3,
                                    'order' => 2,
                                    'children' => [
                                        [
                                            'name' => '평행선에 의하여 생기는 선분의 길이의 비',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 1,
                                        ],
                                        [
                                            'name' => '평행선에 의하여 생기는 선분의 길이의 비의 활용',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 2,
                                        ],
                                        [
                                            'name' => '평행선 찾기',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 3,
                                        ],
                                        [
                                            'name' => '삼각형의 내각의 이등분선',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 4,
                                        ],
                                        [
                                            'name' => '삼각형의 내각의 이등분선과 넓이',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 5,
                                        ],
                                        [
                                            'name' => '삼각형의 외각의 이등분선',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 6,
                                        ],
                                        [
                                            'name' => '평행선 사이의 선분의 길이의 비',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 7,
                                        ],
                                        [
                                            'name' => '사다리꼴에서 평행선과 선분의 길이의 비 – 보조선 이용',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 8,
                                        ],
                                        [
                                            'name' => '사다리꼴에서 평행선과 한 대각선',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 9,
                                        ],
                                        [
                                            'name' => '사다리꼴에서 평행선과 두 대각선',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 10,
                                        ],
                                        [
                                            'name' => '삼각형의 두 변의 중점을 연결한 선분의 성질',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 11,
                                        ],
                                        [
                                            'name' => '삼각형의 두 변의 중점을 연결한 선분의 성질 활용',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 12,
                                        ],
                                        [
                                            'name' => '삼각형의 세 변의 중점을 연결한 삼각형',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 13,
                                        ],
                                        [
                                            'name' => '삼각형의 네 변의 중점을 연결한 사각형',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 14,
                                        ],
                                        [
                                            'name' => '사다리꼴에서 두 변의 중점을 연결한 선분의 성질',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 15,
                                        ],
                                        [
                                            'name' => '삼각형의 중선의 성질',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 16,
                                        ],
                                        [
                                            'name' => '삼각형의 무게중심의 성질',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 17,
                                        ],
                                        [
                                            'name' => '삼각형의 무게중심의 응용',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 18,
                                        ],
                                        [
                                            'name' => '삼각형의 무게중심과 넓이',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 19,
                                        ],
                                        [
                                            'name' => '평행사변형에서 삼각형의 무게중심의 응용 – 길이',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 20,
                                        ],
                                        [
                                            'name' => '평행사변형에서 삼각형의 무게중심의 응용 – 넓이',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 21,
                                        ],
                                        [
                                            'name' => '닮은 두 평면도형의 넓이의 비',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 22,
                                        ],
                                        [
                                            'name' => '닮은 두 평면도형의 넓이의 비의 활용',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 23,
                                        ],
                                        [
                                            'name' => '닮은 두 입체도형의 겉넓이의 비',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 24,
                                        ],
                                        [
                                            'name' => '닮은 두 입체도형의 겉넓이의 비의 활용',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 25,
                                        ],
                                        [
                                            'name' => '닮은 두 입체도형의 부피의 비',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 26,
                                        ],
                                        [
                                            'name' => '닮은 두 입체도형의 부피의 비의 활용',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 27,
                                        ],
                                        [
                                            'name' => '닮음의 활용',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 28,
                                        ],
                                        [
                                            'name' => '축도와 축척',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 29,
                                        ],
                                        [
                                            'name' => '평행선 사이의 선분의 길이의 비의 응용',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 30,
                                        ],
                                    ]
                                ]
                            ]
                        ],
                        [
                            'name' => '피타고라스 정리',
                            'type' => 'scope',
                            'depth' => 2,
                            'order' => 3,
                            'children' => [
                                [
                                    'name' => '피타고라스',
                                    'type' => 'scope',
                                    'depth' => 3,
                                    'order' => 1,
                                    'children' => [
                                        [
                                            'name' => '피타고라스 정리를 이용하여 변의 길이',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 1,
                                        ],
                                        [
                                            'name' => '삼각형에서 피타고라스 정리의 이용',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 2,
                                        ],
                                        [
                                            'name' => '직사각형의 대각선의 길이',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 3,
                                        ],
                                        [
                                            'name' => '사각형에서 피타고라스 정리의 이용',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 4,
                                        ],
                                        [
                                            'name' => '직각삼각형의 닮음을 이용한 성질',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 5,
                                        ],
                                        [
                                            'name' => '직각삼각형의 넓이를 이용한 성질',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 6,
                                        ],
                                        [
                                            'name' => '피타고라스 정리의 설명(1) - 유클리드',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 7,
                                        ],
                                        [
                                            'name' => '피타고라스 정리의 설명(1) - 피타고라스',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 8,
                                        ],
                                        [
                                            'name' => '직각삼각형이 될 조건',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 9,
                                        ],
                                        [
                                            'name' => '피타고라스 정리를 이용한 직각삼각형의 성질',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 10,
                                        ],
                                        [
                                            'name' => '두 대각선이 직교하는 사각형의 성질',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 11,
                                        ],
                                        [
                                            'name' => '피타고라스 정리를 이용한 직사각형의 성질',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 12,
                                        ],
                                        [
                                            'name' => '직각삼각형에서의 세 반원 사이의 관계',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 13,
                                        ],
                                        [
                                            'name' => '히포크라테스의 원의 넓이',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 14,
                                        ],
                                        [
                                            'name' => '이등변삼각형의 높이와 넓이',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 15,
                                        ],
                                        [
                                            'name' => '종이접기',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 16,
                                        ],
                                        [
                                            'name' => '세 변의 길이에 따른 삼각형의 종류',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 17,
                                        ],
                                        [
                                            'name' => '삼각형의 각의 크기에 따른 변의 길이',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 18,
                                        ],
                                    ]
                                ]
                            ]
                        ],
                        [
                            'name' => '확률',
                            'type' => 'scope',
                            'depth' => 2,
                            'order' => 4,
                            'children' => [
                                [
                                    'name' => '경우의수',
                                    'type' => 'scope',
                                    'depth' => 3,
                                    'order' => 1,
                                    'children' => [
                                        [
                                            'name' => '경우의수',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 1,
                                        ],
                                        [
                                            'name' => '돈을 지불하는 경우의 수',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 2,
                                        ],
                                        [
                                            'name' => '경우의 수의 합',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 3,
                                        ],
                                        [
                                            'name' => '경우의 수의 곱',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 4,
                                        ],
                                        [
                                            'name' => '최단거리',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 5,
                                        ],
                                        [
                                            'name' => '색칠하는 경우의수',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 6,
                                        ],
                                        [
                                            'name' => '일렬로 세우는 경우의 수',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 7,
                                        ],
                                        [
                                            'name' => '특정한 사람의 자리를 고정하여 일렬로 세우는 경우의 수',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 8,
                                        ],
                                        [
                                            'name' => '이웃하여 일렬로 세우는 경우의 수',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 9,
                                        ],
                                        [
                                            'name' => '자연수의 개수',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 10,
                                        ],
                                        [
                                            'name' => '대표를 뽑는 경우의 수 – 자격이 같을 경우',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 11,
                                        ],
                                        [
                                            'name' => '대표를 뽑는 경우의 수 – 자격이 다를 경우',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 12,
                                        ],
                                        [
                                            'name' => '방정식, 부등식에서의 경우의수',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 13,
                                        ],
                                        [
                                            'name' => '선분 또는 삼각형의 개수',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 14,
                                        ],
                                    ]
                                ],
                                [
                                    'name' => '확률',
                                    'type' => 'scope',
                                    'depth' => 3,
                                    'order' => 2,
                                    'children' => [
                                        [
                                            'name' => '확률의 뜻',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 1,
                                        ],
                                        [
                                            'name' => '확률의 기본성질',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 2,
                                        ],
                                        [
                                            'name' => '어떤 사건이 일어나지 않을 경우',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 3,
                                        ],
                                        [
                                            'name' => '적어도 ~일 확률',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 4,
                                        ],
                                        [
                                            'name' => '확률의 덧셈',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 5,
                                        ],
                                        [
                                            'name' => '확률의 곱셈',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 6,
                                        ],
                                        [
                                            'name' => '어떤 사건이 일어나지 않을 확률',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 7,
                                        ],
                                        [
                                            'name' => '연속하여 꺼내는 경우의 확률 – 꺼낸 것을 다시 넣을 때',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 8,
                                        ],
                                        [
                                            'name' => '연속하여 꺼내는 경우의 확률 – 꺼낸 것을 다시 넣지 않을 때',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 9,
                                        ],
                                        [
                                            'name' => '시험에 합격할 확률(문제를 맞힐 확률)',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 10,
                                        ],
                                        [
                                            'name' => '두 사람이 만날 확률',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 11,
                                        ],
                                        [
                                            'name' => '명중률에 대한 확률',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 12,
                                        ],
                                        [
                                            'name' => '방정식, 부등식에서의 확률',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 13,
                                        ],
                                        [
                                            'name' => '도형 위를 움직이는 점의 위치에 대한 확률',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 14,
                                        ],
                                        [
                                            'name' => '날씨에 대한 확률',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 15,
                                        ],
                                        [
                                            'name' => '승패에 대한 확률',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 16,
                                        ],
                                    ]
                                ]
                            ]
                        ]
                    ];
                } else if ($grade === 3 && $semester === 1) {
                    $children = [
                        [
                            'name' => '제곱근과 실수',
                            'type' => 'scope',
                            'depth' => 2,
                            'order' => 1,
                            'children' => [
                                [
                                    'name' => '제곱근과 실수',
                                    'type' => 'scope',
                                    'depth' => 3,
                                    'order' => 1,
                                    'children' => [
                                        [
                                            'name' => '제곱근의 뜻',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 1,
                                        ],
                                        [
                                            'name' => '제곱근의 이해',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 2,
                                        ],
                                        [
                                            'name' => '제곱근 구하기',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 3,
                                        ],
                                        [
                                            'name' => '제곱근을 이용하여 정사각형의 한변의 길이 구하기',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 4,
                                        ],
                                        [
                                            'name' => '근호를 사용하지 않고 나타내기',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 5,
                                        ],
                                        [
                                            'name' => '제곱근의 성질',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 6,
                                        ],
                                        [
                                            'name' => '제곱근이 자연수가 되기 위한 조건',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 7,
                                        ],
                                        [
                                            'name' => '제곱근의 대소관계',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 8,
                                        ],
                                        [
                                            'name' => '제곱근을 포함한 부등식',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 9,
                                        ],
                                        [
                                            'name' => '제곱근의 활용',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 10,
                                        ]
                                    ]
                                ],
                                [
                                    'name' => '무리수와 실수',
                                    'type' => 'scope',
                                    'depth' => 3,
                                    'order' => 2,
                                    'children' => [
                                        [
                                            'name' => '유리수와 무리수 구별하기',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 1,
                                        ],
                                        [
                                            'name' => '무리수의 이해',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 2,
                                        ],
                                        [
                                            'name' => '수 집합과 실수의 분류',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 3,
                                        ],
                                        [
                                            'name' => '무리수를 수직선 위에 나타내기',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 4,
                                        ],
                                        [
                                            'name' => '실수와 수직선',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 5,
                                        ],
                                        [
                                            'name' => '실수의 대소관계',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 6,
                                        ],
                                        [
                                            'name' => '두 실수 사이의 수',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 7,
                                        ]
                                    ]
                                ],
                                [
                                    'name' => '근호를 포함한 식의 계산',
                                    'type' => 'scope',
                                    'depth' => 3,
                                    'order' => 3,
                                    'children' => [
                                        [
                                            'name' => '제곱근의 곱셈',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 1,
                                        ],
                                        [
                                            'name' => '근호가 있는 식의 변형',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 2,
                                        ],
                                        [
                                            'name' => '제곱근의 나눗셈',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 3,
                                        ],
                                        [
                                            'name' => '문자를 이용한 제곱근의 표현',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 4,
                                        ],
                                        [
                                            'name' => '분모의 유리화',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 5,
                                        ],
                                        [
                                            'name' => '제곱근의 곱셈과 나눗셈의 혼합계산',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 6,
                                        ],
                                        [
                                            'name' => '제곱근의 도형에서의 활용',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 7,
                                        ],
                                        [
                                            'name' => '제곱근의 덧셈과 뺄셈',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 8,
                                        ],
                                        [
                                            'name' => '분배법칙을 이용한 무리수의 계산',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 9,
                                        ],
                                        [
                                            'name' => '곱셈공식을 이용한 무리수의 계산',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 10,
                                        ],
                                        [
                                            'name' => '제곱근의 계산결과가 유리수가 될 조건',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 11,
                                        ],
                                        [
                                            'name' => '식의 값 구하기',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 12,
                                        ],
                                        [
                                            'name' => '제곱근의 덧셈과 뺄셈의 도형에의 활용',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 13,
                                        ],
                                        [
                                            'name' => '제곱근의 덧셈과 뺄셈의 수직선에의 활용',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 14,
                                        ],
                                        [
                                            'name' => '실수의 대소관계',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 15,
                                        ],
                                        [
                                            'name' => '제곱근표를 이용하여 근삿값 구하기',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 16,
                                        ],
                                        [
                                            'name' => '제곱근의 근삿값을 이용한 계산',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 17,
                                        ],
                                        [
                                            'name' => '무리수의 정수부분과 소수부분',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 18,
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        [
                            'name' => '다항식의 곱셈과 인수분해',
                            'type' => 'scope',
                            'depth' => 2,
                            'order' => 2,
                            'children' => [
                                [
                                    'name' => '곱셈공식',
                                    'type' => 'scope',
                                    'depth' => 3,
                                    'order' => 1,
                                    'children' => [
                                        [
                                            'name' => '다항식과 다항식의 곱셈',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 1,
                                        ],
                                        [
                                            'name' => '개수 구하기',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 2,
                                        ],
                                        [
                                            'name' => '곱셈공식',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 3,
                                        ],
                                        [
                                            'name' => '연속한 합과 차의 곱',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 4,
                                        ],
                                        [
                                            'name' => '곱셈공식의 종합',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 5,
                                        ],
                                        [
                                            'name' => '곱셈 공식과 도형의 넓이',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 6,
                                        ],
                                        [
                                            'name' => '곱셈공식을 이용한 무리수의 계산',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 7,
                                        ],
                                        [
                                            'name' => '곱셈공식을 이용한 분모의 유리화',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 8,
                                        ],
                                        [
                                            'name' => '곱셈공식을 이용한 수의 계산',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 9,
                                        ],
                                        [
                                            'name' => '곱셈공식을 변형하여 식의 값 구하기',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 10,
                                        ],
                                        [
                                            'name' => '식의 값 구하기 – 두 수가 주어진 경우',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 11,
                                        ],
                                        [
                                            'name' => '식의 값 구하기 – 식 간단히 하기',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 12,
                                        ],
                                        [
                                            'name' => '식의 대입',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 13,
                                        ],
                                        [
                                            'name' => '치환을 이용한 식의 전개',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 14,
                                        ]
                                    ]
                                ],
                                [
                                    'name' => '인수분해',
                                    'type' => 'scope',
                                    'depth' => 3,
                                    'order' => 2,
                                    'children' => [
                                        [
                                            'name' => '공통인수를 이용한 인수분해',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 1,
                                        ],
                                        [
                                            'name' => '인수분해 공식',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 2,
                                        ],
                                        [
                                            'name' => '완전제곱식 만들기',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 3,
                                        ],
                                        [
                                            'name' => '두 다항식의 공통인수 구하기',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 4,
                                        ],
                                        [
                                            'name' => '인수분해의 활용',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 5,
                                        ],
                                        [
                                            'name' => '공통인수로 묶어 인수분해',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 6,
                                        ],
                                        [
                                            'name' => '치환을 이용한 인수분해',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 7,
                                        ],
                                        [
                                            'name' => '내림차순을 이용한 인수분해',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 8,
                                        ],
                                        [
                                            'name' => '인수분해 공식을 이용한 수의 계산',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 9,
                                        ],
                                        [
                                            'name' => '인수분해의 도형의 활용',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 10,
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        [
                            'name' => '이차방정식',
                            'type' => 'scope',
                            'depth' => 2,
                            'order' => 3,
                            'children' => [
                                [
                                    'name' => '이차방정식',
                                    'type' => 'scope',
                                    'depth' => 3,
                                    'order' => 1,
                                    'children' => [
                                        [
                                            'name' => '이차방정식의 뜻',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 1,
                                        ],
                                        [
                                            'name' => '이차방정식의 해',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 2,
                                        ],
                                        [
                                            'name' => '근이 주어졌을 때 미지수 구하기',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 3,
                                        ],
                                        [
                                            'name' => '이차방정식의 근의 활용',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 4,
                                        ],
                                        [
                                            'name' => '이차방정식의 중근',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 5,
                                        ],
                                        [
                                            'name' => '두 이차방정식의 공통인 근',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 6,
                                        ],
                                        [
                                            'name' => '완전제곱식을 이용한 근',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 7,
                                        ],
                                        [
                                            'name' => '이차방정식의 근의 공식',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 8,
                                        ],
                                        [
                                            'name' => '치환을 이용한 이차방정식',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 9,
                                        ],
                                        [
                                            'name' => '이차방정식의 근의 개수',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 10,
                                        ],
                                        [
                                            'name' => '근과 계수의 관계',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 11,
                                        ],
                                        [
                                            'name' => '두 근의 차 또는 비가 주어졌을 때 미지수 구하기',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 12,
                                        ],
                                        [
                                            'name' => '한 근이 무리수일 때, 미지수의 값 구하기',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 13,
                                        ],
                                        [
                                            'name' => '이차방정식의 활용',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 14,
                                        ],
                                        [
                                            'name' => '이차방정식의 실생활의 활용',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 15,
                                        ],
                                        [
                                            'name' => '이차방정식의 활용 – 도형',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 16,
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        [
                            'name' => '이차함수와 그래프',
                            'type' => 'scope',
                            'depth' => 2,
                            'order' => 4,
                            'children' => [
                                [
                                    'name' => '이차함수',
                                    'type' => 'scope',
                                    'depth' => 3,
                                    'order' => 1,
                                    'children' => [
                                        [
                                            'name' => '이차함수의 뜻',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 1,
                                        ],
                                        [
                                            'name' => '이차함수의 값',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 2,
                                        ],
                                        [
                                            'name' => '이차함수 y = ax²의 성질',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 3,
                                        ],
                                        [
                                            'name' => '이차함수 y = ax²의 식 구하기',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 4,
                                        ],
                                        [
                                            'name' => '이차함수 y = ax²의 평행이동',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 5,
                                        ],
                                        [
                                            'name' => '이차함수 y = ax²의 대칭이동',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 6,
                                        ],
                                        [
                                            'name' => '이차함수 y = a(x - p)² + q의 성질',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 7,
                                        ],
                                        [
                                            'name' => '이차함수 y = a(x - p)² + q의 미지수 구하기',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 8,
                                        ],
                                        [
                                            'name' => '이차함수 y = ax² + bx + c의 성질',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 9,
                                        ],
                                        [
                                            'name' => '이차함수 그래프의 활용',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 10,
                                        ],
                                        [
                                            'name' => '이차함수 y = ax² + bx + c의 미지수 구하기',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 11,
                                        ],
                                        [
                                            'name' => '이차함수의 식 구하기',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 12,
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ];
                } else if ($grade === 3 &&  $semester === 2) {
                    $children = [
                        [
                            'name' => '삼각비',
                            'type' => 'scope',
                            'depth' => 2,
                            'order' => 1,
                            'children' => [
                                [
                                    'name' => '삼각비의 이해',
                                    'type' => 'scope',
                                    'depth' => 3,
                                    'order' => 1,
                                    'children' => [
                                        [
                                            'name' => '삼각비의 값',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 1,
                                        ],
                                        [
                                            'name' => '한 변의 길이와 삼각비의 값을 알 때 삼각형의 변의 길이 구하기',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 2,
                                        ],
                                        [
                                            'name' => '한 삼각비의 값을 알 때, 다른 삼각비의 값 구하기',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 3,
                                        ],
                                        [
                                            'name' => '직각삼각형의 닮음과 삼각비',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 4,
                                        ],
                                        [
                                            'name' => '특수한 삼각비의 값',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 5,
                                        ],
                                        [
                                            'name' => '특수한 삼각비의 값이 주어질 때 각의 크기 구하기',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 6,
                                        ],
                                        [
                                            'name' => '특수한 삼각비의 값이 주어질 때 변의 길이 구하기',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 7,
                                        ],
                                        [
                                            'name' => '직선의 기울기와 삼각비',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 8,
                                        ],
                                        [
                                            'name' => '사분원을 이용하여 삼각비의 값 구하기',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 9,
                                        ],
                                        [
                                            'name' => '0, 90도의 삼각비',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 10,
                                        ],
                                        [
                                            'name' => '삼각비의 값의 대소 관계',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 11,
                                        ],
                                        [
                                            'name' => '삼각비의 표를 이용하여 삼각비의 값, 각의 크기, 변의 길이 구하기',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 12,
                                        ]
                                    ]
                                ],
                                [
                                    'name' => '삼각비의 활용',
                                    'type' => 'scope',
                                    'depth' => 3,
                                    'order' => 2,
                                    'children' => [
                                        [
                                            'name' => '직각삼각형의 변의 길이',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 1,
                                        ],
                                        [
                                            'name' => '입체도형에서의 직각삼각형의 변의 길이의 활용',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 2,
                                        ],
                                        [
                                            'name' => '실생활에서 직각삼각형의 변의 길이의 활용',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 3,
                                        ],
                                        [
                                            'name' => '일반 삼각형의 변의 길이 – 두 변의 길이와 그 끼인각의 크기를 알 때',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 4,
                                        ],
                                        [
                                            'name' => '일반 삼각형의 변의 길이 – 한 변의 길이와 그 양 끝 각의 크기를 알 때',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 5,
                                        ],
                                        [
                                            'name' => '삼각형의 높이 – 예각이 주어진 경우',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 6,
                                        ],
                                        [
                                            'name' => '삼각형의 높이 – 둔각이 주어진 경우',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 7,
                                        ],
                                        [
                                            'name' => '삼각형의 넓이',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 8,
                                        ],
                                        [
                                            'name' => '사각형의 넓이',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 9,
                                        ],
                                        [
                                            'name' => '평행사변형의 넓이',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 10,
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        [
                            'name' => '원의 성질',
                            'type' => 'scope',
                            'depth' => 2,
                            'order' => 2,
                            'children' => [
                                [
                                    'name' => '원과 직선',
                                    'type' => 'scope',
                                    'depth' => 3,
                                    'order' => 1,
                                    'children' => [
                                        [
                                            'name' => '현의 수직이등분선',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 1,
                                        ],
                                        [
                                            'name' => '현의 길이',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 2,
                                        ],
                                        [
                                            'name' => '길이가 같은 두 현이 만드는 삼각형',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 3,
                                        ],
                                        [
                                            'name' => '원의 접선의 성질',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 4,
                                        ],
                                        [
                                            'name' => '반원에서의 접선의 길이',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 5,
                                        ],
                                        [
                                            'name' => '삼각형의 내접원',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 6,
                                        ],
                                        [
                                            'name' => '직각삼각형의 내접원',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 7,
                                        ],
                                        [
                                            'name' => '외접사각형의 성질',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 8,
                                        ],
                                        [
                                            'name' => '외접사각형의 성질의 응용',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 9,
                                        ],
                                        [
                                            'name' => '접하는 원의 활용',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 10,
                                        ]
                                    ]
                                ],
                                [
                                    'name' => '원주각',
                                    'type' => 'scope',
                                    'depth' => 3,
                                    'order' => 2,
                                    'children' => [
                                        [
                                            'name' => '원주각과 중심각의 크기',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 1,
                                        ],
                                        [
                                            'name' => '한 호에 대한 원주각의 크기',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 2,
                                        ],
                                        [
                                            'name' => '반원에 대한 원주각의 크기',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 3,
                                        ],
                                        [
                                            'name' => '원주각과 삼각비의 값',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 4,
                                        ],
                                        [
                                            'name' => '원주각의 크기와 호의 길이',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 5,
                                        ],
                                        [
                                            'name' => '네 점이 한 원 위에 있을 조건 - 원주각',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 6,
                                        ],
                                        [
                                            'name' => '원에 내접하는 사각형의 성질',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 7,
                                        ],
                                        [
                                            'name' => '접선과 현이 이루는 각',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 8,
                                        ],
                                        [
                                            'name' => '접선과 현이 이루는 각의 활용',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 9,
                                        ],
                                        [
                                            'name' => '삼각비를 활용한 삼각형의 넓이',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 10,
                                        ],
                                        [
                                            'name' => '원에 내접하는 사각형과 외각의 성질',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 11,
                                        ],
                                        [
                                            'name' => '원에 내접하는 다각형',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 12,
                                        ],
                                        [
                                            'name' => '두 원에서 내접하는 사각형의 성질의 응용',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 13,
                                        ],
                                        [
                                            'name' => '사각형이 원에 내접하기 위한 조건',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 14,
                                        ],
                                        [
                                            'name' => '두 원에서 접선과 현이 이루는 각',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 15,
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        [
                            'name' => '통계',
                            'type' => 'scope',
                            'depth' => 2,
                            'order' => 3,
                            'children' => [
                                [
                                    'name' => '대푯값과 산포도',
                                    'type' => 'scope',
                                    'depth' => 3,
                                    'order' => 1,
                                    'children' => [
                                        [
                                            'name' => '평균의 뜻과 성질',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 1,
                                        ],
                                        [
                                            'name' => '변량이 증가 또는 감소할 때의 평균',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 2,
                                        ],
                                        [
                                            'name' => '평균이 주어질 때 변량 구하기',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 3,
                                        ],
                                        [
                                            'name' => '중앙값의 뜻과 성질',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 4,
                                        ],
                                        [
                                            'name' => '중앙값이 주어질 때 변량 구하기',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 5,
                                        ],
                                        [
                                            'name' => '최빈값의 뜻과 성질',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 6,
                                        ],
                                        [
                                            'name' => '대푯값 구하기',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 7,
                                        ],
                                        [
                                            'name' => '평균과 최빈값이 같을 때, 변량 구하기',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 8,
                                        ],
                                        [
                                            'name' => '편차의 성질을 이용하여 변량 구하기',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 9,
                                        ],
                                        [
                                            'name' => '분산과 표준편차 구하기',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 10,
                                        ],
                                        [
                                            'name' => '표준편차의 직관적 비교',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 11,
                                        ],
                                        [
                                            'name' => '변량의 평균과 분산을 이용하여 식의 값 구하기',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 12,
                                        ],
                                        [
                                            'name' => '변화된 변량의 평균, 분산, 표준편차',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 13,
                                        ],
                                        [
                                            'name' => '평균이 같은 두 집단 전체의 표준편차 구하기',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 14,
                                        ],
                                        [
                                            'name' => '자료의 분석',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 15,
                                        ]
                                    ]
                                ],
                                [
                                    'name' => '산점도와 상관관계',
                                    'type' => 'scope',
                                    'depth' => 3,
                                    'order' => 2,
                                    'children' => [
                                        [
                                            'name' => '산점도',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 1,
                                        ],
                                        [
                                            'name' => '상관관계',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 2,
                                        ],
                                        [
                                            'name' => '산점도와 상관관계의 이해',
                                            'type' => 'question_type',
                                            'depth' => 4,
                                            'order' => 3,
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
            ],
            [
                'name' => '수학(하)',
                'type' => 'scope',
                'depth' => 1,
                'order' => 2,
                'children' => [
                    [
                        'name' => '평면좌표와 직선의 방정식',
                        'type' => 'scope',
                        'depth' => 2,
                        'order' => 1,
                        'children' => [
                            [
                                'name' => '두 점 사이의 거리',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 1,
                            ],
                            [
                                'name' => '두 점 사이의 거리 ; 식의 값 구하기',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 2,
                            ],
                            [
                                'name' => '정점에서 같은 거리에 있는 점',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 3,
                            ],
                            [
                                'name' => '두 점 사이의 거리 ; 삼각형에의 활용',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 4,
                            ],
                            [
                                'name' => '선분의 길이의 합의 최솟값',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 5,
                            ],
                            [
                                'name' => '두 점 사이의 거리의 활용 ; 거리의 제곱의 합이 최소인 점의 좌표',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 6,
                            ],
                            [
                                'name' => '선분의 내분점․외분점',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 7,
                            ],
                            [
                                'name' => '선분의 중점 ; 사각형에의 활용',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 8,
                            ],
                            [
                                'name' => '삼각형의 무게중심',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 9,
                            ],
                            [
                                'name' => '중선정리',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 10,
                            ],
                            [
                                'name' => '자취의 방정식',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 11,
                            ],
                            [
                                'name' => '직선의 방정식 ; 한 점과 기울기가 주어질 때',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 12,
                            ],
                            [
                                'name' => '직선의 방정식 ; 두 점이 주어질 때',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 13,
                            ],
                            [
                                'name' => '직선의 방정식 ;  축과 이루는 각의 크기가 주어질 때',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 14,
                            ],
                            [
                                'name' => '직선의 방정식 ;  절편, 절편이 주어질 때',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 15,
                            ],
                            [
                                'name' => '세 점이 한 직선 위에 있을 조건',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 16,
                            ],
                            [
                                'name' => '도형의 넓이를 이등분하는 직선',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 17,
                            ],
                            [
                                'name' => '직선의 개형',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 18,
                            ],
                            [
                                'name' => '두 직선의 위치 관계',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 19,
                            ],
                            [
                                'name' => '세 직선의 위치 관계',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 20,
                            ],
                            [
                                'name' => '직선의 방정식 ; 수직․평행 조건이 주어질 때',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 21,
                            ],
                            [
                                'name' => '직선의 방정식 ; 수직이등분선',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 22,
                            ],
                            [
                                'name' => '정점을 지나는 직선',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 23,
                            ],
                            [
                                'name' => '정점을 지나는 직선의 활용',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 24,
                            ],
                            [
                                'name' => '두 직선의 교점을 지나는 직선의 방정식',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 25,
                            ],
                            [
                                'name' => '점과 직선 사이의 거리(1)',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 26,
                            ],
                            [
                                'name' => '삼각형의 넓이',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 27,
                            ],
                            [
                                'name' => '평행한 두 직선 사이의 거리',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 28,
                            ],
                            [
                                'name' => '자취의 방정식',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 29,
                            ],
                        ],
                    ],
                    [
                        'name' => '원의 방정식',
                        'type' => 'scope',
                        'depth' => 2,
                        'order' => 2,
                        'children' => [
                            [
                                'name' => '중심에 대한 조건이 주어진 원의 방정식',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 1,
                            ],
                            [
                                'name' => '원이 되기 위한 조건',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 2,
                            ],
                            [
                                'name' => '원의 중심의 좌표와 반지름의 길이 구하기',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 3,
                            ],
                            [
                                'name' => '세 점을 지나는 원의 방정식',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 4,
                            ],
                            [
                                'name' => ' 축에 접하는 원의 방정식',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 5,
                            ],
                            [
                                'name' => ' 축에 접하는 원의 방정식',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 6,
                            ],
                            [
                                'name' => ' 축, 축에 동시에 접하는 원의 방정식',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 7,
                            ],
                            [
                                'name' => '자취의 방정식(1)',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 8,
                            ],
                            [
                                'name' => '자취의 방정식(2) - 아폴로니오스의 원',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 9,
                            ],
                            [
                                'name' => '두 원의 위치 관계',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 10,
                            ],
                            [
                                'name' => '두 원의 교점을 지나는 원의 방정식',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 11,
                            ],
                            [
                                'name' => '두 원의 공통현의 방정식',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 12,
                            ],
                            [
                                'name' => '두 원의 공통현의 길이',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 13,
                            ],
                            [
                                'name' => '원과 직선의 위치 관계 ; 서로 다른 두 점에서 만날 때',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 14,
                            ],
                            [
                                'name' => '현의 길이',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 15,
                            ],
                            [
                                'name' => '원과 직선의 위치 관계 ; 접할 때',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 16,
                            ],
                            [
                                'name' => '접선의 길이',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 17,
                            ],
                            [
                                'name' => '원과 직선의 위치 관계 ; 만나지 않을 때',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 18,
                            ],
                            [
                                'name' => '원 위의 점과 직선 사이의 거리의 최대․최소',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 19,
                            ],
                            [
                                'name' => '원의 접선의 방정식 ; 기울기가 주어질 때',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 20,
                            ],
                            [
                                'name' => '원의 접선의 방정식 ; 원 위의 한 점이 주어질 때',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 21,
                            ],
                            [
                                'name' => '원의 접선의 방정식 ; 원 밖의 한 점이 주어질 때',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 22,
                            ],
                            [
                                'name' => '두 원의 공통외접선의 길이',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 23,
                            ],
                            [
                                'name' => '두 원의 공통내접선의 길이',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 24,
                            ],
                        ],
                    ],
                    [
                        'name' => '도형의 이동',
                        'type' => 'scope',
                        'depth' => 2,
                        'order' => 3,
                        'children' => [
                            [
                                'name' => '점의 평행이동 ; 평행이동이 주어진 경우',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 1,
                            ],
                            [
                                'name' => '점의 평행이동 ; 평행이동을 구해야 하는 경우',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 2,
                            ],
                            [
                                'name' => '직선의 평행이동(1)',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 3,
                            ],
                            [
                                'name' => '직선의 평행이동(2)',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 4,
                            ],
                            [
                                'name' => '곡선의 평행이동',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 5,
                            ],
                            [
                                'name' => '평행이동의 활용',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 6,
                            ],
                            [
                                'name' => '점의 대칭이동',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 7,
                            ],
                            [
                                'name' => '도형의 대칭이동 ; 직선',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 8,
                            ],
                            [
                                'name' => '도형의 대칭이동 ; 곡선',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 9,
                            ],
                            [
                                'name' => '도형의 대칭이동 ;   ',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 10,
                            ],
                            [
                                'name' => '대칭이동의 활용',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 11,
                            ],
                            [
                                'name' => '도형의 평행이동과 대칭이동',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 12,
                            ],
                            [
                                'name' => '점에 대한 점의 대칭이동',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 13,
                            ],
                            [
                                'name' => '점에 대한 도형의 대칭이동',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 14,
                            ],
                            [
                                'name' => '직선  에 대한 점의 대칭이동',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 15,
                            ],
                            [
                                'name' => '직선   에 대한 원의 대칭이동',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 16,
                            ],
                            [
                                'name' => '대칭이동을 이용한 거리의 최솟값',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 17,
                            ],
                        ],
                    ],
                    [
                        'name' => '부등식의 영역',
                        'type' => 'scope',
                        'depth' => 2,
                        'order' => 4,
                        'children' => [
                            [
                                'name' => '부등식의 영역에 속하는 점',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 1,
                            ],
                            [
                                'name' => '곡선 또는 직선의 윗부분, 아랫부분',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 2,
                            ],
                            [
                                'name' => '원의 내부, 외부',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 3,
                            ],
                            [
                                'name' => '부등식의 영역과 집합의 포함 관계',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 4,
                            ],
                            [
                                'name' => '부등식의 영역과 집합의 연산',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 5,
                            ],
                            [
                                'name' => '연립부등식의 영역',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 6,
                            ],
                            [
                                'name' => '연립부등식의 영역의 넓이',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 7,
                            ],
                            [
                                'name' => '연립부등식의 영역에 한 점이 포함될 조건',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 8,
                            ],
                            [
                                'name' => '부등식의 영역에서 격자점의 개수',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 9,
                            ],
                            [
                                'name' => '두 점이 서로 다른 부등식의 영역에 속하는 경우',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 10,
                            ],
                            [
                                'name' => '다항식의 곱으로 표시된 부등식의 영역',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 11,
                            ],
                            [
                                'name' => '부등식의 영역에 속하는 점',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 12,
                            ],
                            [
                                'name' => '부등식의 영역을 식으로 나타내기',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 13,
                            ],
                            [
                                'name' => '부등식의 영역에서 일차식의 최대․최소',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 14,
                            ],
                            [
                                'name' => '부등식의 영역에서         의 최대・최소',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 15,
                            ],
                            [
                                'name' => '부등식의 영역에서    의 최대・최소',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 16,
                            ],
                            [
                                'name' => '부등식의 영역에서 분수식의 최대・최소',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 17,
                            ],
                            [
                                'name' => '부등식의 영역에서의 최대・최소의 활용',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 18,
                            ],
                        ],
                    ],
                    [
                        'name' => '함수',
                        'type' => 'scope',
                        'depth' => 2,
                        'order' => 5,
                        'children' => [
                            [
                                'name' => '함수의 뜻',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 1,
                            ],
                            [
                                'name' => '함숫값 구하기(1)',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 2,
                            ],
                            [
                                'name' => '함숫값 구하기(2)',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 3,
                            ],
                            [
                                'name' => '함수의 치역',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 4,
                            ],
                            [
                                'name' => '조건을 이용하여 함숫값 구하기',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 5,
                            ],
                            [
                                'name' => '서로 같은 함수',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 6,
                            ],
                            [
                                'name' => '일대일 대응 찾기',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 7,
                            ],
                            [
                                'name' => '일대일 대응이 되기 위한 조건',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 8,
                            ],
                            [
                                'name' => '항등함수와 상수함수',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 9,
                            ],
                            [
                                'name' => '조건을 만족하는 함수의 개수',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 10,
                            ],
                            [
                                'name' => '합성함수의 함수값 구하기',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 11,
                            ],
                            [
                                'name' => '∘ ∘인 경우',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 12,
                            ],
                            [
                                'name' => '∘에 대한 조건이 주어진 경우',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 13,
                            ],
                            [
                                'name' => '∘ 를 만족하는 함수  또는  구하기',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 14,
                            ],
                            [
                                'name' => '합성함수에서 함숫값의 활용',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 15,
                            ],
                            [
                                'name' => '합성함수의 추정',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 16,
                            ],
                            [
                                'name' => '역함수의 정의',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 17,
                            ],
                            [
                                'name' => '합성함수와 역함수',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 18,
                            ],
                            [
                                'name' => '역함수가 존재하기 위한 조건',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 19,
                            ],
                            [
                                'name' => '역함수 구하기',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 20,
                            ],
                            [
                                'name' => ' 인 함수',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 21,
                            ],
                            [
                                'name' => '역함수의 성질',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 22,
                            ],
                            [
                                'name' => '합성함수․역함수의 정의와 그래프',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 23,
                            ],
                            [
                                'name' => '역함수의 그래프의 성질',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 24,
                            ],
                            [
                                'name' => '역함수를 이용한 함수값 구하기',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 25,
                            ],
                        ],
                    ],
                    [
                        'name' => '이차함수의 활용',
                        'type' => 'scope',
                        'depth' => 2,
                        'order' => 6,
                        'children' => [
                            [
                                'name' => '일차함수의 치역',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 1,
                            ],
                            [
                                'name' => '일차함수의 그래프의 활용',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 2,
                            ],
                            [
                                'name' => '   또는   를 만족하는 함수',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 3,
                            ],
                            [
                                'name' => '이차함수의 그래프의 꼭짓점의 좌표',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 4,
                            ],
                            [
                                'name' => '이차함수의 그래프의 평행이동과 대칭이동',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 5,
                            ],
                            [
                                'name' => '이차함수의 그래프와 계수의 부호',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 6,
                            ],
                            [
                                'name' => '이차함수의 식 구하기',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 7,
                            ],
                            [
                                'name' => '이차함수의 그래프의 활용',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 8,
                            ],
                            [
                                'name' => '절댓값 기호를 포함한 식의 그래프 ;  의 그래프가 주어진 경우',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 9,
                            ],
                            [
                                'name' => '두 개 이상의 절댓값 기호를 포함한 함수',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 10,
                            ],
                            [
                                'name' => '이차함수의 최대・최소',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 11,
                            ],
                            [
                                'name' => '완전제곱식을 이용한 이차식의 최대・최소',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 12,
                            ],
                            [
                                'name' => '제한된 범위에서의 이차함수의 최대・최소',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 13,
                            ],
                            [
                                'name' => '이차함수의 최대・최소의 활용',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 14,
                            ],
                            [
                                'name' => '치환을 이용한 최대・최소',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 15,
                            ],
                            [
                                'name' => '조건을 만족하는 이차식의 최대․최소',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 16,
                            ],
                            [
                                'name' => '판별식을 이용한 최대・최소',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 17,
                            ],
                        ],
                    ],
                    [
                        'name' => '이차함수의 활용(2)',
                        'type' => 'scope',
                        'depth' => 2,
                        'order' => 7,
                        'children' => [
                            [
                                'name' => '이차함수의 그래프와 직선의 위치 관계',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 1,
                            ],
                            [
                                'name' => '이차함수의 그래프에 접하는 직선',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 2,
                            ],
                            [
                                'name' => '이차함수의 그래프와 직선의 교점',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 3,
                            ],
                            [
                                'name' => '두 그래프의 두 교점 사이의 거리',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 4,
                            ],
                            [
                                'name' => '이차함수의 그래프와 직선 사이의 거리',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 5,
                            ],
                            [
                                'name' => '절댓값 기호를 포함한 방정식의 실근과 함수의 그래프',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 6,
                            ],
                            [
                                'name' => '이차함수의 그래프와  축과의 교점의 좌표',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 7,
                            ],
                            [
                                'name' => '이차함수의 그래프와  축과의 위치 관계',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 8,
                            ],
                            [
                                'name' => '이차함수의 그래프와 이차방정식의 해',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 9,
                            ],
                            [
                                'name' => '이차방정식의 근의 분리',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 10,
                            ],
                            [
                                'name' => '그래프를 이용한 부등식의 풀이',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 11,
                            ],
                            [
                                'name' => '이차부등식과 두 그래프의 위치 관계(1)',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 12,
                            ],
                            [
                                'name' => '제한된 범위에서 항상 성립하는 이차부등식',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 13,
                            ],
                            [
                                'name' => '이차부등식이 해를 가질 조건',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 14,
                            ],
                            [
                                'name' => '모든 실수에 대하여 항상 성립하는 부등식',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 15,
                            ],
                            [
                                'name' => '이차부등식과 두 그래프의 위치 관계(2)',
                                'type' => 'question_type',
                                'depth' => 3,
                                'order' => 16,
                            ],
                        ],
                    ],
                    ...require storage_path('app/수학하.php')
                ]
            ],
            [
                'name' => '수학 1',
                'type' => 'scope',
                'depth' => 1,
                'order' => 3,
                'children' => [
                    ...require storage_path('app/수학1.php')
                ]
            ],
            [
                'name' => '기본미적과 통계기초',
                'type' => 'scope',
                'depth' => 1,
                'order' => 4,
                'children' => [
                    ...require storage_path('app/기본미적과통계기초.php')
                ]
            ],
            [
                'name' => '수학 2',
                'type' => 'scope',
                'depth' => 1,
                'order' => 5,
                'children' => [
                    ...require storage_path('app/수2.php')
                ]
            ],
            [
                'name' => '적분과 통계',
                'type' => 'scope',
                'depth' => 1,
                'order' => 6,
                'children' => [
                    ...require storage_path('app/적분과통계.php')
                ]
            ],
            [
                'name' => '기하와 벡터',
                'type' => 'scope',
                'depth' => 1,
                'order' => 7,
                'children' => [
                    ...require storage_path('app/기하와벡터.php')
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
