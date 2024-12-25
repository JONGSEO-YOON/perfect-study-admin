<div class="flex-1 overflow-auto h-0">
    <div class="max-w-[740px] mx-auto w-full px-5 py-4">
        <h1 class="w-full text-2xl font-bold pb-4 border-b md:mt-6 flex flex-row items-center gap-x-4">

            <svg width="20" height="17" viewBox="0 0 20 17" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M17 5V11C18.7 11 20 9.7 20 8C20 6.3 18.7 5 17 5ZM9 4H2C0.9 4 0 4.9 0 6V10C0 11.1 0.9 12 2 12H3V15C3 16.1 3.9 17 5 17H7V12H9L13 16H15V0H13L9 4Z"
                    fill="currentColor" />
            </svg>
            공지사항
        </h1>

        <div class="grid grid-cols-1 md:grid-cols-2 mt-6 gap-6">
            @foreach ($notices as $notice)
                <div @click.stop="$dispatch('open-notice-modal', { notice: {{ json_encode($notice) }} })"
                    @class([
                        'bg-white rounded-xl overflow-hidden transition-all hover:-translate-y-1 shadow-md border cursor-pointer',
                        'border-2 border-[#7256C2]/50' => $notice->pinned_at,
                    ])>
                    <div class="p-5 border-b relative">
                        <h2 class="text-lg font-semibold text-gray-900 pr-8">{{ $notice->title }}</h2>
                        @if ($notice->pinned_at)
                            <svg class="w-5 h-5 text-[#7256C2] absolute top-5 right-5" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path
                                    d="M10 2a1 1 0 011 1v1.323l3.954 1.582a1.5 1.5 0 01.646 2.415l-1.505 1.505a1 1 0 01.293.708V14a1 1 0 01-1 1H6.612a1 1 0 01-1-1v-3.467a1 1 0 01.293-.708L4.4 8.32a1.5 1.5 0 01.646-2.415L9 4.323V3a1 1 0 011-1z" />
                            </svg>
                        @endif
                    </div>

                    <div class="p-5 text-gray-600 h-28 overflow-hidden relative">
                        <div class="line-clamp-3">
                            {!! $notice->content !!}
                        </div>
                        <div class="absolute bottom-0 left-0 right-0 h-8 bg-gradient-to-t from-white to-transparent">
                        </div>
                    </div>
                    <div class="px-5 py-4 bg-gray-50 flex justify-between items-center text-sm text-gray-500">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            {{ $notice->created_at->format('Y.m.d') }}
                        </div>
                        @if ($notice->attachments && count($notice->attachments) > 0)
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                </svg>
                                {{ count($notice->attachments) }}개의 첨부파일
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- 모달 컴포넌트를 수정합니다 -->
    <div x-data="{
        show: false,
        notice: null,
    }"
        @open-notice-modal.window="
        notice = $event.detail.notice;
        show = true;
    " x-show="show"
        class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">

        <!-- 백그라운드 오버레이 -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="show = false"></div>

        <!-- 모달 컨텐츠 -->
        <template x-if="notice">
            <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4 sm:items-center ">
                    <div class="w-full relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-4xl"
                        @click.outside="show = false">
                        <!-- 모달 헤더 -->
                        <div class="p-6 border-b border-gray-200">
                            <div class="flex items-center gap-2 text-sm text-gray-600 mb-4">
                                <template x-if="notice?.pinned_at">
                                    <span
                                        class="px-3 py-1 rounded-full bg-[#7256C2]/10 text-[#7256C2] font-medium">공지</span>
                                </template>
                                <span x-text="new Date(notice?.created_at).toLocaleDateString()"></span>
                            </div>
                            <h1 class="text-2xl font-bold text-gray-900" x-text="notice?.title"></h1>
                        </div>

                        <!-- 첨부파일 섹션 -->
                        <template x-if="notice.attachments.length > 0">
                            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                                <div class="flex items-center gap-2 text-sm text-gray-600">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                    </svg>
                                    <span class="font-medium">첨부파일</span>
                                </div>
                                <div class="mt-2 space-y-1">
                                    <template x-for="attachment in notice.attachments" :key="attachment">
                                        <a target="_blank" :href="'/storage/' + attachment"
                                            class="group flex items-center gap-2 text-sm text-gray-600 hover:text-[#7256C2]">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                            </svg>
                                            <span class="group-hover:underline" x-text="attachment"></span>
                                        </a>
                                    </template>
                                </div>
                            </div>
                        </template>

                        <!-- 본문 내용 -->
                        <div class="p-6 max-h-[60vh] overflow-y-auto">
                            <div class="prose prose-sm max-w-none" x-html="notice?.content"></div>
                        </div>

                        <!-- 모달 푸터 -->
                        <div class="bg-gray-50 px-6 py-4 flex justify-end">
                            <button type="button" @click="show = false"
                                class="inline-flex justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                                닫기
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</div>
