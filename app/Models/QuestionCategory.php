<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class QuestionCategory extends Model
{

    // 직계 자식 카테고리들
    public function children()
    {
        return $this->belongsToMany(QuestionCategory::class, 'question_category_closure', 'ancestor_id', 'descendant_id')
            ->wherePivot('depth', 1);
    }

    // 모든 하위 카테고리들
    public function descendants()
    {
        return $this->belongsToMany(QuestionCategory::class, 'question_category_closure', 'ancestor_id', 'descendant_id')
            ->wherePivot('depth', '>', 0);
    }

    // 직계 부모 카테고리
    public function parent()
    {
        return $this->belongsToMany(QuestionCategory::class, 'question_category_closure', 'descendant_id', 'ancestor_id')
            ->wherePivot('depth', 1);
    }

    // 모든 상위 카테고리들
    public function ancestors()
    {
        return $this->belongsToMany(QuestionCategory::class, 'question_category_closure', 'descendant_id', 'ancestor_id')
            ->wherePivot('depth', '>', 0);
    }

    // 이 카테고리에 속한 모든 문제들 (문제 유형인 경우)
    public function questions()
    {
        return $this->hasMany(Question::class, 'question_type_id');
    }

    // 이 카테고리와 모든 하위 카테고리에 속한 문제들
    public function allQuestions()
    {
        $descendantIds = $this->descendants()->pluck('question_categories.id');
        $ids = collect([$this->id])->concat($descendantIds);

        return Question::whereIn('question_type_id', $ids);
    }

    // 트리 구조로 변환하는 메서드
    public function toTree()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'depth' => $this->depth,
            'children' => $this->children->map(function ($child) {
                return $child->toTree();
            })
        ];
    }

    // 정적 메서드로 전체 트리 가져오기
    public static function getFullTree()
    {
        // depth가 0인 최상위 카테고리들만 가져옴
        return self::with('children.children.children.children') // 필요한 깊이만큼 with() 체이닝
            ->where('depth', 0)
            ->orderBy('order')
            ->get()
            ->map(function ($category) {
                return $category->toTree();
            });
    }

    public static function createWithParent(array $data, ?QuestionCategory $parent = null): QuestionCategory
    {
        return DB::transaction(function () use ($data, $parent) {
            // 1. order 값 계산
            $maxOrder = static::when($parent, function ($query) use ($parent) {
                // 부모가 있는 경우: 같은 부모를 가진 카테고리들 중 최대값
                $siblingIds = DB::table('question_category_closure')
                    ->where('ancestor_id', $parent->id)
                    ->where('depth', 1)
                    ->pluck('descendant_id');
                return $query->whereIn('id', $siblingIds);
            }, function ($query) {
                // 부모가 없는 경우: depth 0인 카테고리들 중 최대값
                return $query->where('depth', 0);
            })
                ->max('order') ?? -1;

            // 2. 새로운 카테고리 생성
            $category = new static();
            $category->name = $data['name'];
            $category->type = $data['type'] ?? 'scope';
            $category->depth = $parent ? $parent->depth + 1 : 0;
            $category->order = $maxOrder + 1;
            $category->save();

            // 3. Closure Table 관계 생성
            // 3.1. 자기 자신과의 관계 (depth = 0)
            DB::table('question_category_closure')->insert([
                'ancestor_id' => $category->id,
                'descendant_id' => $category->id,
                'depth' => 0
            ]);

            if ($parent) {
                // 3.2. 부모의 모든 조상들과의 관계를 가져옴
                $ancestors = DB::table('question_category_closure')
                    ->where('descendant_id', $parent->id)
                    ->get();

                // 3.3. 각 조상과 새 카테고리와의 관계 생성
                $relations = $ancestors->map(function ($ancestor) use ($category) {
                    return [
                        'ancestor_id' => $ancestor->ancestor_id,
                        'descendant_id' => $category->id,
                        'depth' => $ancestor->depth + 1
                    ];
                })->toArray();

                // 3.4. 관계 데이터 삽입
                DB::table('question_category_closure')->insert($relations);
            }

            return $category;
        });
    }

    public function updateWithOrder(array $data): bool
    {
        return DB::transaction(function () use ($data) {
            // 1. order 변경이 있는 경우 처리
            if (isset($data['order']) && $data['order'] !== $this->order) {
                $newOrder = $data['order'];
                $oldOrder = $this->order;

                // 같은 depth level에서의 처리
                $query = static::where('depth', $this->depth);

                // 부모가 있는 경우 (depth > 0), 같은 부모를 가진 카테고리들 사이에서만 처리
                if ($this->depth > 0) {
                    $siblingIds = DB::table('question_category_closure')
                        ->where('ancestor_id', $this->parent()->first()->id)
                        ->where('depth', 1)
                        ->pluck('descendant_id');
                    $query->whereIn('id', $siblingIds);
                }

                // order를 증가시킬지 감소시킬지 결정
                if ($newOrder > $oldOrder) {
                    // 이동할 위치가 더 뒤인 경우
                    // old와 new 사이에 있는 항목들의 order를 1씩 감소
                    $query->where('order', '>', $oldOrder)
                        ->where('order', '<=', $newOrder)
                        ->decrement('order');
                } else {
                    // 이동할 위치가 더 앞인 경우
                    // new와 old 사이에 있는 항목들의 order를 1씩 증가
                    $query->where('order', '>=', $newOrder)
                        ->where('order', '<', $oldOrder)
                        ->increment('order');
                }

                $this->order = $newOrder;
                $this->save();

                $categories = $this->parent()->first()->children()->orderBy('order')->get();
                foreach ($categories as $index => $category) {
                    if ($category->order !== $index) {
                        $category->update(['order' => $index]);
                    }
                }
            }

            // 2. 기본 필드 업데이트
            if (isset($data['name'])) {
                $this->name = $data['name'];
            }
            if (isset($data['type'])) {
                $this->type = $data['type'];
            }

            return $this->save();
        });
    }

    public function updateParent(QuestionCategory $newParent): bool
    {
        return DB::transaction(function () use ($newParent) {
            // 기존 부모 저장 (나중에 reorder하기 위해)
            $oldParent = $this->parent()->first();

            // 1. Closure Table 관계 업데이트
            // 1.1. 기존 관계 삭제 (자신과 자신의 조상들 간의 관계)
            DB::table('question_category_closure')
                ->where('descendant_id', $this->id)
                ->where('depth', '>', 0)
                ->delete();

            // 1.2. 새로운 부모의 모든 조상들과의 관계 생성
            $ancestors = DB::table('question_category_closure')
                ->where('descendant_id', $newParent->id)
                ->get();

            $relations = $ancestors->map(function ($ancestor) {
                return [
                    'ancestor_id' => $ancestor->ancestor_id,
                    'descendant_id' => $this->id,
                    'depth' => $ancestor->depth + 1
                ];
            })->toArray();

            $lastOrder = $newParent->children()->max('order') ?? -1;
            DB::table('question_category_closure')->insert($relations);

            // 2. depth 업데이트
            $this->depth = $newParent->depth + 1;

            // 3. order 업데이트 (새 부모의 마지막 순서로)
            $this->order = $lastOrder + 1;

            $this->save();

            // 4. 이전 부모의 자식들 reorder
            if ($oldParent) {
                $categories = $oldParent->children()->orderBy('order')->get();
                foreach ($categories as $index => $category) {
                    if ($category->order !== $index) {
                        $category->update(['order' => $index]);
                    }
                }
            }

            return true;
        });
    }

    public function delete(): bool
    {
        return DB::transaction(function () {
            // 삭제 전에 부모 정보 저장
            $parent = $this->parent()->first();

            // 기본 삭제 수행
            $result = parent::delete();

            // 삭제 후 부모의 자식들 reorder
            if ($parent) {
                $categories = $parent->children()->orderBy('order')->get();
                foreach ($categories as $index => $category) {
                    if ($category->order !== $index) {
                        $category->update(['order' => $index]);
                    }
                }
            }

            return $result;
        });
    }
    /**
     * full_path attribute 정의
     * 조상부터 현재까지의 경로를 '>' 구분자로 반환
     */
    public function getFullPathAttribute(): string
    {
        // 자신을 포함한 모든 조상들을 depth 순으로 가져오기
        $categories = DB::table('question_category_closure as c')
            ->join('question_categories as qc', 'c.ancestor_id', '=', 'qc.id')
            ->where('c.descendant_id', $this->id)
            ->orderBy('c.depth', 'desc')
            ->select('qc.name')
            ->get();

        // 이름들을 ' > ' 구분자로 연결
        return $categories->pluck('name')->implode(' < ');
    }
}
