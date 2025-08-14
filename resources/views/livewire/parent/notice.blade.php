<div class="min-h-screen bg-gray-50">

    <!-- Main Content Area -->
    <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">


        <!-- Notice Detail Card -->
        <div class="bg-stone-100  rounded-lg shadow-md overflow-hidden">
            <!-- Notice Header -->
            <div class="px-6 py-5 border-b border-gray-200">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <!-- Notice Title -->
                        <h1 class="text-2xl font-bold text-gray-900 mb-3">
                            {{ $notice->title ?? '공지사항 제목' }}
                        </h1>
                        
                        <!-- Notice Meta Info -->
                        <div class="flex flex-col items-start text-sm text-gray-500">
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                작성자: {{ $notice->author->name ?? '관리자' }}
                            </div>
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                {{ $notice->created_at ? $notice->created_at->format('Y년 m월 d일 H:i') : '2024년 10월 30일 15:30' }}
                            </div>
                        </div>
                    </div>
  
                </div>

   
            </div>

            <!-- Notice Content -->
            <div class="px-6 py-6">
                <div class="max-w-none">
                    <div class="text-gray-800 leading-relaxed whitespace-pre-wrap">
                        {!! $notice->content !!}
                    </div>
                </div>
            </div>

            <!-- Attachments Section -->
            @if($notice->attachments ?? false)
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                <h3 class="text-sm font-medium text-gray-900 mb-3 flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                    </svg>
                    첨부파일
                </h3>
                <div class="space-y-2">
                    @foreach(json_decode($notice->attachments ?? '[]', true) as $attachment)
                    <div class="flex items-center p-3 bg-white rounded-lg border border-gray-200 hover:bg-gray-50 transition-colors duration-200">
                        <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <a href="{{ $attachment['url'] ?? '#' }}" class="text-blue-600 hover:text-blue-800 font-medium flex-1">
                            {{ $attachment['name'] ?? '첨부파일.pdf' }}
                        </a>
                        <span class="text-sm text-gray-500 ml-2">
                            {{ $attachment['size'] ?? '1.2MB' }}
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
        <!-- Back Button -->
        <div class="mt-6">
            <button onclick="history.back()" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                뒤로가기
            </button>
        </div>
    </main>
</div>

<script>
function shareNotice() {
    if (navigator.share) {
        navigator.share({
            title: '{{ $notice->title ?? "공지사항" }}',
            text: '{{ Str::limit($notice->content ?? "공지사항 내용", 100) }}',
            url: window.location.href
        });
    } else {
        // Fallback: copy to clipboard
        navigator.clipboard.writeText(window.location.href).then(() => {
            alert('링크가 클립보드에 복사되었습니다.');
        });
    }
}
</script>
