@props(['title'])

<div {{ $attributes->class(['species-chart-panel rounded-md bg-white px-6 py-4']) }}>
    <div class="mb-2 flex items-start pr-4">
        <div class="mr-2 w-1 self-stretch bg-yellow-400"></div>
        <div class="whitespace-normal font-semibold leading-relaxed text-gray-800">
            {{ $title }}
        </div>
    </div>

    @isset($summary)
        <div class="mt-2">
            {{ $summary }}
        </div>
    @endisset

    {{ $slot }}
</div>
