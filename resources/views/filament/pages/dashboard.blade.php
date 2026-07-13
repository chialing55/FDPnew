<x-filament-panels::page>
    <section class="cms-welcome">
        <div>
            <p class="cms-eyebrow">森林動態樣區研究成果平台</p>
            <h2>今天想更新什麼內容？</h2>
            <p>從下方快速進入常用功能，或使用左側選單管理完整資料。</p>
        </div>
        <a href="{{ url('/') }}" target="_blank" rel="noopener" class="cms-front-link">開啟前台 <x-heroicon-m-arrow-top-right-on-square /></a>
    </section>

    <div class="cms-section-heading"><h3>快速開始</h3><p>選擇要維護的前台內容。</p></div>

    <div class="cms-quick-grid">
        @php
            $items = [
                ['label' => '網站頁面', 'description' => '編輯一般頁面、導覽資訊與發布狀態', 'icon' => 'heroicon-o-document-text', 'url' => \App\Filament\Resources\PageResource::getUrl('index')],
                ['label' => '頁面內容', 'description' => '維護頁面中的文字、圖片與內容區塊', 'icon' => 'heroicon-o-rectangle-stack', 'url' => \App\Filament\Resources\ContentBlockResource::getUrl('index')],
                ['label' => '研究成果', 'description' => '新增或更新研究成果與公開狀態', 'icon' => 'heroicon-o-chart-bar-square', 'url' => \App\Filament\Resources\ResearchOutputResource::getUrl('index')],
                ['label' => '樣區介紹', 'description' => '管理各研究樣區的基本資料與關聯團隊', 'icon' => 'heroicon-o-map-pin', 'url' => \App\Filament\Resources\SiteResource::getUrl('index')],
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

    <div class="cms-help-card"><x-heroicon-o-light-bulb /><div><strong>編輯建議</strong><p>儲存後可點選「開啟前台」檢查顯示結果；修改前請先確認正在編輯的語系與發布狀態。</p></div></div>
</x-filament-panels::page>
