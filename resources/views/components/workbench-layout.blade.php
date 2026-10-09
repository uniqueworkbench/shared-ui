{{--
    Client app layout: <x-workbench-layout> ... </x-workbench-layout>

    Slots: $header (page title), $breadcrumb (optional), $slot (page content).
    Props: title (browser tab title), padded (false = pages bring their own spacing),
    nav (false = no menu: desktop drops the sidebar and drawer, phone keeps the drawer for the user's links only),
    vite (the entries to load; default resources/css/app.css + resources/js/app.js),
    inertia (true when the slot is an Inertia page (@inertia): the tab title is Inertia's to change, and the
    page-loading overlay, confirm/prompt modals and dirty forms — made for full page loads — are left out).
    @push('head') content goes in <head> before the scripts (e.g. @routes, @viteReactRefresh, @inertiaHead).
    @push('banners') content shows under the header (e.g. impersonation notices).
    Navigation style comes from config('shared-ui.layout'): "desktop" (left
    sidebar, a drawer on small screens) or "phone" (app-style header with a
    hamburger drawer). Menu items come from config('shared-ui.navigation').
--}}
@props(['title' => null, 'padded' => true, 'nav' => true, 'vite' => ['resources/css/app.css', 'resources/js/app.js'], 'inertia' => false])
@php
    $layout = config('shared-ui.layout') === 'phone' ? 'phone' : 'desktop';
    $pageTitle = $title ?: config('app.name', 'Unique Workbench');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title @if ($inertia) inertia @endif>{{ $pageTitle }}</title>
        {{-- The favicon uploaded in the account app (sent at sign-in), else the app's own --}}
        <link rel="icon" href="{{ (auth()->check() ? workbench()->brandingUrl('favicon_url') : null) ?: config('app.favicon', '/favicon.ico') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        @stack('head')

        <!-- Scripts -->
        @vite($vite)
    </head>
    <body class="font-sans antialiased bg-zinc-100 text-zinc-900" data-layout="{{ $layout }}">
        @include('shared-ui::workbench.' . $layout)

        @unless ($inertia)
            <x-page-loading/>
            <x-confirm-modal/>
            <x-prompt-modal/>
            @include('shared-ui::workbench.dirty-forms')
        @endunless
        @stack('scripts')
    </body>
</html>
