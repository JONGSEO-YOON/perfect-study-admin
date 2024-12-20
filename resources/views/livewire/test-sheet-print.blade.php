<div class="h-screen w-screen">
    <div class="no-print" style="position: fixed; top: 20px; right: 20px; z-index: 1000;">
        <button
            onclick="
            const previewWindow = document.getElementById('preview').contentWindow;
            previewWindow.print();"
            style="font-size: 16px !important; padding: 10px 20px; background: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer;">
            인쇄하기
        </button>
    </div>

    <iframe onload="onPreviewLoaded()" id="preview" class="w-full h-full" src="/preview-test-sheet?scale=1&readonly=true">
    </iframe>

    <style>
        @media print {
            body {
                margin: 0;
                padding: 0;
            }

            iframe {
                height: 100vh !important;
                width: 100vw !important;
            }
        }
    </style>

</div>

@script
    <script>
        window.questions = @json($testSheet->questions);
        window.printLayout = @json($testSheet->print_layout);

        window.onPreviewLoaded = async () => {
            const preview = document.getElementById('preview').contentWindow;

            // 1. 먼저 레이아웃 복원
            if (window.printLayout) {
                preview.postMessage({
                    type: 'restorePrintLayout',
                    data: window.printLayout
                }, '*');
            }

            // 2. 문제 데이터 설정
            preview.postMessage({
                type: 'setQuestions',
                questions: window.questions
            }, '*');
        }

        // 미리보기가 준비되면 자동으로 프린트 다이얼로그 표시
        window.addEventListener('message', (event) => {
            if (event.data.type === 'previewReady') {
                window.print();
            }
        });

        // 프린트 설정
        window.addEventListener('beforeprint', () => {
            document.body.style.margin = '0';
            document.body.style.padding = '0';
        });
    </script>
@endscript
