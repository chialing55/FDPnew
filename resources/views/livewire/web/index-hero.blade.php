<div>
    @if ($hero !== '')
        <div class="relative z-10 h-[45vh] w-full overflow-hidden md:h-[60vh]" oncontextmenu="return false">
            <img src="{{ asset('images/hero/' . $hero) }}" alt="Hero" class="h-full w-full object-cover" loading="eager"
                decoding="async" fetchpriority="high">
            {{-- 可選：在圖上疊一層遮罩與標題 --}}

            <div class="absolute bottom-8 rounded-r-[15px] px-1 py-1 text-white">

                <div class="">
                    <p class="text-2xl font-bold md:text-5xl"
                        style='line-height: 1.2; text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.7);'>
                        @if (app()->getLocale() === 'en')
                            {!! __('web.index_hero_1') !!}
                        @else
                            {{ __('web.index_hero_1_part1') }}<br>

                            <span class="block text-2xl md:text-[5rem] md:leading-[3rem]">
                                {{ __('web.index_hero_1_highlight') }}
                            </span>

                            {{ __('web.index_hero_1_part2') }}<br>

                            <span class="text-2xl md:text-[5rem] md:leading-[3rem]">
                                {{ __('web.index_hero_1_forest') }}
                            </span><span class='-ml-8'>{{ __('web.index_hero_1_tail') }}</span>
                        @endif

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
