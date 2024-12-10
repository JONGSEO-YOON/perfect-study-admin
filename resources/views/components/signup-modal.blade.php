{{-- resources/views/components/signup-modal.blade.php --}}
<div id="signupModal" class="fixed inset-0 z-50 overflow-y-auto hidden">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>
        <div
            class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-lg w-screen">
            <form id="signupForm" class="px-4 pt-5 pb-4 bg-white sm:p-6 sm:pb-4">
                @csrf
                <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">회원가입</h3>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">이름</label>
                    <input type="text" name="name"
                        class="mt-1 block w-full rounded-md border-[#6D4FC5]/30 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        style="background: #F8F5FF;" required>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">생년월일 (6자리)</label>
                    <input type="text" name="birthday" maxlength="6"
                        class="mt-1 block w-full rounded-md border-[#6D4FC5]/30 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        style="background: #F8F5FF;" required pattern="\d{6}">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">사용할 아이디</label>
                    <input type="text" name="username"
                        class="mt-1 block w-full rounded-md border-[#6D4FC5]/30 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        style="background: #F8F5FF;" required>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">비밀번호</label>
                    <input type="password" name="password"
                        class="mt-1 block w-full rounded-md border-[#6D4FC5]/30 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        style="background: #F8F5FF;" required>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">비밀번호 확인</label>
                    <input type="password" name="password_confirmation"
                        class="mt-1 block w-full rounded-md border-[#6D4FC5]/30 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        style="background: #F8F5FF;" required>
                </div>

                <div id="errorMessage" class="mb-4 text-red-600 text-sm hidden"></div>

                <div class="mt-5 sm:mt-6">
                    <button type="submit"
                        class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white bg-[#6D4FC5] border border-transparent rounded-md shadow-sm hover:bg-[#5d43a6] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#6D4FC5] sm:text-sm">
                        회원가입
                    </button>
                    <button type="button" onclick="closeModal()"
                        class="mt-3 inline-flex justify-center w-full px-4 py-2 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:text-sm">
                        취소
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // 모달 열기
        const signupLink = document.querySelector('.signup');
        const modal = document.getElementById('signupModal');
        const form = document.getElementById('signupForm');
        const errorDiv = document.getElementById('errorMessage');

        signupLink.addEventListener('click', function(e) {
            e.preventDefault();
            modal.classList.remove('hidden');
        });

        // 모달 닫기
        window.closeModal = function() {
            modal.classList.add('hidden');
            form.reset();
            errorDiv.classList.add('hidden');
            errorDiv.textContent = '';
        }

        // 폼 제출
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(form);

            if (formData.get('password') !== formData.get('password_confirmation')) {
                errorDiv.textContent = '비밀번호가 일치하지 않습니다.';
                errorDiv.classList.remove('hidden');
                return;
            }

            try {
                const response = await fetch('/api/signup', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                            .content
                    },
                    body: JSON.stringify(Object.fromEntries(formData))
                });

                const data = await response.json();

                if (!response.ok) {
                    errorDiv.textContent = data.message;
                    errorDiv.classList.remove('hidden');
                    return;
                }

                alert('회원가입 신청이 완료되었습니다. 관리자 승인 후 이용하실 수 있습니다.');
                closeModal();
            } catch (error) {
                errorDiv.textContent = '오류가 발생했습니다. 다시 시도해주세요.';
                errorDiv.classList.remove('hidden');
            }
        });
    });
</script>
