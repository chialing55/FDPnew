@props([
    'records',
    'margin' => 'margin-top:18px;',
])

@if ($records->hasPages())
    <div style="{{ $margin }} display:flex; justify-content:center; align-items:center; gap:12px; flex-wrap:wrap;">
        @if ($records->onFirstPage())
            <span style="padding:8px 14px; border:1px solid #d1d5db; border-radius:6px; background:#f8fafc; color:#94a3b8;">上一頁</span>
        @else
            <a href="{{ $records->previousPageUrl() }}"
                style="padding:8px 14px; border:1px solid rgba(0,0,0,.15); border-radius:6px; background:#fff; color:#2f3e3b; text-decoration:none;">
                上一頁
            </a>
        @endif

        <span style="color:#475569;">
            第 {{ $records->currentPage() }} / {{ $records->lastPage() }} 頁
        </span>

        @if ($records->hasMorePages())
            <a href="{{ $records->nextPageUrl() }}"
                style="padding:8px 14px; border:1px solid rgba(0,0,0,.15); border-radius:6px; background:#fff; color:#2f3e3b; text-decoration:none;">
                下一頁
            </a>
        @else
            <span style="padding:8px 14px; border:1px solid #d1d5db; border-radius:6px; background:#f8fafc; color:#94a3b8;">下一頁</span>
        @endif
    </div>
@endif
