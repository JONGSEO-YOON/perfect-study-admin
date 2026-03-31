<x-filament-panels::page>
    <style>
        .exam-grid { display: grid; gap: 0; border: 1px solid #e5e7eb; border-radius: 0.5rem; overflow: hidden; }
        .exam-grid-2 { grid-template-columns: repeat(2, 1fr); }
        .exam-grid-3 { grid-template-columns: repeat(3, 1fr); }
        .exam-btn {
            padding: 0.625rem 0.5rem; text-align: center; cursor: pointer; font-size: 0.875rem;
            border: 0.5px solid #e5e7eb; background: #fff; transition: all 0.15s;
            color: #374151; user-select: none;
        }
        .exam-btn:hover { background: #f3f4f6; }
        .exam-btn.selected { background: #eff6ff; color: #2563eb; font-weight: 600; border-color: #93c5fd; }
        .exam-btn.all-btn { color: #6b7280; font-weight: 500; }
        .exam-btn.all-btn.selected { background: #f0fdf4; color: #16a34a; border-color: #86efac; }
        .section-title { font-size: 0.9rem; font-weight: 700; color: #111827; margin-bottom: 0.5rem; }
        .section-sub { font-size: 0.7rem; color: #9ca3af; font-weight: 400; margin-left: 0.375rem; }
        .method-btn {
            padding: 0.75rem 1.5rem; border: 2px solid #e5e7eb; border-radius: 0.5rem;
            cursor: pointer; font-size: 0.875rem; font-weight: 500; transition: all 0.15s;
            text-align: center; background: #fff;
        }
        .method-btn:hover { border-color: #93c5fd; }
        .method-btn.selected { border-color: #3b82f6; background: #eff6ff; color: #2563eb; }
    </style>

    <div class="space-y-6">
        {{-- 제목 --}}
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-bold text-gray-900">
                @if ($examType === 'mock_exam')
                    수능/모의고사 선택 🏫
                @else
                    학교 기출 선택 🏫
                @endif
            </h2>
            <a href="/admin/test-sheets" class="text-sm text-gray-500 hover:text-gray-700">← 목록으로</a>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            {{-- 학교 기출: 학교 검색 --}}
            @if ($examType === 'school_exam')
                <div class="mb-6" x-data="{ search: '', results: [], open: false }">
                    <div class="section-title">학교 선택</div>
                    <div class="relative">
                        @if ($schoolName)
                            <div class="flex items-center gap-2 p-3 bg-blue-50 rounded-lg border border-blue-200">
                                <span class="text-sm font-medium text-blue-700">{{ $schoolName }}</span>
                                <button wire:click="selectSchool(0, '')" class="text-blue-400 hover:text-blue-600 text-xs ml-auto">변경</button>
                            </div>
                        @else
                            <input
                                type="text"
                                x-model="search"
                                @input.debounce.300ms="
                                    if (search.length >= 2) {
                                        $wire.searchSchool(search).then(r => { results = Object.entries(r); open = true; });
                                    } else { results = []; open = false; }
                                "
                                placeholder="학교명을 입력하세요 (2글자 이상)"
                                class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                            <div x-show="open && results.length > 0" x-cloak
                                class="absolute z-10 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg max-h-48 overflow-y-auto">
                                <template x-for="[id, name] in results" :key="id">
                                    <button
                                        @click="$wire.selectSchool(parseInt(id), name); open = false; search = '';"
                                        class="block w-full text-left px-4 py-2 text-sm hover:bg-gray-50"
                                        x-text="name"
                                    ></button>
                                </template>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-{{ $examType === 'mock_exam' ? '4' : '5' }} gap-6">
                {{-- 학년 선택 --}}
                <div>
                    <div class="section-title">학년 선택 <span class="section-sub">복수 선택 가능</span></div>
                    <div class="exam-grid exam-grid-2">
                        <div class="exam-btn all-btn {{ empty($selectedGrades) ? 'selected' : '' }}"
                            wire:click="toggleGrade('all')">전체</div>
                        @foreach (['고1', '고2', '고3'] as $grade)
                            <div class="exam-btn {{ in_array($grade, $selectedGrades) ? 'selected' : '' }}"
                                wire:click="toggleGrade('{{ $grade }}')">{{ $grade }}</div>
                        @endforeach
                    </div>
                </div>

                {{-- 년도 선택 --}}
                <div>
                    <div class="section-title">년도 선택 <span class="section-sub">복수 선택 가능</span></div>
                    <div class="exam-grid exam-grid-2">
                        <div class="exam-btn all-btn {{ empty($selectedYears) ? 'selected' : '' }}"
                            wire:click="toggleYear('all')">전체</div>
                        @php
                            $years = range(date('Y'), 2015, -1);
                            $displayYears = $showAllYears ? $years : array_slice($years, 0, 5);
                        @endphp
                        @foreach ($displayYears as $year)
                            <div class="exam-btn {{ in_array($year, $selectedYears) ? 'selected' : '' }}"
                                wire:click="toggleYear({{ $year }})">{{ $year }}년</div>
                        @endforeach
                        @if (!$showAllYears && count($years) > 5)
                            <div class="exam-btn all-btn" wire:click="$set('showAllYears', true)" style="grid-column: span 2;">
                                ⚙ 더보기
                            </div>
                        @endif
                    </div>
                </div>

                {{-- 모의고사: 월 선택 --}}
                @if ($examType === 'mock_exam')
                    <div>
                        <div class="section-title">월 선택 <span class="section-sub">복수 선택 가능</span></div>
                        <div class="exam-grid exam-grid-2">
                            <div class="exam-btn all-btn {{ empty($selectedMonths) ? 'selected' : '' }}"
                                wire:click="toggleMonth('all')">전체</div>
                            @foreach ([3, 4, 5, 6, 7, 9, 10, 11] as $month)
                                <div class="exam-btn {{ in_array($month, $selectedMonths) ? 'selected' : '' }}"
                                    wire:click="toggleMonth({{ $month }})">{{ $month }}월</div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- 학교기출: 학기 선택 --}}
                @if ($examType === 'school_exam')
                    <div>
                        <div class="section-title">학기 선택 <span class="section-sub">복수 선택 가능</span></div>
                        <div class="exam-grid exam-grid-2">
                            <div class="exam-btn all-btn {{ empty($selectedSemesters) ? 'selected' : '' }}"
                                wire:click="toggleSemester('all')">전체</div>
                            <div class="exam-btn {{ in_array(1, $selectedSemesters) ? 'selected' : '' }}"
                                wire:click="toggleSemester(1)">1학기</div>
                            <div class="exam-btn {{ in_array(2, $selectedSemesters) ? 'selected' : '' }}"
                                wire:click="toggleSemester(2)">2학기</div>
                        </div>
                    </div>

                    {{-- 시험유형 --}}
                    <div>
                        <div class="section-title">시험 유형 <span class="section-sub">복수 선택 가능</span></div>
                        <div class="exam-grid exam-grid-2">
                            <div class="exam-btn all-btn {{ empty($selectedExamTypes) ? 'selected' : '' }}"
                                wire:click="toggleExamType('all')">전체</div>
                            <div class="exam-btn {{ in_array('midterm', $selectedExamTypes) ? 'selected' : '' }}"
                                wire:click="toggleExamType('midterm')">중간고사</div>
                            <div class="exam-btn {{ in_array('final', $selectedExamTypes) ? 'selected' : '' }}"
                                wire:click="toggleExamType('final')">기말고사</div>
                        </div>
                    </div>
                @endif

                {{-- 문제 추가 옵션 --}}
                <div>
                    <div class="section-title">문제 추가 옵션 ⚙</div>
                    <div class="space-y-2">
                        <div class="method-btn {{ $creationMethod === 'number' ? 'selected' : '' }}"
                            wire:click="$set('creationMethod', 'number')">
                            문제 번호로 추가
                        </div>
                        <div class="method-btn {{ $creationMethod === 'category' ? 'selected' : '' }}"
                            wire:click="$set('creationMethod', 'category')">
                            단원으로 추가
                        </div>
                    </div>
                </div>
            </div>

            {{-- 과목 선택 --}}
            <div class="mt-6">
                <div class="section-title">과목 선택 <span class="section-sub">복수 선택 가능</span></div>
                <div class="exam-grid" style="grid-template-columns: repeat(7, 1fr); max-width: 700px;">
                    <div class="exam-btn all-btn {{ empty($selectedSubjects) ? 'selected' : '' }}"
                        wire:click="toggleSubject('all')">전체</div>
                    @foreach ($subjectOptions as $key => $label)
                        <div class="exam-btn {{ in_array($key, $selectedSubjects) ? 'selected' : '' }}"
                            wire:click="toggleSubject('{{ $key }}')">{{ $label }}</div>
                    @endforeach
                </div>
            </div>

            {{-- 다음 단계 버튼 --}}
            <div class="mt-8 flex justify-end">
                <button
                    wire:click="proceed"
                    class="px-6 py-2.5 bg-blue-600 text-white rounded-lg font-medium text-sm hover:bg-blue-700 transition shadow-sm"
                >
                    문제 선택으로 이동 →
                </button>
            </div>
        </div>
    </div>
</x-filament-panels::page>
