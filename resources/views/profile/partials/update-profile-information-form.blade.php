<section>
    <header class="flex items-start gap-3.5">
        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-[#edf4ff] text-[#1357e8]"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="3.5" /><path d="M5 21a7 7 0 0 1 14 0" /></svg></span>
        <div class="min-w-0">
            <h2 class="text-base font-extrabold tracking-[-0.01em] text-[#0b1739]">{{ __('Account details') }}</h2>
            <p class="mt-1 text-sm leading-5 text-[#64748b]">{{ __("Update your account's profile information and email address.") }}</p>
        </div>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 grid gap-5">
        @csrf
        @method('patch')

        <div class="grid gap-4 sm:grid-cols-2">
            <label class="grid gap-1.5 text-xs font-extrabold text-[#10214b]">
                {{ __('Name') }}
                <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name" class="rounded-xl border-[#cdd9ea] px-3.5 py-2.5 text-sm font-semibold text-[#10214b] placeholder:text-[#9aabc6] focus:border-[#1357e8] focus:ring-[#1357e8]">
                <x-input-error :messages="$errors->get('name')" />
            </label>

            <label class="grid gap-1.5 text-xs font-extrabold text-[#10214b]">
                {{ __('Email') }}
                <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required autocomplete="username" class="rounded-xl border-[#cdd9ea] px-3.5 py-2.5 text-sm font-semibold text-[#10214b] placeholder:text-[#9aabc6] focus:border-[#1357e8] focus:ring-[#1357e8]">
                <x-input-error :messages="$errors->get('email')" />
            </label>
        </div>

        @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
            <div>
                <p class="text-sm text-[#334155]">
                    {{ __('Your email address is unverified.') }}

                    <button form="send-verification" class="underline text-[#1357e8] hover:text-[#0b43be]">
                        {{ __('Click here to re-send the verification email.') }}
                    </button>
                </p>

                @if (session('status') === 'verification-link-sent')
                    <p class="mt-2 font-medium text-sm text-emerald-600">
                        {{ __('A new verification link has been sent to your email address.') }}
                    </p>
                @endif
            </div>
        @endif

        <div class="flex items-center gap-4">
            <button type="submit" class="admin-primary-action inline-flex items-center justify-center gap-2 rounded-xl px-5 py-2.5 text-xs font-extrabold text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-[#1357e8] focus-visible:ring-offset-2">{{ __('Save changes') }}</button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm font-semibold text-emerald-600"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
