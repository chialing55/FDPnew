<div wire:poll.5s>
    @php($status = \Illuminate\Support\Facades\Cache::get('zotero-sync-status'))
    <p>最近一次手動同步：{{ $status['message'] ?? '尚無紀錄' }}</p>
    @if ($status)<p class="text-sm text-gray-500">{{ $status['at'] }}</p>@endif
</div>
