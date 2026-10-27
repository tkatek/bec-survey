<x-app-layout>
    <div class="mx-auto w-full max-w-5xl p-4 sm:p-6 lg:p-8">
        <p class="text-sm font-bold text-[#1357e8]">Survey builder</p>
        <h1 class="mt-1 text-2xl font-extrabold tracking-[-0.03em] sm:text-3xl">Add a question</h1>
        <p class="mt-2 text-sm leading-6 text-[#63749a]">The new question will be added to the end of the live survey.</p>
        @include('admin.questions.form', ['action' => route('admin.questions.store'), 'method' => 'POST', 'submitLabel' => 'Add question'])
    </div>
</x-app-layout>
