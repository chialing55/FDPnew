<div class="pages" style="margin-bottom: 14px; display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
    <div class="totalnum">每頁 {{ $perPage }} 筆</div>
    <div class="pagenote">第 {{ $page }} / {{ $totalPages }} 頁</div>
    <button type="button" class="prev" wire:click="previousPage" @if($page <= 1) disabled @endif>上一頁</button>
    <button type="button" class="next" wire:click="nextPage" @if($page >= $totalPages) disabled @endif>下一頁</button>
    <span>跳至</span>
    <select class="fs100" style="width: 72px;" wire:change="goToPage($event.target.value)">
        @for($i = 1; $i <= $totalPages; $i++)
            <option value="{{ $i }}" @if($i === $page) selected @endif>{{ $i }}</option>
        @endfor
    </select>
</div>
