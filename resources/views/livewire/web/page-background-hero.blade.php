<div>
    @if ($hero !== '')

        <div class="relative w-full h-[10vh] md:h-[30vh] overflow-hidden z-10" oncontextmenu="return false">
            <img src="{{ asset('images/hero/' . $hero) }}" alt="Hero" class="w-full h-full object-cover" loading="eager"
                decoding="async" fetchpriority="high">
            {{-- 可選：在圖上疊一層遮罩與標題 --}}

            <div
                class="absolute left-1/2 top-1/2 
            -translate-x-1/2 -translate-y-1/2
            text-white text-4xl md:text-[5rem] font-extrabold  
            tracking-widest md:tracking-[0.5em] text-center hero-title-stroke">

                @if (isset($title))
                    {{ $title }}
                @endif
            </div>

        </div>
    @endif
    <div aria-label="breadcrumb" class="text-gray-600 space-x-1 mt-2 mb-2 ml-4 breadcrumb-separator">
        @foreach ($breadcrumbs as $i => $crumb)
            @if ($i > 0)
                <span>></span>
            @endif
            @if (!empty($crumb['url']))
                <a href="{{ $crumb['url'] }}">
                    {{ $crumb['label'] }}
                </a>
            @else
                <span>
                    {{ $crumb['label'] }}
                </span>
            @endif
            @endforeach
    </div>


</div>
