<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name') }}</title>

    {{-- Styles --}}
    @wireUiStyles
    @vite(['resources/css/app.css'])
    @livewireStyles

    {{-- Scripts --}}
    @livewireScripts
    @wireUiScripts
    @vite(['resources/js/app.js'])
    @include('popper::assets')

    {{-- FontAwesome Fallback --}}
    <script src="https://kit.fontawesome.com/e2a87f9a11.js" crossorigin="anonymous"></script>

    {{-- App Theme --}}
    <script>
        if (
            localStorage.theme === "dark" ||
            (!("theme" in localStorage) &&
                window.matchMedia("(prefers-color-scheme: dark)").matches)
        ) {
            document.documentElement.classList.add("dark");
        } else {
            document.documentElement.classList.remove("dark");
        }
    </script>
</head>

<body @class([
    'h-screen w-screen',
    'flex flex-col',
    'font-sans antialiased',
    'bg-gray-50 dark:bg-neutral-800',
    'text-slate-600 dark:text-white',
    'overflow-hidden',
])>
    {{ $slot }}
</body>

</html>
