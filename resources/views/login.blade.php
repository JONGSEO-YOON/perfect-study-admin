<x-layouts.simple>
    <div class="h-full w-full flex items-center justify-center overflow-auto"
        style="background: linear-gradient(143.53deg, #F5F5F5 4.9%, #D0D4FF 52.45%, #FFFFFF 101.93%);">
        <div class="w-full max-w-2xl flex items-center justify-center py-16  [@media(min-height:900px)]:rounded-[33px] "
            style="background: #FFFFFF; box-shadow: 0px 0px 12px 8px rgba(229, 225, 240, 0.71);">
            <div class="w-[80%] md:w-2/3 flex flex-col">
                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <img class="w-2/3 md:w-[85%] mx-auto translate-x-[4%]" src="/images/login-main.png" />
                    <img class="w-[65%] mx-auto mt-4" src="/logo.png" />

                    @if ($errors->any())
                        <div class="mt-4 p-4 text-red-500 bg-red-50 rounded">
                            @foreach ($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    <div class="mt-8 gap-y-2 flex flex-col">
                        <label class="font-semibold" for="username">아이디</label>
                        <input name="username" id="username" type="text"
                            class="w-full border-0 py-3 pl-6 placeholder:text-[#9A96A3] @error('username') border-red-500 @enderror"
                            style="background: #F8F5FF;border-radius: 5px;" placeholder="아이디를 입력해 주세요."
                            value="{{ old('username') }}" />
                    </div>

                    <div class="mt-6 gap-y-2 flex flex-col">
                        <label class="font-semibold" for="password">비밀번호</label>
                        <input name="password" id="password" type="password"
                            class="w-full border-0 py-3 pl-6 placeholder:text-[#9A96A3] @error('password') border-red-500 @enderror"
                            style="background: #F8F5FF;border-radius: 5px;" placeholder="비밀번호를 입력해 주세요." />
                    </div>

                    <button type="submit" class="w-full flex text-white items-center justify-center mt-10 py-4"
                        style="background: #6D4FC5;border-radius: 5px;">
                        로그인
                    </button>

                    <div class="mt-4 flex justify-between font-medium">
                        <a href="#" class="text-[#6D4FC5] signup">회원가입</a>
                        <a href="#" onclick="install()" class="text-[#6D4FC5] install">홈 화면에 설치</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <x-signup-modal />
    <script>
        window.addEventListener('beforeinstallprompt', async (e) => {
            // Chrome 76 이전 버전에서는 자동 표시되는 설치 프롬프트를 방지
            e.preventDefault();
            // 나중에 사용하기 위해 이벤트를 저장
            deferredPrompt = e;

        });

        const isIos = () => {
            const userAgent = window.navigator.userAgent.toLowerCase();
            return /iphone|ipad|ipod/.test(userAgent);
        };

        const isInStandaloneMode = () => ('standalone' in window.navigator) && (window.navigator.standalone);

        function install() {
            // iOS 디바이스에서 Safari로 접속한 경우 설치 안내 메시지 표시
            if (isIos() && !isInStandaloneMode()) {
                alert('Safari에서 하단 공유 버튼을 클릭한 후, [홈 화면에 추가] 버튼을 선택하세요')
                return;
            }
            deferredPrompt.prompt();
            deferredPrompt.userChoice.then((choiceResult) => {
                if (choiceResult.outcome === 'accepted') {
                    console.log('사용자가 설치를 수락했습니다.');
                } else {
                    console.log('사용자가 설치를 거부했습니다.');
                }
                deferredPrompt = null;
            });
        }

        window.addEventListener('DOMContentLoaded', function() {
            if (isInStandaloneMode()) {
                document.querySelector('.install').style.display = 'none';
            }
        });
    </script>
</x-layouts.simple>
