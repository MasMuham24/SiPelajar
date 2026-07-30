@props(['text' => '', 'class' => '', 'type' => 'button'])

<button type="{{ $type }}" class="inline-flex items-center justify-center px-6 py-3 bg-blue-500 hover:bg-blue-600 text-white font-medium rounded-lg transition-colors shadow-lg hover:shadow-xl {{ $class }}">
    @if (isset($slot))
        <span class="mr-2">{{ $slot }}</span>
    @endif
    {{ $text }}
</button>