@props(['type' => 'info', 'message' => ''])

@php
    $colors = match($type) {
        'success' => 'bg-green-100 text-green-800 border-green-300',
        'error'   => 'bg-red-100 text-red-800 border-red-300',
        'warning' => 'bg-yellow-100 text-yellow-800 border-yellow-300',
        'info'    => 'bg-blue-100 text-blue-800 border-blue-300',
        default   => 'bg-gray-100 text-gray-800 border-gray-300',
    };
    $icons = match($type) {
        'success' => 'fa-check-circle',
        'error'   => 'fa-times-circle',
        'warning' => 'fa-exclamation-triangle',
        'info'    => 'fa-info-circle',
        default   => 'fa-info-circle',
    };
@endphp

<div class="flex items-center p-4 mb-4 rounded-lg border {{ $colors }}" role="alert">
    <i class="fas {{ $icons }} mr-3 text-lg"></i>
    <span class="text-sm font-medium">{{ $message }}</span>
</div>