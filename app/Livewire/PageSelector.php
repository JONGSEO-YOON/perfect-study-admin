<?php

namespace App\Livewire;

use App\Filament\Resources\QuestionResource;
use App\Models\Question;
use Filament\Notifications\Notification;
use Ijpatricio\Mingle\Concerns\InteractsWithMingles;
use Ijpatricio\Mingle\Contracts\HasMingles;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Livewire\Component;

class PageSelector extends Component implements HasMingles
{
    use InteractsWithMingles;

    public $id;

    public function component(): string
    {
        return 'resources/js/PageSelector.js';
    }

    public function refreshPages()
    {
        return $this->mingleData();
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

        $cache = Cache::get("pdf_conversion_{$id}", [
            'progress' => 0,
            'currentPage' => 0,
            'totalPages' => 0,
            'material_id' => null,
            'is_public' => false,
            'is_completed' => false
        ], now()->addHours(1));

        return [
            'cache' => $cache,
            'pages' => $data,
            'id' => $id
        ];
    }


    public function editQuestions($data)
    {
        // 페이지별로 그룹화하여 정렬
        $grouped = [];
        foreach ($data as $item) {
            $grouped[$item['pageNumber']][] = $item;
        }
        ksort($grouped);

        $data = [];
        foreach ($grouped as $pageNumber => $pageItems) {
            // 실제 이미지 너비를 기준으로 열 분리
            $sourcePath = storage_path("app/public/converted-pdfs/{$this->id}/page_{$pageNumber}.jpg");
            if (file_exists($sourcePath)) {
                $imageSize = getimagesize($sourcePath);
                $pageWidth = $imageSize ? $imageSize[0] : 1000;
            } else {
                $pageWidth = 1000;
            }
            $columnThreshold = $pageWidth * 0.45;

            $leftCol = [];
            $rightCol = [];
            foreach ($pageItems as $item) {
                $centerX = $item['x'] + ($item['width'] / 2);
                if ($centerX < $columnThreshold) {
                    $leftCol[] = $item;
                } else {
                    $rightCol[] = $item;
                }
            }

            // 각 열 내에서 y좌표로 정렬
            usort($leftCol, fn($a, $b) => $a['y'] - $b['y']);
            usort($rightCol, fn($a, $b) => $a['y'] - $b['y']);

            // 1단 레이아웃인 경우 (한쪽 열에만 문제가 있으면) y좌표로만 정렬
            if (empty($leftCol) || empty($rightCol)) {
                $allItems = array_merge($leftCol, $rightCol);
                usort($allItems, fn($a, $b) => $a['y'] - $b['y']);
                $data = array_merge($data, $allItems);
            } else {
                // 2단 레이아웃: 좌측 열 → 우측 열
                $data = array_merge($data, $leftCol, $rightCol);
            }
        }

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

                $sourceWidth = $imagick->getImageWidth();
                $sourceHeight = $imagick->getImageHeight();

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
                    'metadata' => [
                        ...$question,
                        'sourceWidth' => $sourceWidth,
                        'sourceHeight' => $sourceHeight,
                    ],
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

    public function extractQuestions($data, $data2)
    {
        $number = $data['number'];
        $margin = $data2['margin'];

        $filePath = "storage/app/public/converted-pdfs/{$this->id}/page_{$number}.jpg";
        $fullPath = env('APP_ABSOLUTE_PATH') . '/' . $filePath;

        try {
            // HTTP 요청 보내기
            $response = Http::get(env('EXTRACT_SERVER_URL') . '/detect_problems', [
                'input_path' => $fullPath,
                'top_crop' => $margin['top'] / 100,
                'bottom_crop' => (100 - $margin['bottom']) / 100,
                'left_crop' => $margin['left'] / 100,
                'right_crop' => (100 - $margin['right']) / 100,
            ]);

            // JSON 응답 확인 및 반환
            if ($response->successful()) {
                return $response->json();
            }

            // 에러 처리
            return [];
        } catch (\Exception $e) {
            return [];
        }
    }

    public function addQuestions($questions)
    {
        DB::transaction(function () use ($questions) {
            foreach ($questions as $question) {
                $questionSub1 = $question['sub1_data'] ?? null;
                $questionSub2 = $question['sub2_data'] ?? null;

                unset($question['sub1_data']);
                unset($question['sub2_data']);

                $createdQuestion = QuestionResource::handleCreate($question['data']);

                if ($questionSub1) {
                    $questionSub1['parent_question_id'] = $createdQuestion->id;
                    QuestionResource::handleCreate($questionSub1);
                }
                if ($questionSub2) {
                    $questionSub2['parent_question_id'] = $createdQuestion->id;
                    QuestionResource::handleCreate($questionSub2);
                }
            }

            Notification::make()
                ->title('문제가 등록되었습니다')
                ->success()
                ->send();
            redirect('/admin/questions');
        });
    }
}
