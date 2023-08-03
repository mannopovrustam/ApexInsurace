<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="stylesheet" href="https://fonts.bunny.net/css2?family=Nunito:wght@400;600;700&display=swap">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link href="/assets/css/bootstrap.min.css" id="bootstrap-style" rel="stylesheet" type="text/css" />
    <link href="/assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/css/app.min.css" id="app-style" rel="stylesheet" type="text/css" />
    @livewireStyles
    @yield('style')
    <!-- Scripts -->
</head>
<body class="font-sans antialiased">

<div id="layout-wrapper">
    @include('components.navbar')
    @include('components.sidebar')

    <div class="main-content">
        <div class="page-content">
            {{--{{ $slot ?? null }}--}}
            @yield('content')
        </div>
        @include('components.footer')
    </div>

</div>
@include('components.scripts')
@livewireScripts
</body>
</html>
