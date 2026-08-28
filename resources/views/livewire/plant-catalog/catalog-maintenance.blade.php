<div style="display:grid; gap:16px; max-width:850px;">
    <section style="">

        <p>依每木、種子、小苗及死亡率正式資料，先檢查並新增可安全連到台灣名錄的 <code>site_species</code>，再重新建立 <code>species_research_links</code>。未知代碼（UNK*）不會寫入共用名錄或研究連結。</p>

        <p>待新增正式物種：{{ count($audit['missing_site_species'] ?? []) }}；無法自動連結：{{ count($audit['unresolved_site_species'] ?? []) }}</p>


            <button type="button" class="recruitbutton" wire:click="syncFushanSpecies" wire:loading.attr="disabled" onclick="return confirm('確定依目前正式調查資料重新建立福山物種研究連結？')">
                整理福山調查物種
            </button>
            <span wire:loading wire:target="syncFushanSpecies">整理中...</span>
    </section>

    @if ($message)
        <div style="padding:12px 14px; border:1px solid {{ $messageType === 'success' ? '#9ec7a5' : '#e2a3a3' }}; background:{{ $messageType === 'success' ? '#eef8ef' : '#fff1f1' }}; color:{{ $messageType === 'success' ? '#1f4d2f' : '#9b1c1c' }};">
            {{ $message }}
        </div>
    @endif

    @if ($syncResult)
        <table class="tablesorter" style="min-width:620px;">
            <thead><tr><th>整理前</th><th>整理後</th><th>每木</th><th>種子</th><th>小苗</th><th>死亡率</th></tr></thead>
            <tbody><tr>
                <td>{{ $syncResult['before'] }}</td><td>{{ $syncResult['after'] }}（新增名錄 {{ $syncResult['site_species_added'] ?? 0 }}）</td>
                <td>{{ $syncResult['counts']['tree'] ?? 0 }}</td><td>{{ $syncResult['counts']['seed'] ?? 0 }}</td>
                <td>{{ $syncResult['counts']['seedling'] ?? 0 }}</td><td>{{ $syncResult['counts']['mortality'] ?? 0 }}</td>
            </tr></tbody>
        </table>
    @endif

    @if (!empty($audit['missing_site_species']))
        <section>
            <h3>可自動新增的樣區物種</h3>
            <table class="tablesorter" style="min-width:620px;">
                <thead><tr><th>spcode</th><th>中文名</th><th>台灣名錄代碼</th><th>調查</th><th>來源</th></tr></thead>
                <tbody>
                @foreach ($audit['missing_site_species'] as $species)
                    <tr><td>{{ $species['spcode'] }}</td><td>{{ $species['csp'] }}</td><td>{{ $species['code'] }}</td><td>{{ implode(', ', $species['researches']) }}</td><td>{{ $species['source'] }}</td></tr>
                @endforeach
                </tbody>
            </table>
        </section>
    @endif

    @if (!empty($audit['unresolved_site_species']))
        <section>
            <h3>需要人工整理的樣區物種</h3>
            <table class="tablesorter" style="min-width:620px;">
                <thead><tr><th>spcode</th><th>中文名</th><th>調查</th><th>原因</th><th>來源</th></tr></thead>
                <tbody>
                @foreach ($audit['unresolved_site_species'] as $species)
                    <tr><td>{{ $species['spcode'] }}</td><td>{{ $species['csp'] }}</td><td>{{ implode(', ', $species['researches']) }}</td><td>{{ $species['reason'] }}</td><td>{{ $species['source'] }}</td></tr>
                @endforeach
                </tbody>
            </table>
        </section>
    @endif

    <div class="loading-container" wire:loading.class="visible"><div class="loading-spinner"></div></div>
</div>
