@props([
    'icon' => 'chart',
    'title',
    'description',
    'variant' => 'dashed',
])

@php
    $icons = [
        'chart' => '<path d="M4 20V10M10 20V4M16 20v-8M22 20H2" />',
        'users' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" /><circle cx="9" cy="7" r="4" /><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" />',
        'clock' => '<circle cx="12" cy="12" r="9" /><path d="M12 7v5l3 2" />',
        'list' => '<path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01" />',
        'message' => '<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2Z" />',
        'lightbulb' => '<path d="M9 18h6m-5 3h4m2.5-6.5A7 7 0 1 0 7.5 14.5C8.5 15.4 9 16.3 9 18h6c0-1.7.5-2.6 1.5-3.5Z" />',
    ];
@endphp

<div class="admin-empty {{ $variant === 'grid' ? 'admin-empty--grid' : 'admin-empty--dashed' }}" role="status">
    <span class="admin-empty-icon" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">{!! $icons[$icon] ?? $icons['chart'] !!}</svg>
    </span>
    <p class="admin-empty-title">{{ $title }}</p>
    <p class="admin-empty-description">{{ $description }}</p>
</div>
