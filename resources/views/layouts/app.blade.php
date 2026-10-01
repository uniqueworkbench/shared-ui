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
    <body class="font-sans antialiased bg-gray-100">
        <div class="min-h-screen bg-gray-100">
            @include('layouts.navigation')

            @stack('banners')

            <!-- Page Breadcrumbs -->
            @isset($breadcrumb)
                <div class="max-w-7xl mx-auto py-2 px-2">
                    {{ $breadcrumb }}
                </div>
            @endisset

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white shadow mt-1">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main class="pb-12">
                {{ $slot }}
            </main>
        </div>
        
        <x-application-version/>
        <x-page-loading/>
        <x-confirm-modal/>
        <x-prompt-modal/>
        @stack('scripts')
    </body>
</html>
