<div>
    @if ($hero !== '')
        @php
            // 判斷是否在首頁

            $isIndex = request()->routeIs('webindex');
            $heroClass = $isIndex ? 'h-[45vh] md:h-[60vh]' : 'h-[10vh] md:h-[30vh]';
        @endphp
        <div class="relative w-full {{ $heroClass }} overflow-hidden z-10" oncontextmenu="return false">
            <img src="{{ asset('images/hero/' . $hero) }}" alt="Hero" class="w-full h-full object-cover" loading="eager"
                decoding="async" fetchpriority="high">
            {{-- 可選：在圖上疊一層遮罩與標題 --}}
            @if ($isIndex)
                <div class="absolute bottom-10 px-12 py-6 rounded-r-[15px]"
                    style='background-color: rgb(255 255 255 / 0.6);'>
                    <div class="items-end  @if(app()->getLocale() === 'en') block @else lg:flex lg:space-x-4 @endif ">
                        <p class="text-black text-2xl md:text-4xl font-bold">
                            {{ __('web.index_hero_1') }}
                            {{-- 讓長期生態調查資料，說出台灣森林的故事 --}}
                        </p>
                        {{-- <p class="text-black text-xl md:text-2xl font-bold ">
                            {{ __('web.index_hero_2') }}
                        </p> --}}
                    </div>
                </div>
            @else
                <div
                    class="absolute left-1/2 top-1/2 
            -translate-x-1/2 -translate-y-1/2
            text-white text-4xl md:text-5xl font-extrabold  
            tracking-widest md:tracking-[0.5em] text-center hero-title-stroke">

                    @if (isset($title))
                        {{ $title }}
                    @endif
                </div>
            @endif

        </div>
    @endif

</div>
