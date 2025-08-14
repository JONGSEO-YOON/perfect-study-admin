
 <div class="h-full w-full flex items-center justify-center overflow-auto"
    style="background: linear-gradient(143.53deg, #F5F5F5 4.9%, #D0D4FF 52.45%, #FFFFFF 101.93%);">
    <div class="w-full max-w-lg flex items-center justify-center h-full bg-white lg:rounded-xl lg:h-auto lg:shadow-lg">
        <div class="h-full flex flex-col">
            <form class="flex flex-col justify-between h-full" wire:submit="login">
                <div class="px-6 py-16">
                    <img class="w-2/3 md:w-[85%] mx-auto translate-x-[4%]" src="/images/login-main.png" />
                    <img class="w-[65%] mx-auto mt-4" src="/logo.png" />
                    <div class="text-2xl w-[65%] mx-auto  font-bold text-right">학부모</div>
                    @if ($errors->any())
                        <div class="mt-4 p-4 text-red-500 bg-red-50 rounded">
                            @foreach ($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="mt-4 p-4 text-red-500 bg-red-50 rounded">
                           <p> {{ session('error') }}</p>
                        </div>
                    @endif

                    <div class="mt-8 flex flex-col">
                        <label class="font-semibold" for="username">전화번호</label>
                        <input id="phone" type="tel"
                            wire:model="phone"
                            class="w-full border-0 py-3 pl-6 placeholder:text-[#9A96A3]"
                            style="background: #F8F5FF;border-radius: 5px;" placeholder="핸드폰 번호를 입력해 주세요."
                            value="{{ old('phone') }}" />
                    </div>

                    <div class="mt-6  flex flex-col">
                        <label class="font-semibold" for="password">비밀번호</label>
                        <input name="password" id="password" type="password"
                            wire:model="password"
                            class="w-full border-0 py-3 pl-6 placeholder:text-[#9A96A3]"
                            style="background: #F8F5FF;border-radius: 5px;" placeholder="비밀번호를 입력해 주세요." />
                    </div>


                    <div class="mt-4 flex justify-between font-medium">
                        <a href="#" wire:click="$js.install" class="text-[#6D4FC5] install">홈 화면에 설치</a>
                    </div>

                </div>

                <button type="submit" class="w-full flex text-white items-center justify-center py-5 bg-violet-500 lg:rounded-b-xl">
                    로그인
                </button>
            </form>
        </div>
    </div>
</div>
@script
<script>
    let deferredPrompt;
    window.addEventListener('beforeinstallprompt', async (e) => {
        // Chrome 76 이전 버전에서는 자동 표시되는 설치 프롬프트를 방지
        e.preventDefault();
        // 나중에 사용하기 위해 이벤트를 저장
        deferredPrompt = e;

    });



     $js('install', () => {
       // iOS Safari의 경우
        if (isIos() && !isInStandaloneMode()) {
            alert('Safari에서 하단 공유 버튼을 클릭한 후, [홈 화면에 추가] 버튼을 선택하세요');
            return;
        }

        // PWA 설치 프롬프트
        if (deferredPrompt) {
            deferredPrompt.prompt();
            deferredPrompt.userChoice.then((choiceResult) => {
                if (choiceResult.outcome === 'accepted') {
                    console.log('사용자가 설치를 수락했습니다.');
                } else {
                    console.log('사용자가 설치를 거부했습니다.');
                }
                deferredPrompt = null;
            });
        } else {
            // 이벤트가 준비되지 않은 경우, 사용자에게 수동 설치를 안내
            alert('홈 화면 설치를 위해 브라우저 메뉴를 확인해 주세요.');
        }
    })

       const isIos = () => {
        const userAgent = window.navigator.userAgent.toLowerCase();
        return /iphone|ipad|ipod/.test(userAgent);
    };

    const isInStandaloneMode = () => ('standalone' in window.navigator) && (window.navigator.standalone);

    // '홈 화면에 설치' 버튼 숨기기
    window.addEventListener('DOMContentLoaded', function() {
        if (isInStandaloneMode()) {
            document.querySelector('.install').style.display = 'none';
        }
    });



    window.addEventListener('DOMContentLoaded', function() {
        if (isInStandaloneMode()) {
            document.querySelector('.install').style.display = 'none';
        }
    });
</script>
@endscript
