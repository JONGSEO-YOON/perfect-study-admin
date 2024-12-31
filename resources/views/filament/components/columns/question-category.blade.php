<div class="text-sm font-medium  flex flex-row items-center gap-x-3 pl-2">
    @if ($getRecord()->type === 'scope')
        <img src="/images/scope.svg" alt="범위" class="w-5 h-5">
    @else
        <img src="/images/question-type.svg" alt="범위" class="w-5 h-5">
    @endif
    {!! $getRecord()->name !!}
</div>
