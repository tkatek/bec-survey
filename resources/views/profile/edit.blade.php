<x-app-layout>
    <div class="mx-auto w-full max-w-4xl px-4 py-6 sm:px-6 sm:py-8 lg:px-8">
        <header>
            <p class="text-xs font-extrabold uppercase tracking-[0.12em] text-[#1457ff]">Administration</p>
            <h1 class="mt-1 text-2xl font-extrabold tracking-[-0.035em] text-[#0b1739] sm:text-[2rem]">Settings</h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-[#64748b]">Manage your administrator account, keep your sign-in password current, and control account data.</p>
        </header>

        <div class="mt-6 grid gap-5">
            <section class="admin-card p-5 sm:p-7">
                @include('profile.partials.update-profile-information-form')
            </section>

            <section class="admin-card p-5 sm:p-7">
                @include('profile.partials.update-password-form')
            </section>

            <section class="admin-card p-5 sm:p-7">
                @include('profile.partials.delete-user-form')
            </section>
        </div>
    </div>
</x-app-layout>
