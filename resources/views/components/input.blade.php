@props(['name', 'placeholder' => '', 'value' => '', 'required' => false, 'type' => 'text', 'error' => ''])

@php
    $extraClasses = '';
    if ($error) {
        $extraClasses = 'border-red-500 focus:border-red-500 focus:ring-red-500';
    }
@endphp

<div class="relative">
    <input
        type="{{ $type }}"
        name="{{ $name }}"
        id="{{ $name }}"
        placeholder="{{ $placeholder }}"
        value="{{ $value }}"
        {{ $required ? 'required' : '' }}
        class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors placeholder-gray-400 {{ $extraClasses }}"
    />
    <div class="absolute inset-y-0 right-0 flex items-center pr-3">
        <i class="fas fa-{{ $type === 'password' ? 'lock' : 'type-' . $type }}"></i>
    </div>
</div>
@if ($error)
    <p class="mt-1 text-sm text-red-600">{{ $error }}</p>
@endif