<div class="flex-1 overflow-auto h-0">
    <div class="max-w-[740px] mx-auto w-full px-5 py-4">
        <h1 class="w-full text-2xl font-bold pb-4 border-b md:mt-6 flex flex-row items-center gap-x-4">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5">
                <path fill-rule="evenodd"
                    d="M10 1a4.5 4.5 0 0 0-4.5 4.5V9H5a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2h-.5V5.5A4.5 4.5 0 0 0 10 1Zm3 8V5.5a3 3 0 1 0-6 0V9h6Z"
                    clip-rule="evenodd" />
            </svg>
            비밀번호 변경
        </h1>

        @if (session()->has('message'))
            <div class="password-change-container mt-4 text-green-600">
                {{ session('message') }}
            </div>
        @endif

        <div class="password-change-container">
            <form wire:submit="changePassword" class="password-form">
                <div class="form-group">
                    <label for="current-password">현재 비밀번호</label>
                    <input type="password" wire:model="current_password" id="current-password" name="current-password">
                    @error('current_password')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="new-password">새 비밀번호</label>
                    <input type="password" wire:model="new_password" id="new-password" name="new-password">
                    @error('new_password')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="confirm-password">새 비밀번호 확인</label>
                    <input type="password" wire:model="new_password_confirmation" id="confirm-password"
                        name="confirm-password">
                    @error('new_password_confirmation')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
                <button type="submit" class="mt-10">비밀번호 변경</button>
            </form>
        </div>
    </div>
    <style>
        .password-change-container {
            max-width: 400px;
            margin: 16px auto;
            padding: 20px;
            background-color: #ffffff;
        }

        .password-form {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .password-form h2 {
            margin: 0 0 16px;
            text-align: center;
            color: #333;
            font-size: 24px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .password-change-container label {
            font-size: 16px;
            color: #555 !important;
            font-weight: 500;
        }

        .password-change-container input {
            padding: 12px;
            border: 1px solid #ddd !important;
            border-radius: 8px !important;
            font-size: 16px;
            transition: border-color 0.3s ease;

        }

        .password-change-container input:focus {
            outline: none;
            border-color: #8570C2;
            box-shadow: 0 0 0 2px rgba(74, 144, 226, 0.1);
        }

        .password-change-container .error-message {
            font-size: 14px;
            color: #e74c3c;
            min-height: 16px;
        }

        .password-change-container button {
            padding: 12px;
            background-color: #8570C2;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .password-change-container button:hover {
            background-color: #8570C2;
        }

        .password-change-container button:active {
            background-color: #8570C2;
        }
    </style>
</div>
