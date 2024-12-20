<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>성적표 출력</title>
    <!-- Scripts -->
    @stack('scripts')

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <style>
        @page {
            size: A4;
            margin: 0;
        }

        * {
            font-size: 9pt !important;
        }

        .sm * {
            font-size: 7pt !important;
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
            margin: 10mm auto;
            padding: 5mm 5mm 5mm;
            background: white;
            box-sizing: border-box;
            position: relative;
            page-break-after: always;
        }

        .page:last-child {
            page-break-after: avoid;
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
</head>

<body>


    {{ $slot }}

    @livewireScripts
</body>

</html>
