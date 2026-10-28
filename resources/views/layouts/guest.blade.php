<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#071326">
        <title>{{ config('app.name', 'Boston English Center') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen overflow-x-hidden bg-[#f4f7ff] font-sans text-[#071326] antialiased">
        @if (request()->routeIs('login'))
            {{ $slot }}
        @else
            <main class="grid min-h-screen place-items-center p-4">
                <section class="w-full max-w-md rounded-3xl border border-[#dce5f2] bg-white p-6 shadow-[0_24px_60px_rgba(15,45,90,0.12)] sm:p-8">
                    <a href="{{ route('conversation-room.create') }}" class="mx-auto block w-48"><img src="{{ asset('images/conversation-room/boston-english-center-logo.png') }}" alt="Boston English Center"></a>
                    <div class="mt-7">{{ $slot }}</div>
                </section>
            </main>
        @endif
    </body>
</html>
