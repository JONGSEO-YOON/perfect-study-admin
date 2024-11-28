<?php

namespace App\Livewire;

use App\Filament\Resources\QuestionResource;
use App\Models\Question;
use Filament\Notifications\Notification;
use Ijpatricio\Mingle\Concerns\InteractsWithMingles;
use Ijpatricio\Mingle\Contracts\HasMingles;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
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
            'pages' => $data,
            'id' => $id
        ];
    }


    public function editQuestions($data)
    {
        // 정렬 로직 구현
        usort($data, function ($a, $b) {
            // 1. 페이지 번호로 먼저 정렬
            if ($a['pageNumber'] !== $b['pageNumber']) {
                return $a['pageNumber'] - $b['pageNumber'];
            }

            // 2. x 좌표가 50px 이상 차이나는 경우 x 좌표로 정렬
            $xDiff = abs($a['x'] - $b['x']);
            if ($xDiff >= 100) {
                return $a['x'] - $b['x'];
            }

            // 3. x 좌표가 비슷한 경우 y 좌표로 정렬
            return $a['y'] - $b['y'];
        });

        // questions 디렉토리 생성
        $questionsPath = storage_path("app/public/converted-pdfs/{$this->id}/questions");
        if (!file_exists($questionsPath)) {
            mkdir($questionsPath, 0777, true);
        }

        $questions = [];
        try {
            // 각 문제 영역을 크롭하여 저장

            foreach ($data as $index => $question) {
                $pageNumber = $question['pageNumber'];
                $sourcePath = storage_path("app/public/converted-pdfs/{$this->id}/page_{$pageNumber}.jpg");

                // 새 Imagick 인스턴스 생성
                $imagick = new \Imagick($sourcePath);

                // 크롭 실행
                $imagick->cropImage(
                    $question['width'],
                    $question['height'],
                    $question['x'],
                    $question['y']
                );

                // 이미지 최적화
                $imagick->setImageCompression(\Imagick::COMPRESSION_JPEG);
                $imagick->setImageCompressionQuality(90);
                $imagick->stripImage(); // 메타데이터 제거

                // 크롭된 이미지 저장
                $number = $index + 1;
                $outputPath = "{$questionsPath}/question_" . ($number) . ".jpg";
                $imagick->writeImage($outputPath);

                // 메모리 해제
                $imagick->clear();
                $imagick->destroy();

                $questions[] = [
                    'number' => $number,
                    'url' =>  asset("storage/converted-pdfs/{$this->id}/questions/question_{$number}.jpg")
                ];
            }
            return [
                'questions' => $questions,
            ];
        } catch (\ImagickException $e) {
            return [
                'status' => 'error',
                'message' => 'Failed to process images: ' . $e->getMessage()
            ];
        }
    }

    public function addQuestions($questions)
    {
        DB::transaction(function () use ($questions) {
            foreach ($questions as $question) {
                QuestionResource::handleCreate($question['data']);
            }

            Notification::make()
                ->title('문제가 등록되었습니다')
                ->success()
                ->send();
            redirect('/admin/questions');
        });
    }
}
