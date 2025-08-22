<header class="bg-white shadow-sm">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <!-- Logo and Brand -->
            <div class="flex items-center ">
                <a href="{{ route('parent.home') }}" wire:navigate>
                    <img src="{{ asset('logo.png') }}" alt="퍼펙트 스터디" class="h-10">
                </a>
            </div>
            <!-- User Menu -->
            <div class="flex items-center">

                <!-- Children Select -->
                <div class="flex items-center">
                    <select wire:model="studentId" wire:change="changeStudent" class="block py-2 border-0 focus:outline-none focus:ring-violet-500 focus:border-violet-500 font-sd text-sm text-violet-600">
                        @foreach ($students as $student)
                            <option value="{{ $student->id }}" wire:key="{{$student->id}}">{{ $student->user->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

        </div>
    </div>
</header>