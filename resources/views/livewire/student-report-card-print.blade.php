<div>
    <div class="page">
        <h1 class="font-bold !text-3xl">
            주간 학습표
        </h1>
        @livewire('report-card-tab-3', [
            'readonly' => true,
            'student' => $student,
            'classroomId' => $classroomId,
            'dateFrom' => $dateFrom,
            'dateUntil' => $dateUntil,
        ])
    </div>


    @if ($this->student->gradeSystem->display_name == '고3')
        <div class="page sm">
            <h1 class="font-bold !text-3xl mb-6" style="font-size: 1.875rem !important; ">
                오답 유형 분석표
            </h1>
            @livewire('report-card-tab-1-type-2', [
                'student' => $student,
                'classroomId' => $classroomId,
                'dateFrom' => $dateFrom,
                'dateUntil' => $dateUntil,
            ])
        </div>
    @else
        <div class="page sm">
            <h1 class="font-bold !text-3xl mb-6" style="font-size: 1.875rem !important; ">
                오답 유형 분석표
            </h1>
            @livewire('report-card-tab-1', [
                'student' => $student,
                'classroomId' => $classroomId,
                'dateFrom' => $dateFrom,
                'dateUntil' => $dateUntil,
            ])
        </div>
    @endif

    <div class="page">
        <h1 class="font-bold !text-3xl mb-6" style="font-size: 1.875rem !important; ">
            오답 문풀 분석표
        </h1>
        @livewire('report-card-tab-2', [
            'student' => $student,
            'classroomId' => $classroomId,
            'dateFrom' => $dateFrom,
            'dateUntil' => $dateUntil,
        ])
    </div>

</div>
