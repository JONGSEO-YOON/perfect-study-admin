<?php

namespace App\Livewire;

use Ijpatricio\Mingle\Concerns\InteractsWithMingles;
use Ijpatricio\Mingle\Contracts\HasMingles;
use Illuminate\Support\Collection;
use Livewire\Component;

class PageSelector extends Component implements HasMingles
{
    use InteractsWithMingles;

    public $id;

    public function component(): string
    {
        return 'resources/js/PageSelector.js';
    }

    public function mingleData(): array
    {
        $id = $this->id;
        // read all jpgs under /storage/pp/public/converted-pdfs/${id}/*.jpg
        // and add to array their public url
        $jpgFiles = \File::glob(storage_path("app/public/converted-pdfs/{$id}/*.jpg"));
        $jpgUrls = array_map(function ($file) use ($id) {
            return asset("storage/app/public/converted-pdfs/{$id}/" . basename($file));
        }, $jpgFiles);

        $data = [];
        for ($i = 1; $i <= count($jpgUrls); $i++) {
            $data[] = [
                'number' => $i,
                'url' =>  asset("storage/converted-pdfs/{$id}/page_{$i}.jpg")
            ];
        }

        return [
            'message' => 'Message in a bottle 🍾',
            'pages' => $data,
            'id' => $id
        ];
    }

    public function doubleIt($amount)
    {
        return $amount * 2;
    }

    public function onQuestionClicked($data)
    {
        $this->dispatch('questionSelected', $data);
    }
}
