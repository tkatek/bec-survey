<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Conversation Room Student Feedback Survey') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="grid gap-8 p-6 text-gray-900">
                    @include('conversation-room.sections.attendance-participation')
                    @include('conversation-room.sections.updated-conversation-room')
                    @include('conversation-room.sections.preferences')
                    @include('conversation-room.sections.feedback')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
