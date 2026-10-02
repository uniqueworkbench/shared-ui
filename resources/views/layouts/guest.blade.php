<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Unique Workbench') }}</title>
        <link rel="icon" type="image/x-icon" href="{{ config('app.favicon') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />        

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-zinc-900 antialiased bg-zinc-100">
        <main class="pb-12">
            <div class="min-h-screen flex flex-col sm:justify-center items-center pt-10 sm:pt-0 px-4">
                <a href="/">
                    <img src="{{ \UniqueWorkbench\SharedUi\Brand::logo('light') }}" alt="Unique Workbench" class="h-20 w-auto">
                </a>

                <div class="w-full sm:max-w-md mt-8 px-6 py-6 bg-white border border-zinc-200 rounded-md">
                    {{ $slot }}
                </div>
            </div>
        </main>
        <x-application-version/>
        <x-page-loading/>
    </body>
</html>
