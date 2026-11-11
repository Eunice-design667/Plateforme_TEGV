@props(['member', 'size' => 'md'])

@php
    $sizes = [
        'sm' => 'size-9 text-xs',
        'md' => 'size-12 text-sm',
        'lg' => 'size-20 text-xl',
        'xl' => 'size-28 text-3xl',
    ];
    $palette = ['bg-navy-100 text-navy-800', 'bg-gold-100 text-gold-600', 'bg-crimson-50 text-crimson-700'];
    $tone = $palette[$member->id % count($palette)];
@endphp

@if ($member->photo_url)
    <img src="{{ $member->photo_url }}" alt="{{ $member->full_name }}" {{ $attributes->merge(['class' => "{$sizes[$size]} shrink-0 rounded-full object-cover ring-2 ring-white"]) }}>
@else
    <span {{ $attributes->merge(['class' => "{$sizes[$size]} {$tone} inline-flex shrink-0 items-center justify-center rounded-full font-bold ring-2 ring-white"]) }} aria-hidden="true">{{ $member->initials }}</span>
@endif
