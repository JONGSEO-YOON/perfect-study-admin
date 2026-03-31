<?php

namespace App\Filament\Pages;

use App\Models\TempData;
use Filament\Pages\Page;
use Livewire\Attributes\Url;

class SelectExamTestSheet extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $slug = 'test-sheets/exam-select/{examType}';

    protected static string $view = 'filament.pages.select-exam-test-sheet';

    protected static bool $shouldRegisterNavigation = false;

    protected ?string $maxContentWidth = 'full';

    protected static ?string $title = '기출 문제지 선택';

    public string $examType = 'mock_exam'; // mock_exam or school_exam

    // 선택된 값들
    public array $selectedGrades = [];
    public array $selectedYears = [];
    public array $selectedMonths = [];
    public array $selectedSubjects = [];
    public string $creationMethod = 'number'; // number or category

    // 학교기출 전용
    public ?int $schoolId = null;
    public ?string $schoolName = null;
    public array $selectedSemesters = [];
    public array $selectedExamTypes = [];

    // 더보기
    public bool $showAllYears = false;

    // 과목 목록
    public array $subjectOptions = [];

    public function mount(string $examType = 'mock_exam')
    {
        $this->examType = $examType;
        $this->subjectOptions = $this->getSubjectOptions();
    }

    protected function getSubjectOptions(): array
    {
        // 고/고3 루트 카테고리 하위의 과목(depth=1)을 자동으로 가져옴
        $rootIds = \App\Models\QuestionCategory::where('depth', 0)
            ->whereRaw("REPLACE(REPLACE(name, '<p>', ''), '</p>', '') IN ('고', '고3')")
            ->pluck('id');

        return \App\Models\QuestionCategory::where('depth', 1)
            ->whereIn('id', function ($q) use ($rootIds) {
                $q->select('descendant_id')
                    ->from('question_category_closure')
                    ->where('depth', 1)
                    ->whereIn('ancestor_id', $rootIds);
            })
            ->orderBy('id')
            ->pluck('name')
            ->map(fn($name) => trim(str_replace('(2025개정)', '', strip_tags(trim($name)))))
            ->filter(fn($name) => !in_array($name, ['교과외', '연산문제']))
            ->unique()
            ->mapWithKeys(fn($name) => [$name => $name])
            ->toArray();
    }

    public function getTitle(): string
    {
        return $this->examType === 'mock_exam' ? '수능/모의고사 선택' : '학교 기출 선택';
    }

    public function toggleGrade(string $grade)
    {
        if ($grade === 'all') {
            $this->selectedGrades = [];
            return;
        }
        if (in_array($grade, $this->selectedGrades)) {
            $this->selectedGrades = array_values(array_diff($this->selectedGrades, [$grade]));
        } else {
            $this->selectedGrades[] = $grade;
        }
    }

    public function toggleYear($year)
    {
        if ($year === 'all') {
            $this->selectedYears = [];
            return;
        }
        $year = (int) $year;
        if (in_array($year, $this->selectedYears)) {
            $this->selectedYears = array_values(array_diff($this->selectedYears, [$year]));
        } else {
            $this->selectedYears[] = $year;
        }
    }

    public function toggleMonth($month)
    {
        if ($month === 'all') {
            $this->selectedMonths = [];
            return;
        }
        $month = (int) $month;
        if (in_array($month, $this->selectedMonths)) {
            $this->selectedMonths = array_values(array_diff($this->selectedMonths, [$month]));
        } else {
            $this->selectedMonths[] = $month;
        }
    }

    public function toggleSubject(string $subject)
    {
        if ($subject === 'all') {
            $this->selectedSubjects = [];
            return;
        }
        if (in_array($subject, $this->selectedSubjects)) {
            $this->selectedSubjects = array_values(array_diff($this->selectedSubjects, [$subject]));
        } else {
            $this->selectedSubjects[] = $subject;
        }
    }

    public function toggleSemester($semester)
    {
        if ($semester === 'all') {
            $this->selectedSemesters = [];
            return;
        }
        $semester = (int) $semester;
        if (in_array($semester, $this->selectedSemesters)) {
            $this->selectedSemesters = array_values(array_diff($this->selectedSemesters, [$semester]));
        } else {
            $this->selectedSemesters[] = $semester;
        }
    }

    public function toggleExamType(string $type)
    {
        if ($type === 'all') {
            $this->selectedExamTypes = [];
            return;
        }
        if (in_array($type, $this->selectedExamTypes)) {
            $this->selectedExamTypes = array_values(array_diff($this->selectedExamTypes, [$type]));
        } else {
            $this->selectedExamTypes[] = $type;
        }
    }

    public function proceed()
    {
        $data = [
            'source_type' => $this->examType,
            'creation_method' => $this->creationMethod,
            'target_group' => null,
        ];

        // 과목은 항상 전달 (복수 선택)
        $data['exam_subjects'] = $this->selectedSubjects ?: null;

        if ($this->examType === 'mock_exam') {
            if ($this->creationMethod === 'number') {
                $data['exam_grade'] = $this->selectedGrades[0] ?? null;
                $data['exam_year'] = $this->selectedYears[0] ?? null;
                $data['exam_month'] = $this->selectedMonths[0] ?? null;
            } else {
                $data['exam_grades'] = $this->selectedGrades ?: null;
                $data['exam_years'] = $this->selectedYears ?: null;
                $data['exam_months'] = $this->selectedMonths ?: null;
            }
        } else {
            $data['school_id'] = $this->schoolId;
            if ($this->creationMethod === 'number') {
                $data['exam_grade'] = $this->selectedGrades[0] ?? null;
                $data['exam_year'] = $this->selectedYears[0] ?? null;
                $data['exam_semester'] = $this->selectedSemesters[0] ?? null;
                $data['exam_type'] = $this->selectedExamTypes[0] ?? null;
            } else {
                $data['exam_grades'] = $this->selectedGrades ?: null;
                $data['exam_years'] = $this->selectedYears ?: null;
                $data['exam_semesters'] = $this->selectedSemesters ?: null;
                $data['exam_types'] = $this->selectedExamTypes ?: null;
            }
        }

        $tempData = TempData::create(['value' => $data]);
        return redirect('/admin/test-sheets/create/' . $tempData->id);
    }

    public function searchSchool(string $search)
    {
        if (strlen($search) < 2) return [];
        return \App\Models\School::where('name', 'like', "%{$search}%")
            ->limit(20)
            ->pluck('name', 'id')
            ->toArray();
    }

    public function selectSchool(int $id, string $name)
    {
        $this->schoolId = $id;
        $this->schoolName = $name;
    }
}
