{{-- 資料展示網頁用 --}}
<header class="sticky top-0 z-30 border-b bg-white">
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
    <div class="relative flex w-full items-center justify-between" x-data="{ open: false }"
        @keydown.escape.window="open = false">
        {{-- <a href='/web/index'> --}}
        <div class="flex items-center justify-between p-2 text-black md:p-0">
            @if ($locale === 'zh-TW')
                <span class="pl-1 text-base font-bold sm:pl-2 sm:text-lg">台灣</span>
                <img src="{{ asset('/images/web/森林動態樣區.png') }}" class="mx-1 h-8 sm:h-12" alt="Fushan Forest Plot" />
                <span class="text-base font-bold sm:text-lg">研究成果平台</span>
            @else
                <span class="pl-1 text-base font-bold sm:pl-2 sm:text-lg">{{ __('web.title') }}</span>
                {{-- 或 Forest Dynamics Plot Research Platform --}}
            @endif

            {{-- 漢堡按鈕：只在手機顯示 --}}
        </div>
        <div>
            <button @click="open = !open" :aria-expanded="open.toString()" aria-controls="mobile-web-navigation"
                class="m-2 rounded-lg border bg-white p-2 shadow-sm focus:outline-none lg:hidden"
                aria-label="Toggle navigation">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
        </div>
        {{-- </a> --}}

        {{-- 桌機版 nav (md 以上顯示) --}}
        <nav
            class="relative hidden rounded-l-[15px] border border-forest-dark bg-forest-dark p-2 text-sm text-white lg:flex lg:text-base">
            <div class="my-1 inline-flex space-x-5 pl-8">
                {{-- 首頁：單一連結 --}}
                <div class='web-nav'>
                    <a href="{{ route('webindex') }}">
                        {{ __('web.nav_home') }}
                    </a>
                </div>



                {{-- 動態樣區：單一連結或未來也可以做 dropdown --}}
                <div>
                    <x-nav.web-dropdown :label="__('web.nav_sites')" :pages="$navSitePages" />
                </div>

                {{-- 研究主題：也可以做 dropdown --}}
                <div>
                    <x-nav.web-dropdown :label="__('web.nav_subjects')" :pages="$navSubjectPages" />

                </div>
                <div class="web-nav">
                    <a href="{{ url('/publications') }}">{{ __('web.nav_publications') }}</a>
                </div>
                <div class="web-nav">
                    <a href="{{ route('front.splist') }}">{{ __('web.nav_plants') }}</a>
                </div>
                {{-- 關於 / 研究緣起：dropdown --}}
                <div>
                    <x-nav.web-dropdown :label="__('web.nav_about')" :pages="$navAboutPages" :shift="true" />
                </div>
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
        <nav id="mobile-web-navigation" x-show="open" x-transition x-cloak @click.outside="open = false"
            class="web-nav absolute inset-x-2 top-full mt-2 max-h-[calc(100vh-5rem)] space-y-1 overflow-y-auto rounded-lg border bg-white p-2 text-left text-sm text-gray-800 shadow-lg lg:hidden sm:left-auto sm:w-96">
            {{-- 首頁 --}}

            <div><a href="/">{{ __('web.nav_home') }}</a></div>

            @foreach ($navSitePages as $page)
                <div><a href="{{ url($page->slug) }}">
                        {{ __('web.nav_' . $page->nav_group . '') }} - {{ $page->title }}
                    </a></div>
            @endforeach

            @foreach ($navSubjectPages as $page)
                <div><a href="{{ url($page->slug) }}">
                        {{ __('web.nav_' . $page->nav_group . '') }} - {{ $page->title }}
                    </a></div>
            @endforeach

            <div><a href="{{ url('/publications') }}">{{ __('web.nav_publications') }}</a></div>
            <div><a href="{{ route('front.splist') }}">{{ __('web.nav_plants') }}</a></div>
            @foreach ($navAboutPages as $page)
                <div><a href="{{ url($page->slug) }}">
                        {{ __('web.nav_' . $page->nav_group . '') }} - {{ $page->title }}
                    </a></div>
            @endforeach
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
