<x-layouts.simple>
    <div class="h-full w-full flex flex-col">
        <div class="border-b py-4 px-5 flex flex-col">
            <div class="flex flex-row items-center w-full max-w-[740px] mx-auto">
                <a href="#" class="p-2 bg-[#F4F4F4] rounded">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M16.5 4.5H20.25V21.75H3.75V4.5H7.5V6H16.5V4.5ZM6.75 12H17.25V10.5H6.75V12ZM6.75 18H17.25V16.5H6.75V18ZM9 4.5V2.25H15V4.5H9Z"
                            fill="#3E3E3E" />
                    </svg>
                </a>
                <div class="flex flex-col md:flex-row ml-6 md:items-center">
                    <h1 class="font-bold">
                        09월 13일 (수학(상))
                    </h1>
                    <h2 class="text-[#7D7D92] md:ml-4 text-sm">
                        14문제 | 수열의 귀납적 정의
                    </h2>
                </div>
                <button class="bg-[#F4F4F4] rounded-[5px] py-2 px-6 font-semibold  hidden md:flex ml-auto">
                    메인화면
                </button>
            </div>
            <button class="bg-[#F4F4F4] rounded-[5px] py-2 text-sm font-semibold md:hidden mt-2.5">
                메인화면
            </button>
        </div>
        <div class="flex flex-col w-full max-w-[740px] mx-auto  pb-10 ">
            <div class="text-center pt-4  mt-4 text-xl font-bold text-gray-700">
                문제지 체점 결과
            </div>
            <div class="flex flex-col items-center p-8 bg-white rounded-lg">
                <div class="relative w-64">
                    <svg viewBox="-110 -110 220 130" class="w-full">
                        <path d="M -100 0 A 100 100 0 0 1 100 0" fill="none" stroke="#e5e5e5" stroke-width="20" />
                        <path d="M -100 0 A 100 100 0 0 1 10 -99.5" fill="none" stroke="#8570C2" stroke-width="20"
                            stroke-linecap="round" />
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center mt-10 gap-y-2">
                        <span class="text-4xl font-bold text-gray-700">11 / 20</span>
                        <span class="text-lg text-gray-600">55%</span>
                    </div>
                </div>
            </div>

            <div class="flex flex-col w-full max-w-[500px] mx-auto">
                <div
                    class="font-semibold py-2 md:py-4 mt-6 text-lg border-b px-4 md:px-0 flex  flex-row justify-between">
                    문제 해설
                    <div class="flex items-center gap-2">
                        <label class="text-sm font-medium text-gray-700">오답 문제만 보기</label>
                        <div class="relative inline-flex h-6 w-11 items-center rounded-full bg-gray-200">
                            <span
                                class="inline-block h-4 w-4 transform rounded-full bg-white transition-all duration-200 translate-x-1"></span>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-5 gap-4 mt-4 px-5 md:px-0 md:grid-cols-7">
                    @for ($i = 0; $i < 30; $i++)
                        @if ($i % 7 == 0)
                            <div
                                class="rounded-full w-[54px] h-[54px] cursor-pointer hover:brightness-50 transition-all font-medium border flex items-center justify-center
                   text-white 
                    !border-[#FF4F57] 
                !bg-[#FF4F57]/70
                    ">
                                {{ $i + 1 }}
                            </div>
                        @else
                            <div
                                class="rounded-full w-[54px] h-[54px] cursor-pointer hover:brightness-50 transition-all font-medium border flex items-center justify-center
                           text-white 
                            !border-[#6156EF] 
                        !bg-[#6156EF]/70
                            ">
                                {{ $i + 1 }}
                            </div>
                        @endif
                    @endfor
                </div>
            </div>

        </div>
    </div>

</x-layouts.simple>
