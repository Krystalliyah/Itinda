<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} - Admin</title>
    @vite(['resources/js/app.ts'])
</head>
<body>
    @include('partials.admin-nav')
    
    <main>
        @yield('content')
    </main>
</body>
</html>
