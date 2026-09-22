<div>
    <div style="display: flex; justify-content: space-between; gap: 1rem; margin-bottom: 1rem">
        <p style="margin: 0; color: #75685d; font-size: .875rem">僅顯示在張楊家豪個人網站的學術產出。</p>
        <x-filament::button tag="a" :href="\App\Filament\ChangYang\Resources\ChangYangPublicationResource::getUrl('create')">新增學術產出</x-filament::button>
    </div>
    <div class="fi-changyang-list-table-wrap">
        <table class="fi-changyang-list-table">
            <thead><tr><th>年份</th><th>類型</th><th>作者</th><th>標題</th><th>公開</th><th class="fi-changyang-list-table__actions"><span class="sr-only">操作</span></th></tr></thead>
            <tbody>
                @forelse ($publications as $publication)
                    <tr wire:key="changyang-publication-{{ $publication->id }}" class="fi-changyang-list-table__row" x-on:click="if (! $event.target.closest('a, button, input')) window.location = @js(\App\Filament\ChangYang\Resources\ChangYangPublicationResource::getUrl('edit', ['record' => $publication]))">
                        <td class="fi-changyang-list-table__nowrap">{{ $publication->year }}</td>
                        <td>{{ \App\Models\Web\Publication::typeLabels('zh-TW')[$publication->type] ?? $publication->type }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($publication->abbreviated_authors, 60) }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($publication->title, 100) }}</td>
                        <td><x-filament::icon :icon="$publication->is_active ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle'" :class="$publication->is_active ? 'fi-changyang-list-table__published' : 'fi-changyang-list-table__hidden'" /></td>
                        <td class="fi-changyang-list-table__actions"><a class="fi-changyang-list-table__edit" href="{{ \App\Filament\ChangYang\Resources\ChangYangPublicationResource::getUrl('edit', ['record' => $publication]) }}"><x-filament::icon icon="heroicon-o-pencil-square" class="h-5 w-5" /> 編輯</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6">尚無學術產出</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($publications->hasPages())
        <div class="mt-4">{{ $publications->appends(['tab' => '-publications-tab'])->links() }}</div>
    @endif
</div>
