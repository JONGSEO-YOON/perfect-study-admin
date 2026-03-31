@php
    $classroom = $getRecord();
    $students = $classroom->students()
        ->where('status', '!=', 'withdrawn')
        ->with(['user', 'gradeSystem'])
        ->get()
        ->sortBy('user.name');
    $teacher = $classroom->teacher?->user;
    $subTeacher = $classroom->subTeacher?->user;
@endphp

<div>
    <table class="w-full border-collapse border border-gray-300 dark:border-gray-600 text-sm">
        <thead>
            <tr class="bg-gray-100 dark:bg-gray-700">
                <th class="border border-gray-300 dark:border-gray-600 px-3 py-2 text-center font-semibold" colspan="3">
                    {{ $classroom->name }}
                </th>
            </tr>
            <tr>
                <th class="border border-gray-300 dark:border-gray-600 px-3 py-1.5 text-left font-medium bg-gray-50 dark:bg-gray-700/50 w-24">담임</th>
                <td class="border border-gray-300 dark:border-gray-600 px-3 py-1.5" colspan="2">{{ $teacher?->name ?? '-' }}</td>
            </tr>
            <tr>
                <th class="border border-gray-300 dark:border-gray-600 px-3 py-1.5 text-left font-medium bg-gray-50 dark:bg-gray-700/50 w-24">부담임</th>
                <td class="border border-gray-300 dark:border-gray-600 px-3 py-1.5" colspan="2">{{ $subTeacher?->name ?? '-' }}</td>
            </tr>
            <tr class="bg-gray-50 dark:bg-gray-700/50">
                <th class="border border-gray-300 dark:border-gray-600 px-3 py-1.5 text-center font-medium w-16">No</th>
                <th class="border border-gray-300 dark:border-gray-600 px-3 py-1.5 text-left font-medium">이름</th>
                <th class="border border-gray-300 dark:border-gray-600 px-3 py-1.5 text-left font-medium">학년</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($students as $index => $student)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                    <td class="border border-gray-300 dark:border-gray-600 px-3 py-1.5 text-center">{{ $index + 1 }}</td>
                    <td class="border border-gray-300 dark:border-gray-600 px-3 py-1.5">{{ $student->user?->name ?? '-' }}</td>
                    <td class="border border-gray-300 dark:border-gray-600 px-3 py-1.5">{{ $student->gradeSystem?->display_name ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td class="border border-gray-300 dark:border-gray-600 px-3 py-4 text-center text-gray-500" colspan="3">
                        등록된 학생이 없습니다.
                    </td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="bg-gray-50 dark:bg-gray-700/50">
                <td class="border border-gray-300 dark:border-gray-600 px-3 py-1.5 text-center font-medium" colspan="2">총 학생 수</td>
                <td class="border border-gray-300 dark:border-gray-600 px-3 py-1.5 font-semibold">{{ $students->count() }}명</td>
            </tr>
        </tfoot>
    </table>
</div>
