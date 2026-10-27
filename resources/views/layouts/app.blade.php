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

            <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" class="fixed inset-y-0 left-0 z-50 flex w-60 flex-col border-r border-[#dce5f2] bg-white px-4 py-6 transition-transform duration-200 ease-out lg:translate-x-0" aria-label="Administrator navigation">
                <div class="flex items-center justify-between gap-4 px-2">
                    <a href="{{ route('dashboard') }}" class="rounded-xl focus:outline-none focus-visible:ring-2 focus-visible:ring-[#1357e8] focus-visible:ring-offset-4">
                        <img class="w-44" src="{{ asset('images/conversation-room/boston-english-center-logo.png') }}" alt="Boston English Center">
                    </a>
                    <button type="button" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 lg:hidden" @click="sidebarOpen = false" aria-label="Close navigation">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 6 12 12M18 6 6 18" /></svg>
                    </button>
                </div>

                <nav class="mt-9 grid gap-1.5">
                    <a href="{{ route('dashboard') }}" @class(['relative flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-bold transition focus:outline-none focus-visible:ring-2 focus-visible:ring-[#1357e8]', 'bg-[#edf3ff] text-[#1357e8] before:absolute before:-left-4 before:h-9 before:w-1 before:rounded-r-full before:bg-[#1357e8]' => request()->routeIs('dashboard'), 'text-[#53668d] hover:bg-slate-50 hover:text-[#071326]' => ! request()->routeIs('dashboard')])>
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12 12 4l9 8v8a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1Z" /></svg>
                        Dashboard
                    </a>
                    <a href="{{ route('dashboard') }}#survey-results" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-bold text-[#53668d] transition hover:bg-slate-50 hover:text-[#071326] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#1357e8]">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 20V10M10 20V4M16 20v-7M22 20H2" /></svg>
                        Survey Results
                    </a>
                    <a href="{{ route('dashboard') }}#responses" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-bold text-[#53668d] transition hover:bg-slate-50 hover:text-[#071326] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#1357e8]">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 6h13M8 12h13M8 18h13" /><circle cx="3.5" cy="6" r="1" /><circle cx="3.5" cy="12" r="1" /><circle cx="3.5" cy="18" r="1" /></svg>
                        Responses
                    </a>
                    <a href="{{ route('admin.questions.index') }}" @class(['relative flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-bold transition focus:outline-none focus-visible:ring-2 focus-visible:ring-[#1357e8]', 'bg-[#edf3ff] text-[#1357e8] before:absolute before:-left-4 before:h-9 before:w-1 before:rounded-r-full before:bg-[#1357e8]' => request()->routeIs('admin.questions.*'), 'text-[#53668d] hover:bg-slate-50 hover:text-[#071326]' => ! request()->routeIs('admin.questions.*')])>
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 3h12a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z" /><path d="M8 8h8M8 12h8M8 16h5" /></svg>
                        Questions
                    </a>
                    <a href="{{ route('profile.edit') }}" @class(['relative flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-bold transition focus:outline-none focus-visible:ring-2 focus-visible:ring-[#1357e8]', 'bg-[#edf3ff] text-[#1357e8] before:absolute before:-left-4 before:h-9 before:w-1 before:rounded-r-full before:bg-[#1357e8]' => request()->routeIs('profile.*'), 'text-[#53668d] hover:bg-slate-50 hover:text-[#071326]' => ! request()->routeIs('profile.*')])>
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3" /><path d="M19.4 15a1.7 1.7 0 0 0 .34 1.87l.06.06-2.83 2.83-.06-.06A1.7 1.7 0 0 0 15 19.4a1.7 1.7 0 0 0-1 .6 1.7 1.7 0 0 0-.4 1.1V21h-4v-.09A1.7 1.7 0 0 0 8.6 19.4a1.7 1.7 0 0 0-1.87.34l-.06.06-2.83-2.83.06-.06A1.7 1.7 0 0 0 4.6 15a1.7 1.7 0 0 0-1.6-1H3v-4h.09A1.7 1.7 0 0 0 4.6 9a1.7 1.7 0 0 0-.34-1.87l-.06-.06 2.83-2.83.06.06A1.7 1.7 0 0 0 9 4.6a1.7 1.7 0 0 0 1-.6 1.7 1.7 0 0 0 .4-1.1V3h4v.09A1.7 1.7 0 0 0 15.4 4.6a1.7 1.7 0 0 0 1.87-.34l.06-.06 2.83 2.83-.06.06A1.7 1.7 0 0 0 19.4 9a1.7 1.7 0 0 0 1.6 1h.09v4H21a1.7 1.7 0 0 0-1.6 1Z" /></svg>
                        Settings
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

            <main class="min-h-screen min-w-0 lg:pl-60">
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
