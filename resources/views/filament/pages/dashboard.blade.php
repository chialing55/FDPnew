<x-filament-panels::page>
    <section class="cms-welcome">
        <div>
            <p class="cms-eyebrow">森林動態樣區研究成果平台</p>
            <h2>今天想更新什麼內容？</h2>
            <p>從下方直接維護前台首頁各區塊，或使用左側選單管理其他網站內容。</p>
        </div>
        <a href="{{ url('/') }}" target="_blank" rel="noopener" class="cms-front-link">開啟前台 <x-heroicon-m-arrow-top-right-on-square /></a>
    </section>

    <div class="cms-section-heading"><h3>快速管理</h3><p>選擇要維護的網站內容。</p></div>

    <div class="cms-quick-grid">
        @php
            $items = [
                ['label' => '最新消息', 'description' => '新增或更新首頁顯示的最新消息', 'icon' => 'heroicon-o-megaphone', 'url' => \App\Filament\Resources\NewsResource::getUrl('index')],
                ['label' => '動態樣區', 'description' => '維護樣區名稱、簡介、圖片與顯示狀態', 'icon' => 'heroicon-o-map-pin', 'url' => \App\Filament\Resources\SiteResource::getUrl('index')],
                ['label' => '學術產出', 'description' => '管理出版品、作者與公開檔案', 'icon' => 'heroicon-o-book-open', 'url' => \App\Filament\Resources\PublicationResource::getUrl('index')],
                ['label' => '研究計畫', 'description' => '新增或更新研究計畫內容', 'icon' => 'heroicon-o-clipboard-document-list', 'url' => \App\Filament\Resources\ProjectResource::getUrl('index')],
                ['label' => '研究主題', 'description' => '維護研究主題及其頁面內容', 'icon' => 'heroicon-o-academic-cap', 'url' => \App\Filament\Resources\SubjectResource::getUrl('index')],
                ['label' => '研究成果', 'description' => '新增或更新研究成果與公開狀態', 'icon' => 'heroicon-o-chart-bar-square', 'url' => \App\Filament\Resources\ResearchOutputResource::getUrl('index')],
            ];
        @endphp
        @foreach ($items as $item)
            <a href="{{ $item['url'] }}" class="cms-quick-card">
                <span class="cms-quick-icon"><x-dynamic-component :component="$item['icon']" /></span>
                <span><strong>{{ $item['label'] }}</strong><small>{{ $item['description'] }}</small></span>
                <x-heroicon-m-chevron-right class="cms-chevron" />
            </a>
        @endforeach
    </div>

    <div class="cms-help-card"><x-heroicon-o-light-bulb /><div><strong>編輯建議</strong><p>儲存後可點選「開啟前台」檢查顯示結果；最新消息與動態樣區需設為公開／顯示，才會出現在首頁。</p></div></div>
</x-filament-panels::page>
