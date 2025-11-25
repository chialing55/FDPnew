{{-- 資料展示網頁用 --}}
<header class="border-b bg-white z-30 sticky top-0">
    {{--   @if (isset($id))
    <p style='float:left; margin:110px 0 0 40px; font-size:0.9em;'>
      @php
       echo 'Hi, '.$id
      @endphp
    </p>
  @endif --}}
    @php
        $locale = app()->getLocale(); // 'zh-TW' 或 'en'
    @endphp
    <div class='relative flex justify-between items-center' x-data="{ open: false }">
        {{-- <a href='/web/index'> --}}
        <div class="p-2 md:p-0 flex items-center text-black justify-between">
            @if ($locale === 'zh-TW')
                <span class="font-bold text-base sm:text-lg pl-1 sm:pl-2">台灣</span>
                <img src="{{ asset('/images/web/森林動態樣區.png') }}" class="h-8 sm:h-12 mx-1" alt="Fushan Forest Plot" />
                <span class="font-bold text-base sm:text-lg">研究成果平台</span>
            @else
                <span class="font-bold text-base sm:text-lg pl-1 sm:pl-2">{{ __('web.title') }}</span>
                {{-- 或 Forest Dynamics Plot Research Platform --}}
            @endif

            {{-- 漢堡按鈕：只在手機顯示 --}}
        </div>
        <div>
            <button @click="open = !open"
                class="lg:hidden p-2 rounded-lg border bg-white shadow-sm focus:outline-none m-2"
                aria-label="Toggle navigation">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
        </div>
        {{-- </a> --}}

        {{-- 桌機版 nav (md 以上顯示) --}}
        <nav class="text-white bg-forest-dark hidden lg:flex p-2 rounded-l-[15px] border border-forest-dark relative text-sm lg:text-base">
            <div class="inline-flex pl-8 space-x-5 my-1">
                {{-- 首頁：單一連結 --}}
                <div class='web-nav'>
                    <a href="{{ route('webindex') }}">
                        {{ __('web.nav_home') }}
                    </a>
                </div>

                {{-- 關於 / 研究緣起：dropdown --}}
                <div>
                    <x-nav.web-dropdown :label="__('web.nav_background')" active="background.*" :routes="[
                        ['label' => __('web.nav_background_motivation'), 'route' => 'background.motivation'],
                        ['label' => __('web.nav_background_team'), 'route' => 'background.team'],
                        ['label' => __('web.nav_background_partners'), 'route' => 'background.partners'],
                        ['label' => __('web.nav_background_taiwanplots'), 'route' => 'background.taiwanplots'],
                    ]" />
                </div>

                {{-- 動態樣區：單一連結或未來也可以做 dropdown --}}
                <div>
                    <x-nav.web-dropdown :label="__('web.nav_plots')" active="plots.*" :routes="[
                        ['label' => __('web.nav_plots_fushan'), 'route' => 'plots.fushan'],
                        ['label' => __('web.nav_plots_nanjenshan'), 'route' => 'plots.nanjenshan'],
                        ['label' => __('web.nav_plots_shoushan'), 'route' => 'plots.shoushan'],
                    ]" />
                </div>

                {{-- 研究主題：也可以做 dropdown --}}
                <div>
                    <x-nav.web-dropdown :label="__('web.nav_subjects')" active="subjects.*" :routes="[
                        ['label' => __('web.nav_subjects_tree'), 'route' => 'subjects.tree'],
                        ['label' => __('web.nav_subjects_seedling'), 'route' => 'subjects.seedling'],
                        ['label' => __('web.nav_subjects_seeds'), 'route' => 'subjects.seeds'],
                        ['label' => __('web.nav_subjects_mortality'), 'route' => 'subjects.mortality'],
                        ['label' => __('web.nav_subjects_functionaltraits'), 'route' => 'subjects.functionaltraits'],
                        ['label' => __('web.nav_subjects_canopy'), 'route' => 'subjects.canopy'],
                        ['label' => __('web.nav_subjects_epiphytes'), 'route' => 'subjects.epiphytes'],
                    ]" />
                </div>
                {{-- 監測植物 --}}
                <div class='web-nav'>
                    <a href="{{ route('front.splist') }}">
                        {{ __('web.nav_plants') }}
                    </a>
                </div>
                {{-- 最新消息 --}}
                <div class='web-nav'>
                    <a href="{{ route('news.index') }}">
                        {{ __('web.nav_news') }}
                    </a>
                </div>
                {{-- 聯絡我們 --}}
                {{-- <div class='web-nav'>
                    <a href="">
                        {{ __('web.nav_contact') }}
                    </a>
                </div> --}}
                {{-- 語言切換 --}}
                <div class='web-nav'>
                    @if (app()->getLocale() === 'zh-TW')
                        <a href="{{ route('locale.switch', ['locale' => 'en']) }}" class="">
                            EN
                        </a>
                    @else
                        <a href="{{ route('locale.switch', ['locale' => 'zh-TW']) }}" class="">
                            中文
                        </a>
                    @endif
                </div>
            </div>
        </nav>

        {{-- 手機版：收合式導覽列 --}}
        <nav x-show="open" x-transition x-cloak
            class="lg:hidden absolute right-2 top-full mt-2 border shadow-lg rounded-lg text-right py-3 px-4 space-y-2 bg-gray-100 text-gray-800 web-nav text-sm ">
            <div><a href="/">{{ __('web.nav_home') }}</a></div>
            <div>{{ __('web.nav_background') }} - {{ __('web.nav_background_motivation') }}</div>
            <div>{{ __('web.nav_plots') }}</div>
            <div>{{ __('web.nav_subjects') }}</div>
            <div><a href="/splist">{{ __('web.nav_plants') }}</a></div>
            <div>{{ __('web.nav_contact') }}</div>

            {{-- 語言切換 --}}
            <div>
                @if ($locale === 'zh-TW')
                    <a href="{{ route('locale.switch', ['locale' => 'en']) }}">EN</a>
                @else
                    <a href="{{ route('locale.switch', ['locale' => 'zh-TW']) }}">中文</a>
                @endif
            </div>
        </nav>

    </div>
</header>
