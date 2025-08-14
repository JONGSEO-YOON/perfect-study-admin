<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <livewire:parent.header />
    <livewire:parent.navigation />

    <!-- Main Content Area -->
    <main class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-gradient-to-br from-violet-400 to-fuchsia-600 p-4 rounded-lg shadow-sm">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="start_date" class="block text-sm font-medium text-white">시작 날짜</label>
                    <input type="date" name="start_date" id="start_date"
                        class="mt-1 block w-full rounded-md border-transparent bg-white/20 text-white shadow-sm focus:border-violet-300 focus:ring-violet-300 sm:text-sm" style="color-scheme: dark;">
                </div>
                <div>
                    <label for="end_date" class="block text-sm font-medium text-white">종료 날짜</label>
                    <input type="date" name="end_date" id="end_date"
                        class="mt-1 block w-full rounded-md border-transparent bg-white/20 text-white shadow-sm focus:border-violet-300 focus:ring-violet-300 sm:text-sm" style="color-scheme: dark;">
                </div>
            </div>
        </div>
    </main>
</div>
