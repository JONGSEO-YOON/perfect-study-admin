<?php

namespace App\Livewire\Parent;

use App\Models\Student;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Carbon\Carbon;

class ReportDetail extends Component
{
    public $weekKey;
    public $student;
    public $classroomId;
    public $activeTab = 1;
    public $weekLabel;

    public function mount($weekKey)
    {
        $this->weekKey = urldecode($weekKey);
        
        $parent_phone = session('parent_phone');
        $this->student = Student::where('phone_father', $parent_phone)
            ->orWhere('phone_mother', $parent_phone)
            ->first();
        
        $this->classroomId = $this->student->classrooms->first()?->id ?? null;
        
        $this->generateWeekLabel();
    }

    private function generateWeekLabel()
    {
        if ($this->weekKey) {
            // weekKey 파싱: "2024-08-19 ~ 2024-08-25"
            $dates = explode(' ~ ', $this->weekKey);
            if (count($dates) >= 2) {
                $startDate = Carbon::parse($dates[0]);
                $year = $startDate->year; // 2024 (Report.php와 동일하게 전체 연도 사용)
                $month = $startDate->format('n'); // Report.php와 동일한 형식
                $weekOfMonth = $startDate->weekOfMonth; // Carbon 내장 메소드 사용
                
                $this->weekLabel = "{$year}년 {$month}월 {$weekOfMonth}주차";
                return;
            }
        }
        
        // 기본값: 현재 날짜 기준
        $now = Carbon::now();
        $year = $now->year;
        $month = $now->format('n');
        $weekOfMonth = $now->weekOfMonth;
        
        $this->weekLabel = "{$year}년 {$month}월 {$weekOfMonth}주차";
    }



    public function setActiveTab($tabNumber)
    {
        $this->activeTab = $tabNumber;
    }

    #[Layout('layouts.parent')]
    public function render()
    {
        return view('livewire.parent.report-detail');
    }
}