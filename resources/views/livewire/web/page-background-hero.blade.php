<div>
    @if ($hero !== '')

        <div class="relative z-10 h-[10vh] w-full overflow-hidden md:h-[30vh]" oncontextmenu="return false">

            <!-- 背景圖 -->
            <img src="{{ asset('images/hero/' . $hero) }}" alt="Hero" class="h-full w-full object-cover" loading="eager"
                decoding="async" fetchpriority="high">

            <!-- 左 → 右 的綠色漸層遮罩 -->
            <div class="banner-gradient pointer-events-none absolute inset-0"></div>
            @php
                if ($segment1 === 'results' && $segment2) {
                    $fontSizeClass = 'text-xl md:text-[3rem] md:leading-[3rem]';
                    $fontSizeClass2 = 'md:text-[3rem]';
                } else {
                    $fontSizeClass = 'text-4xl md:text-[5rem] md:leading-[7rem]';
                    $fontSizeClass2 = 'text-2xl md:text-[4rem]  ';
                }
            @endphp
            <!-- 中央標題 -->
            <div
                class="hero-title-stroke {{ $fontSizeClass }} absolute left-8 top-1/2 -translate-y-1/2 font-extrabold text-white md:left-12 md:tracking-normal">

                @php($locale = app()->getLocale())

                @if ($locale === 'en')
                    {{-- 英文介面：只顯示英文 --}}
                    {{ $page->title_en }}
                @else
                    {{-- 中文介面：英文 + 中文都顯示 --}}
                    <div>
                        {{ $page->title_en }}
                    </div>
                    <div class="{{ $fontSizeClass2 }} mt-1 md:tracking-normal">
                        {{ $page->title_zh_tw }}
                    </div>
                @endif
            </div>

        </div>

    @endif
    <div aria-label="breadcrumb" class="breadcrumb-separator space-x-1 bg-gray-100 pb-2 pl-4 pt-2 text-gray-600">
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
