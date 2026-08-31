@props(['label', 'pages' => collect(), 'shift' => false])

<div x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
    <div class='web-nav'>
        <a href="javascript:void(0)" :class="{ 'font-bold': open }">
            {{ $label }}
        </a>
    </div>
    
@php
    $shiftClass = $shift ? 'transform: translateX(-25%);' : '';
@endphp

    <div x-show="open" x-cloak
        class="z-100 before:transparent before:z-100 absolute relative left-0 before:absolute before:-top-4 before:left-0 before:right-0 before:h-8 before:content-['']">
        <div class="web-btn-navigation-2 top-4  " style = '{{ $shiftClass }}'>
            @foreach ($pages as $page)
                @php
                    $isActive = request()->is($page->slug . '*');
                    $title = $page->nav_group === 'subjects'
                        ? ($page->subject?->short_name ?: $page->title)
                        : $page->title;
                @endphp

                <a href="{{ url($page->slug) }}"
                    class="{{ $isActive ? 'font-bold text-forest' : '' }} block whitespace-nowrap px-4 py-2 hover:bg-gray-100">
                    {{ $title }}
                </a>
            @endforeach
        </div>
    </div>
</div>
