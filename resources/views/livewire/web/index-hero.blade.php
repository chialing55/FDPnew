<div>
    @if ($hero !== '')
        <div class="relative z-10 h-[45vh] w-full overflow-hidden md:h-[60vh]" oncontextmenu="return false">
            {{-- Hero 背景圖 --}}
            <img src="{{ asset('images/hero/' . $hero) }}" alt="Hero" class="h-full w-full object-cover" loading="eager"
                decoding="async" fetchpriority="high">

            {{-- 左下角的漸層遮罩 --}}
            <div
                class="hero-bottom-gradient pointer-events-none absolute bottom-0 left-0 h-[60%] w-[70%] md:h-[50%] md:w-[50%]">
            </div>

            {{-- 文字區塊 --}}
            <div class="absolute bottom-8 rounded-r-[15px] px-1 py-1 text-white">
                <div>
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
                            </span>
                            <span class='-ml-1 md:-ml-8'>{{ __('web.index_hero_1_tail') }}</span>
                        @endif

                    </p>
                </div>
            </div>
        </div>

    @endif
</div>
