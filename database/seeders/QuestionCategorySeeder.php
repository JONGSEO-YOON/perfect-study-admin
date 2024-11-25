<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
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
                    'name' => "{$grade}-{$semester}",
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
                    'name' => "{$grade}-{$semester}",
                    'type' => 'scope',
                    'depth' => 1,
                    'order' => ($grade - 1) * 2 + $semester,
                    'children' => $children
                ];
            }
        }
        return $categories;
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
                'children' => [
                    [
                        'name' => '수학 (상)',
                        'type' => 'scope',
                        'depth' => 1,
                        'order' => 1,
                    ],
                    [
                        'name' => '수학 (하)',
                        'type' => 'scope',
                        'depth' => 1,
                        'order' => 2,
                    ],
                    [
                        'name' => '수학 1',
                        'type' => 'scope',
                        'depth' => 1,
                        'order' => 3,
                    ],
                    [
                        'name' => '수학 2',
                        'type' => 'scope',
                        'depth' => 1,
                        'order' => 4,
                    ],
                    [
                        'name' => '확률과 통계',
                        'type' => 'scope',
                        'depth' => 1,
                        'order' => 5,
                    ],
                    [
                        'name' => '미적분',
                        'type' => 'scope',
                        'depth' => 1,
                        'order' => 6,
                    ]
                ]
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
