<section>
    <header class="flex items-start gap-3.5">
        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-[#edf4ff] text-[#1357e8]"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="4" y="10" width="16" height="10" rx="2.5" /><path d="M8 10V7a4 4 0 0 1 8 0v3" /><path d="M12 14.5v2.5" /></svg></span>
        <div class="min-w-0">
            <h2 class="text-base font-extrabold tracking-[-0.01em] text-[#0b1739]">{{ __('Update password') }}</h2>
            <p class="mt-1 text-sm leading-5 text-[#64748b]">{{ __('Ensure your account is using a long, random password to stay secure.') }}</p>
        </div>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 grid gap-5">
        @csrf
        @method('put')

        <div class="grid gap-4 sm:grid-cols-3">
            <label class="grid gap-1.5 text-xs font-extrabold text-[#10214b]">
                {{ __('Current Password') }}
                <input id="update_password_current_password" name="current_password" type="password" autocomplete="current-password" class="rounded-xl border-[#cdd9ea] px-3.5 py-2.5 text-sm font-semibold text-[#10214b] placeholder:text-[#9aabc6] focus:border-[#1357e8] focus:ring-[#1357e8]">
                <x-input-error :messages="$errors->updatePassword->get('current_password')" />
            </label>

            <label class="grid gap-1.5 text-xs font-extrabold text-[#10214b]">
                {{ __('New Password') }}
                <input id="update_password_password" name="password" type="password" autocomplete="new-password" class="rounded-xl border-[#cdd9ea] px-3.5 py-2.5 text-sm font-semibold text-[#10214b] placeholder:text-[#9aabc6] focus:border-[#1357e8] focus:ring-[#1357e8]">
                <x-input-error :messages="$errors->updatePassword->get('password')" />
            </label>

            <label class="grid gap-1.5 text-xs font-extrabold text-[#10214b]">
                {{ __('Confirm Password') }}
                <input id="update_password_password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" class="rounded-xl border-[#cdd9ea] px-3.5 py-2.5 text-sm font-semibold text-[#10214b] placeholder:text-[#9aabc6] focus:border-[#1357e8] focus:ring-[#1357e8]">
                <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" />
            </label>
        </div>

        <div class="flex items-center gap-4">
            <button type="submit" class="admin-primary-action inline-flex items-center justify-center gap-2 rounded-xl px-5 py-2.5 text-xs font-extrabold text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-[#1357e8] focus-visible:ring-offset-2">{{ __('Save password') }}</button>

            @if (session('status') === 'password-updated')
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
