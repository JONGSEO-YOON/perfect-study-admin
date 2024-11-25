<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
}
