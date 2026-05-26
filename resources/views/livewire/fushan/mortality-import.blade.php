<div>
    <div class='text_box'>
        <h2>資料匯入 (限管理者)</h2>
        <hr>
        <p style='margin: 10px 0; font-weight: 800;'>{{ $importNote }}</p>
        <ol>
            <li>先完成兩次資料輸入。</li>
            <li>完成資料比對與人工確認。</li>
            <li>以 record1 的資料匯入 census_records、census_record_comments、stem_corrections。</li>
        </ol>
        <p style='margin-top: 20px;'>
            <button type="button" wire:click="importRecord1" @disabled(!$canImport)>匯入大表</button>
        </p>
        <div class="loading-container" wire:loading.class="visible">
            <div class="loading-spinner"></div>
        </div>
    </div>
</div>
