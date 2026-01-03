@script
    <script>
        let callback = null;

        // window.addEventListener('videoSelected', (event) => {
        //     Livewire?.dispatch('videoSelected', {
        //         video: event.data
        //     });
        // });
    </script>
@endscript

<div class="flex-1 overflow-auto h-0">
    <div class="md:max-w-[740px] lg:max-w-[1000px] mx-auto w-full px-5 py-4">
        <a class="flex text-sm flex-row items-center gap-x-1 md:mt-6" href="/lectures">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5">
                <path fill-rule="evenodd"
                    d="M11.78 5.22a.75.75 0 0 1 0 1.06L8.06 10l3.72 3.72a.75.75 0 1 1-1.06 1.06l-4.25-4.25a.75.75 0 0 1 0-1.06l4.25-4.25a.75.75 0 0 1 1.06 0Z"
                    clip-rule="evenodd" />
            </svg>
            목록으로
        </a>
        <h1 class="w-full text-2xl font-bold pb-2  mt-4 md:mt-6 flex flex-row items-center gap-x-4">
            <svg width="24" height="24" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd"
                    d="M18.0031 12.8229H10.3844C11.2489 12.7486 12.0504 12.356 12.6226 11.7267C13.1947 11.0973 13.4934 10.2796 13.4566 9.4434C13.4198 8.6072 13.0503 7.81682 12.4249 7.23649C11.7996 6.65615 10.9664 6.33048 10.0985 6.32714H7.60981C7.98188 5.84467 8.248 5.29409 8.3919 4.70906C8.53579 4.12403 8.55444 3.51684 8.44671 2.92462C8.33897 2.3324 8.10711 1.76759 7.76531 1.26473C7.4235 0.761867 6.97893 0.33152 6.45878 0L18.0031 0C18.533 0.000757029 19.0409 0.204279 19.4153 0.565875C19.7897 0.92747 20 1.41758 20 1.92857V10.8986C20 11.9614 19.1053 12.8243 18.0031 12.8243V12.8229ZM3.92415 6.33C4.28892 6.33818 4.6517 6.27597 4.99117 6.14701C5.33065 6.01805 5.63996 5.82495 5.90094 5.57906C6.16192 5.33317 6.3693 5.03945 6.51089 4.71517C6.65247 4.39088 6.72541 4.04257 6.72541 3.69071C6.72541 3.33886 6.65247 2.99055 6.51089 2.66626C6.3693 2.34198 6.16192 2.04826 5.90094 1.80237C5.63996 1.55648 5.33065 1.36338 4.99117 1.23442C4.6517 1.10546 4.28892 1.04324 3.92415 1.05143C3.20932 1.06747 2.52937 1.35258 2.02969 1.84579C1.53001 2.33901 1.25026 3.00118 1.25026 3.69071C1.25026 4.38025 1.53001 5.04242 2.02969 5.53564C2.52937 6.02885 3.20932 6.31396 3.92415 6.33ZM11.6199 9.58143C11.6199 8.77 10.9384 8.11286 10.0985 8.11286H3.92563C2.88449 8.11286 1.88599 8.51171 1.14979 9.22167C0.413592 9.93163 0 10.8945 0 11.8986V13.62C0 14.38 0.639953 14.9957 1.42804 14.9957H1.68284L2.10059 18.7243C2.13965 19.0744 2.31141 19.3982 2.5829 19.6336C2.85438 19.8691 3.20647 19.9996 3.57159 20H4.31524C4.6726 19.9999 5.01785 19.8751 5.28737 19.6488C5.55688 19.4225 5.73248 19.1099 5.78179 18.7686L6.89727 11.0486H10.097C10.937 11.0486 11.6184 10.3914 11.6184 9.58143H11.6199Z"
                    fill="#3D3D3D" />
            </svg>
            {{ $lecture->title }}
        </h1>
        <div class="pb-2 gap-x-2 flex flex-row">
            @if ($lecture->target_grades)
                @foreach ($lecture->target_grades as $grade)
                    @php
                        $gradeName = App\Models\GradeSystem::find($grade)?->display_name;
                    @endphp
                    <span class="px-3 py-1 text-[#7256C2] bg-[#7256C2]/10 text-xs rounded-full font-medium">
                        {{ $gradeName }}
                    </span>
                @endforeach
            @endif
            @php
                $scopes = $lecture->scopes ?? [];
                $scopeCount = count($scopes);
                $displayScopes = array_slice($scopes, 0, 2);
            @endphp

            @foreach ($displayScopes as $scope)
                <span class="px-3 py-1 bg-gray-100 text-gray-700 text-xs rounded-full">
                    {{ $scope }}
                </span>
            @endforeach
        </div>
        <div class="pb-4 border-b gap-x-2 flex flex-row mt-2">
            <div class="text-sm">
                {!! $lecture->description !!}
            </div>
        </div>
        <div class="w-full flex flex-col mt-6">
            @livewire(\App\Livewire\StudentLectureVideoList::class, [
                'id' => $lecture->id,
                'histories' => $histories,
            ])
        </div>
    </div>
</div>
