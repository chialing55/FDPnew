@props(['label', 'pages' => collect()])

<div x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
    <div class='web-nav'>
        <a href="javascript:void(0)" :class="{ 'font-bold': open }">
            {{ $label }}
        </a>
    </div>
    

    <div x-show="open" x-cloak
        class="z-100 before:transparent before:z-100 absolute relative left-0 before:absolute before:-top-4 before:left-0 before:right-0 before:h-8 before:content-['']">
        <div class="web-btn-navigation-2 top-4">
            @foreach ($pages as $page)
                @php
                    $isActive = request()->is($page->slug . '*');
                    $title = app()->getLocale() === 'en' ? $page->title_en : $page->title_zh_tw;
                @endphp

                <a href="{{ url($page->slug) }}"
                    class="{{ $isActive ? 'font-bold text-forest' : '' }} block px-4 py-2 hover:bg-gray-100">
                    {{ $title }}
                </a>
            @endforeach
        </div>
    </div>
</div>
