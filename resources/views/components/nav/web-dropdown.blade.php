<div x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">

    <div class='web-nav'>
        <a href="" :class="{ 'font-bold': open }">
            {{ $label }}
        </a>
    </div>
    <div x-show="open" x-cloak
        class="absolute left-0 z-100
            relative
            before:content-['']
            before:absolute
            before:-top-4
            before:left-0
            before:right-0
            before:h-8
            before:transparent
            before:z-100">
        <div class="web-btn-navigation-2 top-4">
            @foreach ($routes as $item)
                {{--  --}}
                <a href="{{ route($item['route']) }}" class="block px-4 py-2 hover:bg-gray-100">
                    {{ $item['label'] }}
                </a>
            @endforeach
        </div>
    </div>
</div>
