<x-app-layout>
    <div class="mx-auto w-full max-w-5xl p-4 sm:p-6 lg:p-8">
        <p class="text-sm font-bold text-[#1357e8]">Survey builder</p>
        <h1 class="mt-1 text-2xl font-extrabold tracking-[-0.03em] sm:text-3xl">Edit question</h1>
        <p class="mt-2 text-sm leading-6 text-[#63749a]">Updates affect future survey responses. Historical answer values remain unchanged.</p>
        @include('admin.questions.form', ['action' => route('admin.questions.update', $question), 'method' => 'PUT', 'submitLabel' => 'Save changes'])
    </div>
</x-app-layout>
