<!DOCTYPE html>
@php
    $isAuthLightOnlyPage = request()->is('login')
        || request()->is('register')
        || request()->is('forgot-password')
        || request()->is('two-factor-challenge')
        || request()->is('user/confirm-password')
        || request()->is('reset-password')
        || request()->is('reset-password/*')
        || request()->is('email/verify')
        || request()->is('email/verify/*');
@endphp
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @class(['dark' => ! $isAuthLightOnlyPage && ($appearance ?? 'system') == 'dark'])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="user-id" content="{{ auth()->id() }}">

        {{-- Inline script to detect system dark mode preference and apply it immediately --}}
        <script>
            (function() {
                const appearance = '{{ $appearance ?? "system" }}';
                const isAuthLightOnlyPage = {{ $isAuthLightOnlyPage ? 'true' : 'false' }};

                if (isAuthLightOnlyPage) {
                    document.documentElement.classList.remove('dark');
                    return;
                }

                if (appearance === 'system') {
                    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

                    if (prefersDark) {
                        document.documentElement.classList.add('dark');
                    }
                }
            })();
        </script>

        {{-- Inline style to set the HTML background color based on our theme in app.css --}}
        <style>
            html {
                background-color: oklch(1 0 0);
            }

            html.dark {
                background-color: oklch(0.145 0 0);
            }
        </style>

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <link rel="icon" href="/itinda-tab.png" type="image/png">
        <link rel="apple-touch-icon" href="/itinda-tab.png">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        @vite(['resources/js/app.ts'])
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
