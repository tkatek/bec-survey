<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#071326">
        <title>{{ config('app.name', 'Boston English Center') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen overflow-x-hidden bg-[#f4f7ff] font-['Plus_Jakarta_Sans'] text-[#071326] antialiased">
        <div x-data="{ sidebarOpen: false }" @keydown.escape.window="sidebarOpen = false" class="min-h-screen">
            <div x-cloak x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 z-40 bg-[#071326]/45 backdrop-blur-sm lg:hidden" @click="sidebarOpen = false" aria-hidden="true"></div>

            <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" class="fixed inset-y-0 left-0 z-50 flex w-72 flex-col border-r border-[#dce5f2] bg-white px-5 py-6 transition-transform duration-200 ease-out lg:translate-x-0" aria-label="Administrator navigation">
                <div class="flex items-center justify-between gap-4 px-2">
                    <a href="{{ route('dashboard') }}" class="rounded-xl focus:outline-none focus-visible:ring-2 focus-visible:ring-[#1357e8] focus-visible:ring-offset-4">
                        <img class="w-48" src="{{ asset('images/conversation-room/boston-english-center-logo.png') }}" alt="Boston English Center">
                    </a>
                    <button type="button" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 lg:hidden" @click="sidebarOpen = false" aria-label="Close navigation">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 6 12 12M18 6 6 18" /></svg>
                    </button>
                </div>

                <nav class="mt-10 grid gap-2">
                    <a href="{{ route('dashboard') }}" @class(['flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-bold transition', 'bg-[#edf3ff] text-[#1357e8]' => request()->routeIs('dashboard'), 'text-slate-600 hover:bg-slate-50 hover:text-[#071326]' => ! request()->routeIs('dashboard')])>
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12 12 4l9 8v8a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1Z" /></svg>
                        Dashboard
                    </a>
                    <a href="{{ route('admin.questions.index') }}" @class(['flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-bold transition', 'bg-[#edf3ff] text-[#1357e8]' => request()->routeIs('admin.questions.*'), 'text-slate-600 hover:bg-slate-50 hover:text-[#071326]' => ! request()->routeIs('admin.questions.*')])>
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 3h12a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z" /><path d="M8 8h8M8 12h8M8 16h5" /></svg>
                        Questions
                    </a>
                    <a href="{{ route('conversation-room.create') }}" target="_blank" class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-bold text-slate-600 transition hover:bg-slate-50 hover:text-[#071326]">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 3h7v7M10 14 21 3" /><path d="M21 14v5a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5" /></svg>
                        Open student survey
                    </a>
                    <a href="{{ route('profile.edit') }}" @class(['flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-bold transition', 'bg-[#edf3ff] text-[#1357e8]' => request()->routeIs('profile.*'), 'text-slate-600 hover:bg-slate-50 hover:text-[#071326]' => ! request()->routeIs('profile.*')])>
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4" /><path d="M4 21a8 8 0 0 1 16 0" /></svg>
                        Account
                    </a>
                </nav>

                <div class="mt-auto rounded-2xl bg-[#071326] p-4 text-white">
                    <p class="truncate text-sm font-bold">{{ Auth::user()->name }}</p>
                    <p class="mt-1 truncate text-xs text-blue-100">{{ Auth::user()->email }}</p>
                    <form method="POST" action="{{ route('logout') }}" class="mt-4">
                        @csrf
                        <button class="flex w-full items-center justify-center gap-2 rounded-xl bg-white/10 px-3 py-2 text-xs font-bold transition hover:bg-white/20 focus:outline-none focus-visible:ring-2 focus-visible:ring-white">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 17l5-5-5-5M15 12H3" /><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4" /></svg>
                            Sign out
                        </button>
                    </form>
                </div>
            </aside>

            <main class="min-h-screen min-w-0 lg:pl-72">
                <header class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-[#dce5f2] bg-white/90 px-4 backdrop-blur lg:hidden">
                    <button type="button" class="rounded-xl border border-[#dce5f2] p-2.5 text-[#071326]" @click="sidebarOpen = true" aria-label="Open navigation">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7h16M4 12h16M4 17h16" /></svg>
                    </button>
                    <img class="w-36" src="{{ asset('images/conversation-room/boston-english-center-logo.png') }}" alt="Boston English Center">
                    <span class="h-10 w-10" aria-hidden="true"></span>
                </header>
                @isset($header)
                    <div class="border-b border-[#dce5f2] bg-white px-5 py-6 sm:px-8">
                        {{ $header }}
                    </div>
                @endisset
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
