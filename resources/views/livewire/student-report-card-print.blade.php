<div>
    <div class="no-print" style="position: fixed; top: 20px; right: 20px; z-index: 1000;">
        <button onclick="window.print()"
            style="font-size: 16px !important; padding: 10px 20px; background: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer;">
            인쇄하기
        </button>
    </div>
    <div class="page" style="page-break-after: auto">
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
        <div class="page_landscape sm">
            <h1 class="font-bold !text-3xl mb-6" style="font-size: 1.875rem !important; ">
                오답 유형 분석표
            </h1>
            @livewire('report-card-tab-1', [
                'student' => $student,
                'classroomId' => $classroomId,
                'dateFrom' => $dateFrom,
                'dateUntil' => $dateUntil,
                'reportKey' => 'detailed_hierarchy',
            ])
        </div>
        <div class="page_landscape sm">
            <h1 class="font-bold !text-3xl mb-6" style="font-size: 1.875rem !important; ">
                오답 유형 분석표 - 학부모용
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 모든 table 요소를 찾습니다
            const tables = document.getElementsByTagName('table');

            // 각 table 앞에 버튼을 추가합니다
            Array.from(tables).forEach(table => {
                const buttonHtml =
                    `<div class="flex no-print">
    <button class=" my-4 px-4 py-0.5 border rounded-lg flex items-center justify-center">페이지 분리</button>
</div>
`
                table.insertAdjacentHTML('beforebegin', buttonHtml);
            });
        });
    </script>
    <script>
        document.addEventListener('click', function(e) {
            if (e.target.closest('button') && e.target.textContent === '페이지 분리') {
                // 버튼의 부모 div를 찾아서
                const buttonContainer = e.target.closest('.flex.no-print');

                // page-break div로 교체합니다
                buttonContainer.outerHTML = '<div class="page-break w-full border-t border-dashed my-4"></div>';
            }
        });
    </script>

</div>
