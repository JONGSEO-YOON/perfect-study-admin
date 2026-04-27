<?php

namespace App\Livewire\Parent;

use Livewire\Component;
use App\Models\Academy;
use App\Models\Student;

class Header extends Component
{
    public $academies = [];
    public $students = [];
    public $academyId;
    public $studentId;

    public function mount()
    {
        $parent_phone = session('parent_phone');

        // 부모 전화번호로 등록된 모든 학생 조회
        $allStudents = Student::with(['user', 'academy'])
            ->where('phone_father', $parent_phone)
            ->orWhere('phone_mother', $parent_phone)
            ->get();

        // 학원 목록 추출 (중복 제거)
        $this->academies = $allStudents->pluck('academy')
            ->filter()
            ->unique('id')
            ->values()
            ->toArray();

        // 세션에 저장된 학원이 있으면 사용, 없으면 첫 번째 학원
        $this->academyId = session('parent_academy_id', $this->academies[0]['id'] ?? null);

        // 선택된 학원의 학생만 필터링
        $this->filterStudents($allStudents);

        // 세션에 학원 저장
        session(['parent_academy_id' => $this->academyId]);
    }

    public function changeAcademy()
    {
        // 세션에 학원 저장
        session(['parent_academy_id' => $this->academyId]);
        session()->save();

        // ResolveAcademy 미들웨어가 새 academy_id로 currentAcademy를 다시 계산하도록
        // 현재 페이지를 풀 리다이렉트로 새로고침
        $referer = request()->header('referer');
        return $this->redirect($referer ?: '/parent', navigate: false);
    }

    public function changeStudent()
    {
        $this->dispatch('change-student', id: $this->studentId);
    }

    protected function filterStudents($allStudents)
    {
        $filtered = $allStudents->filter(fn($s) => $s->academy_id == $this->academyId)->values();

        $this->students = $filtered->isEmpty()
            ? $allStudents->values()->toArray()
            : $filtered->toArray();

        // 현재 선택된 학생이 필터된 목록에 없으면 첫 번째로
        $studentIds = collect($this->students)->pluck('id')->toArray();
        if (!in_array($this->studentId, $studentIds)) {
            $this->studentId = $studentIds[0] ?? null;
        }
    }

    public function render()
    {
        return view('livewire.parent.header');
    }
}
