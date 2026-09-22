<div class="fi-cms-content-list" x-data="{ search: '' }">
    @php
        $isPublication = $type === 'publications';
        $resource = $isPublication
            ? \App\Filament\Resources\PublicationResource::class
            : \App\Filament\Resources\ProjectResource::class;
        $label = $isPublication ? '學術產出' : '研究計畫';
        $pageUrl = \App\Filament\Support\CmsListPageNavigation::tabUrl($type);
    @endphp
    <div class="fi-cms-content-list__header">
        <p>{{ $isPublication ? '此處管理全站學術產出；Zotero、CSV 與引用格式設定在頁面右上方。' : '此處管理全站研究計畫。' }}</p>
        <div class="fi-cms-content-list__buttons">
            <x-filament::button tag="a" :href="$resource::getUrl('create')">新增{{ $label }}</x-filament::button>
        </div>
    </div>
    <div class="fi-cms-content-list__search">
        <label for="cms-{{ $type }}-search">搜尋本頁{{ $label }}</label>
        <input id="cms-{{ $type }}-search" type="search" x-model="search" placeholder="搜尋標題{{ $isPublication ? '或作者' : '' }}">
        <div class="fi-cms-content-list__filters">
            <a href="{{ $pageUrl }}" @class(['fi-cms-content-list__filter', 'is-active' => ! $onlyMissing])>全部</a>
            <a href="{{ $pageUrl . '&relation_filter=missing' }}" @class(['fi-cms-content-list__filter', 'is-active' => $onlyMissing])>未連結樣區或研究主題（{{ $missingCount }}）</a>
        </div>
    </div>
    <div class="fi-cms-content-list__table-wrap">
        <table class="fi-cms-content-list__table">
            <thead><tr>
                @if ($isPublication)
                    <th>年份</th><th>類型</th><th>作者</th><th>標題</th>
                @else
                    <th>計畫名稱</th><th>計畫代碼</th><th>開始日期</th>
                @endif
                <th>關聯狀態</th><th>公開</th><th class="fi-cms-content-list__actions"><span class="sr-only">操作</span></th>
            </tr></thead>
            <tbody>
                @forelse ($items as $item)
                    @php
                        $searchText = $isPublication
                            ? \Illuminate\Support\Str::lower(($item->title ?? '') . ' ' . ($item->authors ?? ''))
                            : \Illuminate\Support\Str::lower(($item->title_zh_tw ?? '') . ' ' . ($item->title_en ?? '') . ' ' . ($item->code ?? ''));
                        $editUrl = $resource::getUrl('edit', ['record' => $item]);
                    @endphp
                    <tr wire:key="cms-{{ $type }}-{{ $item->id }}" x-show="! search || @js($searchText).includes(search.toLowerCase())"
                        x-on:click="if (! $event.target.closest('a, button, input')) window.location = @js($editUrl)">
                        @if ($isPublication)
                            <td>{{ $item->year }}</td>
                            <td>{{ \App\Models\Web\Publication::typeLabels('zh-TW')[$item->type] ?? $item->type }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($item->abbreviated_authors, 60) }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($item->title, 100) }}</td>
                        @else
                            <td>{{ $item->title_zh_tw ?: $item->title_en }}</td>
                            <td>{{ $item->code }}</td>
                            <td>{{ $item->start_date ? \Illuminate\Support\Carbon::parse($item->start_date)->format('Y-m-d') : '' }}</td>
                        @endif
                        <td class="fi-cms-content-list__relation">
                            @if ($item->sites_count === 0 && (! $isPublication || $item->site_review_status !== 'not_related'))
                                <span>缺樣區</span>
                            @endif
                            @if ($item->subjects_count === 0)
                                <span>缺研究主題</span>
                            @endif
                            @if ($item->sites_count > 0 && $item->subjects_count > 0)
                                <span class="is-complete">已連結</span>
                            @elseif ($isPublication && $item->site_review_status === 'not_related' && $item->subjects_count > 0)
                                <span class="is-complete">樣區已確認無關</span>
                            @endif
                        </td>
                        <td><x-filament::icon :icon="$item->is_active ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle'" :class="$item->is_active ? 'fi-cms-content-list__published' : 'fi-cms-content-list__hidden'" /></td>
                        <td class="fi-cms-content-list__actions"><a href="{{ $editUrl }}"><x-filament::icon icon="heroicon-o-pencil-square" class="h-5 w-5" /> 編輯</a></td>
                    </tr>
                @empty
                    <tr><td colspan="{{ $isPublication ? 7 : 6 }}">{{ $onlyMissing ? '沒有未連結樣區或研究主題的' : '尚無' }}{{ $label }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($items->hasPages())
        <div class="fi-cms-content-list__pagination">{{ $items->appends(array_filter(['tab' => $isPublication ? '-publications-tab' : '-projects-tab', 'relation_filter' => $onlyMissing ? 'missing' : null]))->links() }}</div>
    @endif
    <style>
        .fi-cms-content-list { border:1px solid #e5e7eb; border-radius:.75rem; background:white; overflow:hidden; }
        .fi-cms-content-list__header { display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem; padding:1rem; }
        .fi-cms-content-list__header p { margin:0; color:#6b7280; }
        .fi-cms-content-list__buttons { display:flex; flex-wrap:wrap; gap:.5rem; }
        .fi-cms-content-list__search { display:flex; align-items:center; flex-wrap:wrap; gap:.75rem; padding:.75rem 1rem; border-top:1px solid #e5e7eb; }
        .fi-cms-content-list__search input { max-width:22rem; width:100%; }
        .fi-cms-content-list__filters { display:flex; flex-wrap:wrap; gap:.4rem; margin-left:auto; }
        .fi-cms-content-list__filter { padding:.4rem .7rem; border:1px solid #d1d5db; border-radius:.5rem; color:#4b5563; text-decoration:none; font-size:.875rem; }
        .fi-cms-content-list__filter.is-active { border-color:#52773a; background:#eef5e8; color:#365625; font-weight:700; }
        .fi-cms-content-list__table-wrap { overflow-x:auto; }
        .fi-cms-content-list__table { width:100%; min-width:45rem; border-collapse:collapse; text-align:left; }
        .fi-cms-content-list__table th, .fi-cms-content-list__table td { padding:.9rem 1rem; border-top:1px solid #e5e7eb; vertical-align:middle; }
        .fi-cms-content-list__table th { font-weight:700; white-space:nowrap; }
        .fi-cms-content-list__table tbody tr { cursor:pointer; }
        .fi-cms-content-list__table tbody tr:hover { background:#f8faf7; }
        .fi-cms-content-list__published, .fi-cms-content-list__hidden { width:1.4rem; height:1.4rem; }
        .fi-cms-content-list__relation { white-space:nowrap; }
        .fi-cms-content-list__relation span { display:inline-block; margin:.1rem; padding:.15rem .4rem; border-radius:.4rem; background:#fff3e1; color:#9a5b13; font-size:.75rem; }
        .fi-cms-content-list__relation span.is-complete { background:#e9f6ed; color:#287344; }
        .fi-cms-content-list__published { color:#20b965; }
        .fi-cms-content-list__hidden { color:#9ca3af; }
        .fi-cms-content-list__actions { text-align:right; white-space:nowrap; }
        .fi-cms-content-list__actions a { display:inline-flex; align-items:center; gap:.3rem; color:#52773a; font-weight:700; text-decoration:none; }
        .fi-cms-content-list__actions a:hover { color:#365625; }
        .fi-cms-content-list__pagination { padding:1rem; }
    </style>
</div>
