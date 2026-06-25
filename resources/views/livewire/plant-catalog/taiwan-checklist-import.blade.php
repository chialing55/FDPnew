<div>
    <form wire:submit.prevent="import" style="display: grid; gap: 14px; max-width: 680px;">
        <div style="padding: 12px 14px; border: 1px solid #e4d18a; background: #fff8d7;">
            <div style="font-weight: 800; margin-bottom: 6px;">匯入格式</div>
            <ol style="margin: 0; padding-left: 20px; line-height: 1.7;">
                <li>資料來源為 tai2 管理系統之「台灣名錄下載」。</li>
                <li>檔案大小上限 20 MB。</li>               
                <li>匯入時會先清空 taiwan_checklist，再整批寫入新資料。</li>
            </ol>
        </div>

        <label style="display: grid; gap: 6px; font-weight: 800;" for="taiwan-checklist-csv">
            CSV 檔案
            <input id="taiwan-checklist-csv" type="file" wire:model="csvFile" accept=".csv,text/csv,text/plain">
        </label>

        @error('csvFile')
            <div style="color: #b00020;">{{ $message }}</div>
        @enderror

        <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
            <button type="submit" class="recruitbutton" wire:loading.attr="disabled" wire:target="csvFile,import">
                匯入台灣植物名錄
            </button>
            <span wire:loading wire:target="csvFile">上傳中...</span>
            <span wire:loading wire:target="import">匯入中...</span>
        </div>
    </form>

    @if ($message)
        <div style="margin-top: 16px; padding: 12px 14px; border: 1px solid {{ $messageType === 'success' ? '#9ec7a5' : '#e2a3a3' }}; background: {{ $messageType === 'success' ? '#eef8ef' : '#fff1f1' }}; color: {{ $messageType === 'success' ? '#1f4d2f' : '#9b1c1c' }};">
            {{ $message }}
        </div>
    @endif

    <div class="loading-container" wire:loading.class="visible">
        <div class="loading-spinner"></div>
    </div>
</div>
