<x-guest-layout>
    <main class="relative grid min-h-screen overflow-hidden lg:grid-cols-[minmax(0,1.05fr)_minmax(32rem,0.95fr)]">
        <section class="relative min-h-[22rem] overflow-hidden bg-[#e9f2ff] lg:min-h-screen">
            <img class="absolute inset-0 h-full w-full object-cover object-center" src="{{ asset('images/conversation-room/conversation-room-background.webp') }}" alt="Bright Boston English Center learning space">
            <div class="absolute inset-0 bg-gradient-to-r from-white via-white/85 to-white/20 lg:bg-gradient-to-r lg:from-white lg:via-white/80 lg:to-transparent"></div>
            <div class="relative z-10 flex h-full max-w-2xl flex-col px-6 py-7 sm:px-10 lg:justify-center lg:px-16 xl:px-24">
                <a href="{{ route('conversation-room.create') }}" class="w-48 rounded-xl focus:outline-none focus-visible:ring-2 focus-visible:ring-[#1357e8] focus-visible:ring-offset-4 sm:w-60">
                    <img src="{{ asset('images/conversation-room/boston-english-center-logo.png') }}" alt="Boston English Center">
                </a>
                <div class="mt-auto max-w-xl pb-3 pt-16 lg:mt-20 lg:pb-0 lg:pt-0">
                    <p class="text-sm font-extrabold text-[#1357e8]">Better conversations. Brighter futures.</p>
                    <h1 class="mt-3 text-3xl font-extrabold leading-tight tracking-[-0.04em] text-[#071326] sm:text-4xl xl:text-5xl">Turn student feedback into better speaking sessions.</h1>
                    <p class="mt-4 max-w-lg text-sm font-medium leading-6 text-[#42547a] sm:text-base">See what gives learners confidence, what holds them back, and what to improve next.</p>
                    <ul class="mt-6 hidden gap-3 text-sm font-bold text-[#10214b] sm:grid lg:grid">
                        <li class="flex items-center gap-3"><span class="grid h-9 w-9 place-items-center rounded-xl bg-[#e8f1ff] text-[#1357e8]"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19V9M10 19V5M16 19v-7M22 19H2" /></svg></span>Understand strengths and weak points</li>
                        <li class="flex items-center gap-3"><span class="grid h-9 w-9 place-items-center rounded-xl bg-emerald-50 text-emerald-500"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" /><circle cx="9" cy="7" r="4" /><path d="M22 21v-2a4 4 0 0 0-3-3.87" /></svg></span>Shape sessions around real learner needs</li>
                    </ul>
                </div>
            </div>
        </section>

        <section class="relative grid place-items-center bg-[#f4f7ff] p-4 sm:p-8 lg:p-12">
            <div class="absolute -right-24 -top-24 h-72 w-72 rounded-full bg-[#1357e8]/10 blur-3xl" aria-hidden="true"></div>
            <div class="relative w-full max-w-xl rounded-[2rem] border border-white bg-white p-6 shadow-[0_28px_80px_rgba(15,45,90,0.14)] sm:p-10 xl:p-12" x-data="{ showPassword: false }">
                <div class="mx-auto w-48 sm:w-56"><img src="{{ asset('images/conversation-room/boston-english-center-logo.png') }}" alt="Boston English Center"></div>
                <div class="mt-8 text-center">
                    <h2 class="text-2xl font-extrabold tracking-[-0.03em] sm:text-3xl">Administrator login</h2>
                    <p class="mx-auto mt-2 max-w-sm text-sm leading-6 text-[#63749a]">Sign in to view survey insights and manage the student questionnaire.</p>
                </div>

                <x-auth-session-status class="mt-6 rounded-xl bg-emerald-50 p-3 text-sm font-bold text-emerald-700" :status="session('status')" />

                <form method="POST" action="{{ route('login') }}" class="mt-8 grid gap-5">
                    @csrf
                    <label class="grid gap-2 text-sm font-bold text-[#10214b]" for="email">
                        Email address
                        <span class="relative block">
                            <svg class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-[#7890b5]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="5" width="18" height="14" rx="2" /><path d="m3 7 9 6 9-6" /></svg>
                            <input id="email" class="w-full rounded-2xl border-[#cbd7e8] py-3.5 pl-12 pr-4 text-sm font-semibold placeholder:text-[#9bacc7] focus:border-[#1357e8] focus:ring-[#1357e8]" type="email" name="email" value="{{ old('email') }}" placeholder="admin@example.com" required autofocus autocomplete="username">
                        </span>
                        <x-input-error :messages="$errors->get('email')" class="text-xs font-semibold text-rose-600" />
                    </label>

                    <label class="grid gap-2 text-sm font-bold text-[#10214b]" for="password">
                        Password
                        <span class="relative block">
                            <svg class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-[#7890b5]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="10" width="14" height="11" rx="2" /><path d="M8 10V7a4 4 0 0 1 8 0v3" /></svg>
                            <input id="password" class="w-full rounded-2xl border-[#cbd7e8] py-3.5 pl-12 pr-12 text-sm font-semibold placeholder:text-[#9bacc7] focus:border-[#1357e8] focus:ring-[#1357e8]" :type="showPassword ? 'text' : 'password'" name="password" placeholder="Enter your password" required autocomplete="current-password">
                            <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 rounded-lg p-2 text-[#7890b5] hover:bg-[#edf3ff] hover:text-[#1357e8]" @click="showPassword = !showPassword" :aria-label="showPassword ? 'Hide password' : 'Show password'">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z" /><circle cx="12" cy="12" r="2.5" /></svg>
                            </button>
                        </span>
                        <x-input-error :messages="$errors->get('password')" class="text-xs font-semibold text-rose-600" />
                    </label>

                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <label for="remember_me" class="inline-flex items-center gap-2 text-sm font-semibold text-[#42547a]"><input id="remember_me" type="checkbox" class="rounded border-[#b9c9df] text-[#1357e8] focus:ring-[#1357e8]" name="remember">Remember me</label>
                        @if (Route::has('password.request'))<a class="rounded-lg text-sm font-bold text-[#1357e8] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#1357e8]" href="{{ route('password.request') }}">Forgot password?</a>@endif
                    </div>

                    <button class="inline-flex items-center justify-center gap-2 rounded-2xl bg-[#1357e8] px-6 py-4 text-sm font-extrabold text-white shadow-xl shadow-blue-200 transition hover:bg-[#0b43be] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#1357e8] focus-visible:ring-offset-2">
                        Sign in
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6" /></svg>
                    </button>
                </form>

                <p class="mt-7 flex items-center justify-center gap-2 border-t border-[#e5ebf4] pt-6 text-center text-xs font-semibold text-[#7890b5]"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="10" width="14" height="11" rx="2" /><path d="M8 10V7a4 4 0 0 1 8 0v3" /></svg>Secure access for the authorized administrator only.</p>
            </div>
        </section>
    </main>
</x-guest-layout>
