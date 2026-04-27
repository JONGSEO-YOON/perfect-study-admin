<?php

namespace App\Livewire;

use App\Models\TestSheet;
use App\Models\Student;
use Livewire\Component;
use Livewire\Attributes\On;

class TestSheetTypeAnalysis extends Component
{
    public TestSheet $testsheet;
    public ?int $studentId = null;
    public array $rows = [];
    public ?string $studentName = null;
    public array $studentOptions = [];

    public function mount(TestSheet $testsheet)
    {
        $this->testsheet = $testsheet;
        $this->loadStudentOptions();
    }

    protected function loadStudentOptions(): void
    {
        $students = $this->testsheet->getTargetStudents();
        $this->studentOptions = $students
            ->mapWithKeys(fn($s) => [$s->id => $s->user?->name ?? '-'])
            ->toArray();
    }

    public function updatedStudentId($value): void
    {
        $this->loadAnalysis();
    }

    protected function loadAnalysis(): void
    {
        $this->rows = [];
        $this->studentName = null;

        if (!$this->studentId) return;

        $student = Student::withoutGlobalScopes()->with('user')->find($this->studentId);
        if (!$student) return;

        $this->studentName = $student->user?->name;

        $report = collect($this->testsheet->report ?? [])
            ->firstWhere('student_id', $student->id);

        if (!$report) return;

        $hierarchy = $report['hierarchical_analysis'] ?? [];
        foreach ($hierarchy as $major => $majorData) {
            foreach (($majorData['sub_categories'] ?? []) as $middle => $middleData) {
                foreach (($middleData['types'] ?? []) as $type => $typeData) {
                    $levels = [];
                    foreach (($typeData['levels'] ?? []) as $level => $data) {
                        $levels[$level] = $data;
                    }
                    $this->rows[] = [
                        'major' => $majorData['name'] ?? $major,
                        'middle' => $middleData['name'] ?? $middle,
                        'type' => $typeData['name'] ?? $type,
                        'levels' => $levels,
                    ];
                }
            }
        }
    }

    public function render()
    {
        return view('livewire.test-sheet-type-analysis');
    }
}
