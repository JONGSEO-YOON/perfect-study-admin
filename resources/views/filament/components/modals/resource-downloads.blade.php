<div class="p-4 space-y-3">
    <div>
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">{{ $record->title }}</h3>
        @if($record->author)
            <p class="text-xs text-gray-500">작성자: {{ $record->author->name }} · {{ $record->created_at->format('Y-m-d') }}</p>
        @endif
    </div>

    @if($record->content)
        <div class="text-sm text-gray-700 dark:text-gray-300 prose prose-sm max-w-none border-t border-b border-gray-200 dark:border-gray-700 py-3">
            {!! $record->content !!}
        </div>
    @endif

    @php
        $attachments = is_array($record->attachments) ? $record->attachments : (json_decode($record->attachments, true) ?: []);
    @endphp

    @if(empty($attachments))
        <div class="text-center py-6 text-gray-500">첨부 파일이 없습니다.</div>
    @else
        <div class="space-y-2">
            <div class="text-xs font-medium text-gray-500 dark:text-gray-400">첨부 파일 ({{ count($attachments) }}개)</div>
            @foreach($attachments as $attachment)
                @php
                    $url = \Illuminate\Support\Facades\Storage::url($attachment);
                    $filename = basename($attachment);
                @endphp
                <a href="{{ $url }}" target="_blank" download
                   class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-800 hover:bg-blue-50 dark:hover:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-700 transition">
                    <div class="flex-shrink-0 w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">{{ $filename }}</div>
                        <div class="text-xs text-gray-500">클릭하여 다운로드</div>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</div>
