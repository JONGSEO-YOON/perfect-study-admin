<div class="space-y-4">
    <div class="grid grid-cols-2 gap-4">
        <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">기본 정보</h3>
            <dl class="space-y-2">
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-600 dark:text-gray-300">이름</dt>
                    <dd class="text-sm font-medium">{{ $record->user->name }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-600 dark:text-gray-300">학교</dt>
                    <dd class="text-sm font-medium">{{ $record->school?->name ?? '-' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-600 dark:text-gray-300">학년</dt>
                    <dd class="text-sm font-medium">{{ $record->gradeSystem?->display_name ?? '-' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-600 dark:text-gray-300">전화번호</dt>
                    <dd class="text-sm font-medium">{{ $record->user->phone ?? '-' }}</dd>
                </div>
            </dl>
        </div>
        
        <div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-4">
            <h3 class="text-sm font-medium text-red-600 dark:text-red-400 mb-2">퇴원 정보</h3>
            <dl class="space-y-2">
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-600 dark:text-gray-300">퇴원일</dt>
                    <dd class="text-sm font-medium">{{ $record->withdrawn_at?->format('Y-m-d') ?? '-' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-600 dark:text-gray-300">담임</dt>
                    <dd class="text-sm font-medium">{{ $record->homeroomTeacher?->user?->name ?? '-' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-600 dark:text-gray-300">퇴원사유</dt>
                    <dd class="text-sm font-medium">
                        @if($record->withdrawal_reason)
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                @switch($record->withdrawal_reason)
                                    @case('poor_performance') bg-red-100 text-red-800 @break
                                    @case('teacher_mismatch') bg-yellow-100 text-yellow-800 @break
                                    @case('academy_atmosphere') bg-yellow-100 text-yellow-800 @break
                                    @case('relocation') bg-blue-100 text-blue-800 @break
                                    @default bg-gray-100 text-gray-800
                                @endswitch
                            ">
                                {{ \App\Models\Student::WITHDRAWAL_REASONS[$record->withdrawal_reason] ?? $record->withdrawal_reason }}
                            </span>
                        @else
                            -
                        @endif
                    </dd>
                </div>
                @if($record->withdrawal_reason_detail)
                <div>
                    <dt class="text-sm text-gray-600 dark:text-gray-300 mb-1">상세 사유</dt>
                    <dd class="text-sm bg-white dark:bg-gray-700 rounded p-2">{{ $record->withdrawal_reason_detail }}</dd>
                </div>
                @endif
            </dl>
        </div>
    </div>

    @if($record->enrollmentHistory->count() > 0)
    <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">입퇴원 이력</h3>
        <div class="space-y-2">
            @foreach($record->enrollmentHistory as $history)
            <div class="flex items-center justify-between py-2 border-b border-gray-200 dark:border-gray-700 last:border-0">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                        @switch($history->action)
                            @case('enroll') bg-green-100 text-green-800 @break
                            @case('withdraw') bg-red-100 text-red-800 @break
                            @case('re_enroll') bg-blue-100 text-blue-800 @break
                        @endswitch
                    ">
                        {{ $history->action_label }}
                    </span>
                    <span class="text-sm text-gray-600 dark:text-gray-300">
                        {{ $history->action_date->format('Y-m-d') }}
                    </span>
                </div>
                @if($history->reason)
                <span class="text-xs text-gray-500">
                    {{ \App\Models\Student::WITHDRAWAL_REASONS[$history->reason] ?? $history->reason }}
                </span>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
