<div>
    @if ($hero !== '')
        <div class="relative w-full h-[45vh] md:h-[60vh] overflow-hidden z-10" oncontextmenu="return false">
            <img src="{{ asset('images/hero/' . $hero) }}" alt="Hero" class="w-full h-full object-cover" loading="eager"
                decoding="async" fetchpriority="high">
            {{-- 可選：在圖上疊一層遮罩與標題 --}}

                <div class="absolute bottom-10 px-12 py-6 rounded-r-[15px] bg-forest-dark/70 text-white"
                    >
                    <div class=" @if(app()->getLocale() === 'en') block @else lg:flex lg:space-x-4 @endif ">
                        <p class="text-2xl md:text-5xl font-bold">
                            {{ __('web.index_hero_1') }}
                            {{-- 讓長期生態調查資料，說出台灣森林的故事 --}}
                        </p>
                        {{-- <p class="text-black text-xl md:text-2xl font-bold ">
                            {{ __('web.index_hero_2') }}
                        </p> --}}
                    </div>
                </div>
        </div>
    @endif
</div>
