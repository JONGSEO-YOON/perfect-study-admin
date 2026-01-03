<div class="h-[60vh] w-full">
    <div class="flex justify-end px-0 lg:px-10">
        <button
            type="button"
            class="flex items-center gap-1 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm text-stone-800 transition-colors duration-200 hover:bg-gray-50"
            x-data="{ copied: false }"
            @click="
                copied = true;
                navigator.clipboard.writeText('{{ $shareLink }}');
                setTimeout(() => { copied = false; }, 3000);
            "
        >
            <svg
                xmlns="http://www.w3.org/2000/svg"
                class="h-5 w-5"
                viewBox="0 0 20 20"
                fill="currentColor"
            >
                <path d="M8 3a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1z" />
                <path
                    d="M6 3a2 2 0 00-2 2v11a2 2 0 002 2h8a2 2 0 002-2V5a2 2 0 00-2-2 3 3 0 01-3 3H9a3 3 0 01-3-3z"
                />
            </svg>
            <span x-text="copied ? '복사완료!' : '링크복사'"></span>
        </button>
    </div>
    <iframe
        class="h-full w-full"
        src="{{ $shareLink }}"
        frameborder="0"
    ></iframe>
</div>
