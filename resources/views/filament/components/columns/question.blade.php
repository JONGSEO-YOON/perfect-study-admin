<div>
    @if ($getRecord()->question_display_type == 'content')
        <div>{!! $getRecord()->content !!}</div>
    @else
        <img src="/storage/{{ $getRecord()->image_path }}" alt='문제 이미지' style='max-width: 300px; max-height: 300px;'>
    @endif
</div>
