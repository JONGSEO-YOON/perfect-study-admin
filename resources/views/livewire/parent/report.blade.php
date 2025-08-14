<div class="min-h-screen bg-gray-50">

  <!-- Main Content Area -->
    <main class="max-w-6xl mx-auto px-3 sm:px-6 lg:px-8 py-4 sm:py-8">
        <div class="bg-gradient-to-br from-violet-400 to-fuchsia-600 p-3 sm:p-4 rounded-lg shadow-sm mb-4">
            <h2 class="text-lg sm:text-xl font-bold text-white mb-3">성적표 조회</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4">
                <div>
                    <label for="start_date" class="block text-sm font-medium text-white">시작 날짜</label>
                    <input type="date" name="start_date" id="start_date"
                        class="mt-1 block w-full rounded-md border-transparent bg-white/20 text-white placeholder-white/70 shadow-sm focus:border-violet-300 focus:ring-violet-300 sm:text-sm text-sm" style="color-scheme: dark;">
                </div>
                <div>
                    <label for="end_date" class="block text-sm font-medium text-white">종료 날짜</label>
                    <input type="date" name="end_date" id="end_date"
                        class="mt-1 block w-full rounded-md border-transparent bg-white/20 text-white placeholder-white/70 shadow-sm focus:border-violet-300 focus:ring-violet-300 sm:text-sm text-sm" style="color-scheme: dark;">
                </div>
            </div>
        </div>
        <div class="bg-fuchsia-50 rounded-lg shadow-sm p-3 sm:p-4 mb-4">
            <div class="flex flex-col items-center justify-center py-8 sm:py-12">
                
                    
                <!-- Text with gradient -->
                <div class="mt-4 sm:mt-6 text-center">
                    <h4 class="text-lg sm:text-xl font-bold bg-gradient-to-r from-fuchsia-600 to-violet-600 bg-clip-text text-transparent">
                        성적표 준비중...
                    </h4>
                </div>
                
                
            </div>
        </div>
    </main>

</div>
