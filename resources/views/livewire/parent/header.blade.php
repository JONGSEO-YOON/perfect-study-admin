<header class="bg-white shadow-sm">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <!-- Logo and Brand -->
            <div class="flex items-center ">
                <a href="{{ route('parent.home') }}" wire:navigate>
                    <img src="{{ isset($currentAcademy) && $currentAcademy->logo_path ? \Illuminate\Support\Facades\Storage::url($currentAcademy->logo_path) : asset('logo.png') }}" alt="{{ $currentAcademy->name ?? '학원' }}" class="h-10">
                </a>
            </div>
            <!-- User Menu -->
            <div class="flex items-center gap-2">
                @if (count($academies) > 1)
                    <!-- Academy Select -->
                    <select wire:model="academyId" wire:change="changeAcademy" class="block py-2 px-3 border border-gray-200 rounded-lg focus:outline-none focus:ring-violet-500 focus:border-violet-500 text-xs text-gray-600 bg-gray-50">
                        @foreach ($academies as $academy)
                            <option value="{{ $academy['id'] }}">{{ $academy['name'] }}</option>
                        @endforeach
                    </select>
                @endif

                <!-- Children Select -->
                <div class="flex items-center">
                    <select wire:model="studentId" wire:change="changeStudent" class="block py-2 border-0 focus:outline-none focus:ring-violet-500 focus:border-violet-500 font-sd text-sm text-violet-600">
                        @foreach ($students as $student)
                            <option value="{{ $student['id'] }}" wire:key="{{ $student['id'] }}">{{ $student['user']['name'] ?? '' }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

        </div>
    </div>
</header>
