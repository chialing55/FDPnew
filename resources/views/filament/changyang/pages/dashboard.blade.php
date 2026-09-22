<x-filament-panels::page>
    <div class="fi-changyang-dashboard">
        <div class="fi-changyang-dashboard__heading">
            <div>
                <h2>頁面總覽</h2>
                <p>選擇頁面以編輯前台內容。</p>
            </div>
            <a href="{{ route('changyang.home') }}" target="_blank" rel="noopener" class="fi-changyang-dashboard__site-link">
                查看公開網站
                <x-filament::icon icon="heroicon-o-arrow-top-right-on-square" class="h-4 w-4" />
            </a>
        </div>

        <div class="fi-changyang-dashboard__grid">
            @foreach ($pages as $page)
                <a href="{{ $page['edit_url'] }}" class="fi-changyang-dashboard__card">
                    <span class="fi-changyang-dashboard__icon">
                        <x-filament::icon :icon="$page['icon']" class="h-7 w-7" />
                    </span>
                    <span class="fi-changyang-dashboard__content">
                        <span class="fi-changyang-dashboard__label">{{ $page['label'] }}</span>
                        <span class="fi-changyang-dashboard__title">{{ $page['title'] }}</span>
                    </span>
                    @if (! $page['is_active'])
                        <span class="fi-changyang-dashboard__draft">未公開</span>
                    @endif
                    <span class="fi-changyang-dashboard__arrow" aria-hidden="true">→</span>
                </a>
            @endforeach
        </div>
    </div>
</x-filament-panels::page>
