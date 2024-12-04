<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>


    {{-- <script src="https://www.wiris.net/demo/plugins/app/WIRISplugins.js?viewer=image"></script> --}}

    <!-- Scripts -->
    @stack('scripts')

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Livewire Styles -->
    @livewireStyles
</head>

<body>
    <!-- Page Content -->
    <main>
        {{ $slot }}
    </main>

    <!-- Livewire Scripts -->
    @livewireScripts

    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>

</html>
