<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#eef4ff">
        <title>{{ config('app.name', 'Boston English Center') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="admin-app-body min-h-screen overflow-x-hidden font-sans text-[#071326] antialiased">
        <div x-data="{ sidebarOpen: false }" @keydown.escape.window="sidebarOpen = false" class="admin-shell min-h-screen">
            <div x-cloak x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 z-40 bg-[#071326]/40 backdrop-blur-sm lg:hidden" @click="sidebarOpen = false" aria-hidden="true"></div>

            <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" class="admin-sidebar fixed inset-y-0 left-0 z-50 flex w-64 flex-col overflow-x-hidden overflow-y-auto px-4 py-5 transition-transform duration-200 ease-out lg:inset-y-3 lg:left-3 lg:translate-x-0 lg:rounded-[1.6rem]" aria-label="Administrator navigation">
                <div class="admin-brand flex items-center justify-between gap-4 rounded-2xl px-3 py-4">
                    <a href="{{ route('dashboard') }}" class="rounded-xl focus:outline-none focus-visible:ring-2 focus-visible:ring-[#1357e8] focus-visible:ring-offset-4">
                        <img class="w-44 max-w-full" src="{{ asset('images/conversation-room/boston-english-center-logo.png') }}" alt="Boston English Center">
                    </a>
                    <button type="button" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 lg:hidden" @click="sidebarOpen = false" aria-label="Close navigation">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 6 12 12M18 6 6 18" /></svg>
                    </button>
                </div>

                <nav class="mt-7 grid gap-1.5">
                    <a href="{{ route('dashboard') }}" @class(['admin-nav-link group', 'is-active' => request()->routeIs('dashboard')])>
                        <span class="admin-nav-icon"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12 12 4l9 8v8a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1Z" /></svg></span>
                        <span>Dashboard</span>
                    </a>
                    <a href="{{ route('admin.common-answers') }}" @class(['admin-nav-link group', 'is-active' => request()->routeIs('admin.common-answers')])>
                        <span class="admin-nav-icon"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 12h10M4 18h7" /><path d="m17 15 3 3 3-4" /></svg></span>
                        <span>Common Answers</span>
                    </a>
                    <a href="{{ route('admin.questions.index') }}" @class(['admin-nav-link group', 'is-active' => request()->routeIs('admin.questions.*')])>
                        <span class="admin-nav-icon"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 3h12a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z" /><path d="M8 8h8M8 12h8M8 16h5" /></svg></span>
                        <span>Questions</span>
                    </a>
                    <a href="{{ route('profile.edit') }}" @class(['admin-nav-link group', 'is-active' => request()->routeIs('profile.*')])>
                        <span class="admin-nav-icon"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3" /><path d="M19.4 15a1.7 1.7 0 0 0 .34 1.87l.06.06-2.83 2.83-.06-.06A1.7 1.7 0 0 0 15 19.4a1.7 1.7 0 0 0-1 .6 1.7 1.7 0 0 0-.4 1.1V21h-4v-.09A1.7 1.7 0 0 0 8.6 19.4a1.7 1.7 0 0 0-1.87.34l-.06.06-2.83-2.83.06-.06A1.7 1.7 0 0 0 4.6 15a1.7 1.7 0 0 0-1.6-1H3v-4h.09A1.7 1.7 0 0 0 4.6 9a1.7 1.7 0 0 0-.34-1.87l-.06-.06 2.83-2.83.06.06A1.7 1.7 0 0 0 9 4.6a1.7 1.7 0 0 0 1-.6 1.7 1.7 0 0 0 .4-1.1V3h4v.09A1.7 1.7 0 0 0 15.4 4.6a1.7 1.7 0 0 0 1.87-.34l.06-.06 2.83 2.83-.06.06A1.7 1.7 0 0 0 19.4 9a1.7 1.7 0 0 0 1.6 1h.09v4H21a1.7 1.7 0 0 0-1.6 1Z" /></svg></span>
                        <span>Settings</span>
                    </a>
                </nav>

                <div class="mt-auto grid min-w-0 gap-3 pt-6">
                    <a href="{{ route('conversation-room.create') }}" target="_blank" rel="noopener" class="admin-secondary-action flex w-full min-w-0 items-center justify-center gap-2 rounded-xl px-3 py-2.5 text-xs font-bold focus:outline-none focus-visible:ring-2 focus-visible:ring-[#1357e8]">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 3h7v7M10 14 21 3" /><path d="M21 14v5a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5" /></svg>
                        Open student survey
                    </a>
                    <div class="admin-account-card w-full min-w-0 max-w-full overflow-hidden rounded-2xl p-4 text-white">
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-white/10 text-blue-100 ring-1 ring-inset ring-white/15"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="3.5" /><path d="M5 21a7 7 0 0 1 14 0" /></svg></span>
                            <div class="min-w-0">
                                <p class="text-[10px] font-bold text-blue-200">Administrator</p>
                                <p class="mt-0.5 min-w-0 truncate text-sm font-bold">{{ Auth::user()->name }}</p>
                            </div>
                        </div>
                        <p class="mt-3 min-w-0 truncate text-xs text-blue-100" title="{{ Auth::user()->email }}">{{ Auth::user()->email }}</p>
                        <form method="POST" action="{{ route('logout') }}" class="mt-4 min-w-0">
                            @csrf
                            <button class="flex w-full min-w-0 items-center justify-center gap-2 rounded-xl bg-white/10 px-3 py-2 text-xs font-bold transition hover:bg-white/20 focus:outline-none focus-visible:ring-2 focus-visible:ring-white">
                                <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 17l5-5-5-5M15 12H3" /><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4" /></svg>
                                Sign out
                            </button>
                        </form>
                    </div>
                </div>
            </aside>

            <main class="admin-main min-h-screen min-w-0 lg:pl-[17rem]">
                <header class="admin-mobile-header sticky top-0 z-30 flex h-16 items-center justify-between px-4 lg:hidden">
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
