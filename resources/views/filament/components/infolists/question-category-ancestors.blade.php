@php
    $record = $getRecord();
    $ancestors = $record->ancestors()->get();
    $ancestors->push($record);
    // sort by depth
    $ancestors = $ancestors->sortBy('depth');
@endphp

<div class="">
    <div class="grid flex-1 auto-cols-fr gap-y-8">
        <div class="flex flex-col gap-y-6">
            <div ax-load="" ax-load-src="/js/filament/tables/components/table.js?v=3.2.110.0" x-data="table"
                class="fi-ta">
                <div
                    class="fi-ta-ctn divide-y divide-gray-200 overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:divide-white/10 dark:bg-gray-900 dark:ring-white/10">
                    <div
                        class="fi-ta-content relative divide-y divide-gray-200 overflow-x-auto dark:divide-white/10 dark:border-t-white/10 !border-t-0">
                        <table
                            class="fi-ta-table w-full table-auto divide-y divide-gray-200 text-start dark:divide-white/5">

                            <thead class="divide-y divide-gray-200 dark:divide-white/5">
                                <tr class="bg-gray-50 dark:bg-white/5">
                                    <th class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6 fi-table-header-cell-name"
                                        style=";">
                                        <button aria-label="이름" type="button" wire:click="sortTable('name')"
                                            class="group flex w-full items-center gap-x-1 whitespace-nowrap justify-start">
                                            <span
                                                class="fi-ta-header-cell-label text-sm font-semibold text-gray-950 dark:text-white">
                                                상위 항목
                                            </span>

                                        </button>
                                    </th>
                                    <th class="w-1"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 whitespace-nowrap dark:divide-white/5">
                                @foreach ($ancestors as $ancestor)
                                    <tr
                                        class="fi-ta-row [@media(hover:hover)]:transition [@media(hover:hover)]:duration-75 hover:bg-gray-50 dark:hover:bg-white/5 {{ $ancestor->id == $record->id ? 'bg-gray-100 dark:bg-white/10' : '' }}">
                                        <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-table-cell-name"
                                            wire:key="nyOfF7KneS3u1oCpKUvP.table.record.1.column.name">
                                            <div class="fi-ta-col-wrp">
                                                <a href="/admin/question-categories/{{ $ancestor->id }}"
                                                    class="flex w-full disabled:pointer-events-none justify-start text-start">
                                                    <div class="fi-ta-text grid w-full gap-y-1 px-3 py-4">
                                                        <div class="flex ">
                                                            <div class="flex max-w-max" style="">
                                                                <div
                                                                    class="fi-ta-text-item inline-flex items-center gap-1.5  ">
                                                                    <div class="flex items-center gap-x-3 text-sm"
                                                                        @style('margin-left: ' . $ancestor->depth * 16 . 'px;')>
                                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                                            viewBox="0 0 20 20" fill="#666666"
                                                                            class="size-4">
                                                                            <path fill-rule="evenodd"
                                                                                d="M3.75 3a.75.75 0 0 1 .75.75v7.5h10.94l-1.97-1.97a.75.75 0 0 1 1.06-1.06l3.25 3.25a.75.75 0 0 1 0 1.06l-3.25 3.25a.75.75 0 1 1-1.06-1.06l1.97-1.97H3.75A.75.75 0 0 1 3 12V3.75A.75.75 0 0 1 3.75 3Z"
                                                                                clip-rule="evenodd" />
                                                                        </svg>

                                                                        <img src="/images/scope.svg" alt="범위"
                                                                            class="w-5 h-5"> {!! $ancestor->name !!}
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                        </td>
                                        <td
                                            class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-actions-cell">
                                        </td>
                                    </tr>
                                @endforeach

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
