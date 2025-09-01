<!-- Navigation -->
<nav class="bg-white border-b border-stone-100 shadow-sm">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-around ">
            <a href="{{ route('parent.home') }}"  class="py-2 px-4 font-medium text-sm" wire:navigate wire:current="border-b-2 border-violet-500 text-violet-600">
                소식
            </a>
            <a href="{{ route('parent.attendance') }}"  class="py-2 px-4 font-medium text-sm" wire:navigate wire:current="border-b-2 border-violet-500 text-violet-600">
                출결현황
            </a>
            <a href="{{ route('parent.report') }}"  class="py-2 px-4 font-medium text-sm" wire:navigate wire:current="border-b-2 border-violet-500 text-violet-600">
                성적표
            </a>
             <a href="{{ route('parent.payment') }}"  class="py-2 px-4 font-medium text-sm" wire:navigate wire:current="border-b-2 border-violet-500 text-violet-600">
                결제
            </a>
        </div>
    </div>
</nav>