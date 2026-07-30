@props(['title' => '', 'subtitle' => '', 'extra' => '', 'class' => ''])

<div class="bg-white rounded-lg shadow-md hover:shadow-xl transition-shadow duration-200 {{ $class }}">
    @if ($title)
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">{{ $title }}</h3>
                @if ($subtitle)
                    <p class="text-sm text-gray-500 mt-1">{{ $subtitle }}</p>
                @endif
            </div>
            @if ($extra)
                <div>
                    {{ $extra }}
                </div>
            @endif
        </div>
    @endif

    <div class="px-6 py-4">
        {{ $slot }}
    </div>
</div>