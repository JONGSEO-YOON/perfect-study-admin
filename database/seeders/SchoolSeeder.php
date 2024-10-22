<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SchoolSeeder extends Seeder
{
    /**
     * CSV 컬럼과 데이터베이스 컬럼 간의 매핑
     */
    private array $columnMapping = [
        '행정표준코드' => 'administrative_code',
        '학교명' => 'name',
        '학교종류명' => 'school_type',
        '시도명' => 'province',
        '도로명우편번호' => 'postal_code',
        '도로명주소' => 'address',
        '전화번호' => 'phone_number',
        '홈페이지주소' => 'website_url'
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $filepath = storage_path('app/학교기본정보_2024년09월30일기준.csv');

        if (!file_exists($filepath)) {
            $this->command->error('CSV 파일을 찾을 수 없습니다: ' . $filepath);
            return;
        }

        // CSV 파일을 열고 UTF-8로 인코딩 변환
        $file = fopen($filepath, 'r');
        if ($file === false) {
            $this->command->error('파일을 열 수 없습니다.');
            return;
        }

        // BOM 제거
        fgets($file, 4);
        rewind($file);
        $bom = fread($file, 3);
        if ($bom !== pack('CCC', 0xEF, 0xBB, 0xBF)) {
            rewind($file);
        }

        // 헤더 읽기 및 컬럼 인덱스 매핑
        $headers = fgetcsv($file);
        if ($headers === false) {
            $this->command->error('CSV 헤더를 읽을 수 없습니다.');
            fclose($file);
            return;
        }

        // 헤더를 기반으로 컬럼 인덱스 매핑 생성
        $columnIndexes = [];
        foreach ($headers as $index => $header) {
            $header = trim($header); // 공백 제거
            if (isset($this->columnMapping[$header])) {
                $columnIndexes[$this->columnMapping[$header]] = $index;
            }
        }

        // 필수 컬럼이 모두 있는지 확인
        $requiredColumns = array_values($this->columnMapping);
        $missingColumns = array_diff($requiredColumns, array_keys($columnIndexes));
        if (!empty($missingColumns)) {
            $this->command->error('필수 컬럼이 없습니다: ' . implode(', ', $missingColumns));
            fclose($file);
            return;
        }

        // 청크 단위로 처리하여 메모리 사용량 최적화
        $chunkSize = 1000;
        $chunk = [];
        $row = 0;

        DB::beginTransaction();
        try {
            while (($data = fgetcsv($file)) !== false) {
                $row++;

                // CSV 데이터를 매핑된 인덱스를 사용하여 변환
                $chunk[] = [
                    'administrative_code' => $data[$columnIndexes['administrative_code']] ?? '',
                    'name' => $data[$columnIndexes['name']] ?? '',
                    'school_type' => $data[$columnIndexes['school_type']] ?? '',
                    'province' => $data[$columnIndexes['province']] ?? '',
                    'postal_code' => $data[$columnIndexes['postal_code']] ?? '',
                    'address' => $data[$columnIndexes['address']] ?? '',
                    'phone_number' => $data[$columnIndexes['phone_number']] ?? '',
                    'website_url' => $data[$columnIndexes['website_url']] ?? '',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                // 청크 크기에 도달하면 삽입
                if (count($chunk) >= $chunkSize) {
                    DB::table('schools')->insert($chunk);
                    $this->command->info("{$row}개의 데이터 처리 완료");
                    $chunk = [];
                }
            }

            // 남은 데이터 삽입
            if (!empty($chunk)) {
                DB::table('schools')->insert($chunk);
            }

            DB::commit();
            $this->command->info("총 {$row}개의 학교 데이터 가져오기 완료");
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('데이터 삽입 중 오류 발생: ' . $e->getMessage());
        } finally {
            fclose($file);
        }
    }
}
