<x-layouts.simple>
    @livewire('exam-print-layout', [
        'scale' => $scale ?? null,
    ])
    <style>
        @font-face {
            font-family: 'MT';
            /* 폰트에 사용할 이름 지정 */
            src: url('fonts/mt.ttf') format('truetype');
            /* ttf 파일 경로 지정 */
            font-weight: normal;
            font-style: normal;
        }

        @page {
            size: A4;
            margin: 0;
        }

        body {
            font-family: 'MT', sans-serif;
            margin: 0;
            padding: 0;
            background: #f0f0f0;
        }

        .page {
            width: 210mm;
            min-height: 297mm;
            /* padding: 20mm; */
            margin: 10mm auto;

            padding-top: 20mm;
            padding-bottom: 15mm;
            padding-left: 20mm;
            padding-right: 20mm;

            background: white;
            box-sizing: border-box;
            position: relative;
            page-break-after: always;
        }

        .sub_page {
            padding-top: 15mm;
        }

        /* 마지막 페이지는 강제 페이지 나눔을 하지 않음 */
        .page:last-child {
            page-break-after: avoid;
        }

        .page-number {
            position: absolute;
            bottom: 10mm;
            right: 10mm;
            font-size: 10pt;
        }

        .exam-header {
            text-align: center;
            margin-bottom: 10mm;
        }

        .exam-title {
            font-size: 16pt;
            font-weight: bold;
            margin-bottom: 5mm;
        }

        .student-info {
            border-top: 1pt solid #000;
            border-bottom: 1pt solid #000;
            padding: 5mm 0;
            margin-bottom: 10mm;
        }

        .question {
            margin-bottom: 8mm;
            page-break-inside: avoid;
        }

        .question-number {
            font-weight: bold;
            margin-right: 2mm;
        }

        .print-bg {
            -webkit-print-color-adjust: exact;
        }

        .diag {
            background: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' version='1.1' preserveAspectRatio='none' viewBox='0 0 100 100'>><path d='M0 99 L99 0 L100 1 L1 100' fill='black' /></svg>");
            background-repeat: no-repeat;
            -webkit-print-color-adjust: exact;

            background-position: center center;
            background-size: 100% 100%, auto;
        }

        @media print {
            body {
                background: none;
            }

            .page {
                margin: 0;
                box-shadow: none;
            }

            .no-print {
                display: none;
            }
        }

        @media screen {
            .page {
                box-shadow: 0 0 10mm rgba(0, 0, 0, 0.1);
            }
        }
    </style>
    @if ($scale ?? false)
        <style>
            body {
                scale: {{ $scale }};
                transform-origin: top
            }
        </style>
    @endif

</x-layouts.simple>
