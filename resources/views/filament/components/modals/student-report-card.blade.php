<div class="modal-content-student-report-card h-[700px] overflow-auto p-1">
    @livewire('report-card', [
        'student' => $record,
        'classroomId' => $classroomId,
    ])
</div>
