<div class="flex-1 overflow-auto h-0">
    <div class="max-w-[740px] mx-auto w-full px-5 py-4">
        <h1 class="w-full text-2xl font-bold pb-4 border-b md:mt-6 flex flex-row items-center gap-x-4">
            <svg width="18" height="20" viewBox="0 0 18 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M0 9C0 5.25 -5.96046e-08 3.375 0.955 2.061C1.26306 1.63667 1.63595 1.26344 2.06 0.955C3.375 -5.96046e-08 5.251 0 9 0C12.749 0 14.625 -5.96046e-08 15.939 0.955C16.3634 1.26336 16.7366 1.6366 17.045 2.061C18 3.375 18 5.251 18 9V11C18 14.75 18 16.625 17.045 17.939C16.7366 18.3634 16.3634 18.7366 15.939 19.045C14.625 20 12.749 20 9 20C5.251 20 3.375 20 2.061 19.045C1.6366 18.7366 1.26336 18.3634 0.955 17.939C-5.96046e-08 16.625 0 14.749 0 11V9Z"
                    fill="currentColor" />
                <path
                    d="M12 11L11.143 9.00004M11.143 9.00004L9.592 5.38204C9.493 5.15004 9.26 5.00004 9 5.00004C8.8746 4.99861 8.75155 5.03406 8.64612 5.10197C8.5407 5.16988 8.45755 5.26727 8.407 5.38204L6.857 9.00004M11.143 9.00004H6.857M6 11L6.857 9.00004M5 15H13"
                    stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            성적표
        </h1>

        <div class="mt-4 flex gap-4">
            <select wire:model.live="dateFrom"
                class="w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                @foreach ($weeks as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>

            <select wire:model.live="classroomId"
                class="w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                @foreach ($classrooms as $classroom)
                    <option value="{{ $classroom->id }}">{{ $classroom->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mt-4 w-full overflow-x-auto mx-auto max-w-[320px] sm:max-w-[740px]">
            @livewire(
                'report-card-tab-3',
                [
                    'readonly' => true,
                    'student' => $student,
                    'classroomId' => $classroomId,
                    'dateFrom' => $dateFrom,
                    'dateUntil' => $dateFrom,
                    'hide' => [
                        'header' => true,
                        'weekRow' => true,
                        'attendance' => true,
                        'comment' => true,
                        'rank' => true,
                    ],
                ],
                key('report-card-' . $dateFrom)
            )
        </div>
    </div>
</div>
