<?php

namespace App\Livewire;

use App\Models\Lecture;
use Ijpatricio\Mingle\Concerns\InteractsWithMingles;
use Ijpatricio\Mingle\Contracts\HasMingles;
use Illuminate\Support\Collection;
use Livewire\Component;

class StudentLectureVideoList extends Component implements HasMingles
{
    use InteractsWithMingles;

    public $id;
    public $histories;

    public function component(): string
    {
        return 'resources/js/StudentLectureVideoList.js';
    }

    public function mingleData(): array
    {
        $tree = Lecture::find($this->id)->lecture_info ?? [];
        $tree = $this->reorderTree($tree);
        return [
            'tree' => $tree,
            'message' => 'Message in a bottle 🍾',
            'histories' => $this->histories,
        ];
    }


    private function reorderTree($items)
    {
        if (empty($items)) return [];

        // Sort by order
        usort($items, fn($a, $b) => ($a['order'] ?? 0) - ($b['order'] ?? 0));

        // Reindex orders starting from 1
        foreach ($items as $index => &$item) {
            $item['order'] = $index + 1;
            if (!empty($item['children'])) {
                $item['children'] = $this->reorderTree($item['children']);
            }
        }

        return $items;
    }

    public function updateTree($tree)
    {
        $lecture = Lecture::find($this->id);
        $lecture->lecture_info = $tree;
        $lecture->save();
    }
}
