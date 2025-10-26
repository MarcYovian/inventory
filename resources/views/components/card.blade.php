@props(['title' => '', 'subtitle' => ''])

<div
    {{ $attributes->merge(['class' => 'bg-white dark:bg-gray-800 rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300 overflow-hidden border border-gray-200 dark:border-gray-700']) }}>
    @if ($title || $subtitle)
        <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 border-b border-gray-200 dark:border-gray-600">
            @if ($title)
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200">{{ $title }}</h3>
            @endif
            @if ($subtitle)
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ $subtitle }}</p>
            @endif
        </div>
    @endif

    <div class="p-4">
        {{ $slot }}
    </div>

    @isset($actions)
        <div
            class="bg-gray-50 dark:bg-gray-700 px-4 py-3 border-t border-gray-200 dark:border-gray-600 flex gap-2 justify-end">
            {{ $actions }}
        </div>
    @endisset
</div>
