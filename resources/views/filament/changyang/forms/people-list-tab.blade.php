<div>
    <p style="margin-bottom: 1rem; color: #655a51">拖曳各列左側箭頭可調整同類型內的人員順序，放開後自動儲存。</p>
    <div class="fi-changyang-people-tab__header">
        <x-filament::button tag="a" :href="\App\Filament\ChangYang\Resources\PersonResource::getUrl('create')" color="primary">
            新增人員
        </x-filament::button>
    </div>

    @php
        $groups = $categories->map(fn ($category) => [
            'id' => $category->id,
            'title' => $category->title,
            'roles' => $category->roles,
        ]);
        if ($unassigned->isNotEmpty()) {
            $groups->push([
                'id' => null,
                'title' => '未分類',
                'roles' => $unassigned->map(fn ($person) => (object) ['id' => 'unassigned-'.$person->id, 'person' => $person]),
            ]);
        }
    @endphp
    @foreach ($groups as $group)
    <div class="fi-changyang-people-tab" style="margin-top: 1.5rem" wire:key="people-category-{{ $group['id'] ?? 'unassigned' }}" x-data="{ search: '' }">
    <div class="fi-changyang-people-tab__filters">
        <h3 style="font-weight: 600">{{ $group['title'] }}</h3>
        <label class="fi-changyang-people-tab__search">
            <span aria-hidden="true">⌕</span>
            <input type="search" x-model="search" placeholder="Search">
        </label>
    </div>

    <div class="fi-changyang-people-tab__table-wrap">
        <table class="fi-changyang-people-tab__table">
            <thead>
                <tr>
                    <th><span class="sr-only">排序</span></th><th>照片</th>
                    <th>姓名</th>
                    <th>目前類型</th>
                    <th>顯示</th>
                    <th class="fi-changyang-people-tab__actions-heading"><span class="sr-only">操作</span></th>
                </tr>
            </thead>
            <tbody @if ($group['id']) x-sortable x-on:end.stop="$wire.reorderPeople({{ $group['id'] }}, $event.target.sortable.toArray())" @endif>
                @foreach ($group['roles'] as $role)
                    @php
                        $person = $role->person;
                        $categories = collect([$group['title']]);
                        $searchText = \Illuminate\Support\Str::lower($person->name . ' ' . $categories->join(' '));
                    @endphp
                    <tr wire:key="people-role-{{ $role->id }}" x-sortable-item="{{ $role->id }}" x-show="! search || @js($searchText).includes(search.toLowerCase())" class="fi-changyang-list-table__row" x-on:click="if (! $event.target.closest('a, button, input')) window.location = @js(\App\Filament\ChangYang\Resources\PersonResource::getUrl('edit', ['record' => $person]))">
                        <td>
                            @if ($group['id'])
                            <button type="button" x-sortable-handle x-show="! search" aria-label="拖曳排序" style="cursor: grab; touch-action: none">
                                <x-filament::icon icon="heroicon-o-arrows-up-down" class="fi-changyang-people-tab__sort" />
                            </button>
                            @endif
                        </td>
                        <td>
                            @if ($person->image_path)
                                <img
                                    class="fi-changyang-person-thumb"
                                    loading="lazy"
                                    src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($person->image_path) }}"
                                    alt="{{ $person->image_alt ?: $person->name }}"
                                >
                            @else
                                <span class="fi-changyang-person-thumb fi-changyang-person-thumb--empty" aria-hidden="true">—</span>
                            @endif
                        </td>
                        <td class="fi-changyang-people-tab__name">{{ $person->name }}</td>
                        <td>
                            <div class="fi-changyang-people-tab__badges">
                                @forelse ($categories as $category)
                                    <span class="fi-changyang-people-tab__badge">{{ $category }}</span>
                                @empty
                                    <span class="fi-changyang-people-tab__muted">未設定</span>
                                @endforelse
                            </div>
                        </td>
                        <td>
                            @if ($person->is_active)
                                <x-filament::icon icon="heroicon-o-check-circle" class="fi-changyang-list-table__published" title="顯示" />
                            @else
                                <x-filament::icon icon="heroicon-o-x-circle" class="fi-changyang-list-table__hidden" title="隱藏" />
                            @endif
                        </td>
                        <td class="fi-changyang-people-tab__actions">
                            <a class="fi-changyang-list-table__edit" href="{{ \App\Filament\ChangYang\Resources\PersonResource::getUrl('edit', ['record' => $person]) }}"><x-filament::icon icon="heroicon-o-pencil-square" class="h-5 w-5" /> 編輯</a>
                        </td>
                    </tr>
                @endforeach
                @if ($group['roles']->isEmpty())
                    <tr><td colspan="6" style="color: #9b948d">尚無人員</td></tr>
                @endif
            </tbody>
        </table>
    </div>
    </div>
    @endforeach
</div>

<style>
    .fi-changyang-people-tab { overflow: hidden; border: 1px solid #e7e2dc; border-radius: 1rem; background: #fff; }
    .fi-changyang-people-tab__header { display: flex; justify-content: flex-end; padding: 1.25rem 1.85rem; border-bottom: 1px solid #e7e2dc; }
    .fi-changyang-people-tab__filters { display: flex; align-items: center; justify-content: space-between; min-height: 4.65rem; padding: 0 1.85rem; border-bottom: 1px solid #e7e2dc; }
    .fi-changyang-people-tab__sort { width: 1.45rem; height: 1.45rem; color: #aaa6af; }
    .fi-changyang-people-tab__search { display: flex; align-items: center; width: min(100%, 19rem); gap: .7rem; padding: .55rem .85rem; border: 1px solid #ded9d4; border-radius: .75rem; color: #9b98a2; font-size: 1.45rem; }
    .fi-changyang-people-tab__search input { width: 100%; border: 0; outline: 0; background: transparent; color: inherit; font-size: .95rem; }
    .fi-changyang-people-tab__table-wrap { overflow-x: auto; }
    .fi-changyang-people-tab__table { width: 100%; min-width: 48rem; border-collapse: collapse; text-align: left; }
    .fi-changyang-people-tab__table th { padding: 1rem 1.85rem; color: #231f20; font-size: .95rem; font-weight: 700; }
    .fi-changyang-people-tab__table td { padding: 1.15rem 1.85rem; border-top: 1px solid #e7e2dc; vertical-align: middle; }
    .fi-changyang-people-tab__name { min-width: 19rem; font-weight: 500; }
    .fi-changyang-person-thumb { display: block; width: 3.1rem; height: 3.1rem; object-fit: cover; background: #eee9e4; }
    .fi-changyang-person-thumb--empty { display: grid; place-items: center; color: #a9a29a; }
    .fi-changyang-people-tab__badges { display: flex; flex-wrap: wrap; gap: .35rem; }
    .fi-changyang-people-tab__badge { display: inline-flex; padding: .32rem .55rem; border: 1px solid #e5e0dc; border-radius: .45rem; background: #faf9f8; color: #655a51; font-size: .87rem; white-space: nowrap; }
    .fi-changyang-people-tab__muted { color: #9b948d; }
    .fi-changyang-people-tab__visible, .fi-changyang-people-tab__hidden { display: grid; place-items: center; width: 1.55rem; height: 1.55rem; border-radius: 999px; font-weight: 700; }
    .fi-changyang-people-tab__visible { border: 2px solid #20b965; color: #20b965; }
    .fi-changyang-people-tab__hidden { border: 1px solid #cfc8c1; color: #a9a29a; }
    .fi-changyang-people-tab__actions-heading, .fi-changyang-people-tab__actions { width: 7rem; text-align: right; }
    .fi-changyang-people-tab__actions a { color: #52773a; font-weight: 700; text-decoration: none; white-space: nowrap; }
    .fi-changyang-people-tab__actions a:hover { color: #365625; text-decoration: none; }
    @media (max-width: 640px) { .fi-changyang-people-tab__header, .fi-changyang-people-tab__filters { padding-right: 1rem; padding-left: 1rem; } .fi-changyang-people-tab__table th, .fi-changyang-people-tab__table td { padding-right: 1rem; padding-left: 1rem; } }
</style>
