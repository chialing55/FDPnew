<div x-data="{ search: '' }">
    <div style="display: flex; justify-content: space-between; gap: 1rem; margin-bottom: 1rem">
        <input type="search" x-model="search" placeholder="搜尋消息內容" aria-label="搜尋消息內容" class="rounded-lg border-gray-300">
        <x-filament::button tag="a" :href="\App\Filament\ChangYang\Resources\NewsResource::getUrl('create')">新增消息</x-filament::button>
    </div>
    <p style="margin: 0 0 .75rem; color: #75685d; font-size: .875rem">預設依年月排序，新增消息會自動排在最上方。拖曳左側圖示即可調整前台顯示順序，放開後會自動儲存；可將任何消息移到最上方置頂。</p>
    <div class="fi-changyang-list-table-wrap">
        <table class="fi-changyang-list-table">
            <thead><tr><th><span class="sr-only">排序</span></th><th>年月</th><th>消息內容</th><th>公開</th><th class="fi-changyang-list-table__actions"><span class="sr-only">操作</span></th></tr></thead>
            <tbody x-sortable x-on:end.stop="$wire.reorderNews($event.target.sortable.toArray())">
                @forelse ($items as $item)
                    @php($text = html_entity_decode(strip_tags($item->content_html), ENT_QUOTES, 'UTF-8'))
                    <tr wire:key="news-{{ $item->id }}" x-sortable-item="{{ $item->id }}" x-show="!search || @js(mb_strtolower($text)).includes(search.toLowerCase())" class="fi-changyang-list-table__row" x-on:click="if (! $event.target.closest('a, button, input')) window.location = @js(\App\Filament\ChangYang\Resources\NewsResource::getUrl('edit', ['record' => $item]))">
                        <td>
                            <button type="button" x-sortable-handle x-show="!search" aria-label="拖曳排序" style="cursor: grab; touch-action: none; color: #75685d">
                                <x-filament::icon icon="heroicon-o-arrows-up-down" class="h-5 w-5" />
                            </button>
                        </td>
                        <td class="fi-changyang-list-table__nowrap">{{ sprintf('%04d-%02d', $item->category_year, $item->category_month) }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($text, 150) }}</td>
                        <td><x-filament::icon :icon="$item->is_active ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle'" :class="$item->is_active ? 'fi-changyang-list-table__published' : 'fi-changyang-list-table__hidden'" /></td>
                        <td class="fi-changyang-list-table__actions"><a class="fi-changyang-list-table__edit" href="{{ \App\Filament\ChangYang\Resources\NewsResource::getUrl('edit', ['record' => $item]) }}"><x-filament::icon icon="heroicon-o-pencil-square" class="h-5 w-5" /> 編輯</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5">尚無消息</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
