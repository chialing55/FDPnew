<div class="space-y-5 rounded-md bg-white p-5 shadow-sm sm:p-6">
    <div>

        <p class="text-sm text-gray-600">收錄福山、南仁山與壽山動態樣區的監測植物；分類與名稱依台灣植物名錄。</p>
    </div>

    <div class="space-y-4 rounded-md bg-gray-50 p-4">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <label class="block w-full sm:max-w-md">
                <span class="mb-1 block text-sm font-medium text-gray-700">搜尋物種</span>
                <input type="search" wire:model.live.debounce.250ms="search" placeholder="中文名、學名、科、屬或代碼"
                    class="h-10 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm" />
            </label>
            <div class="flex gap-2" role="group" aria-label="顯示方式">
                <button type="button" wire:click="setDisplayMode('site')" @class([
                    'rounded-md px-3 py-2 text-sm',
                    'bg-forest text-white' => $displayMode === 'site',
                    'border bg-white text-gray-700 hover:bg-gray-100' =>
                        $displayMode !== 'site',
                ])>依樣區分類</button>
                <button type="button" wire:click="setDisplayMode('research')"
                    @class([
                        'rounded-md px-3 py-2 text-sm',
                        'bg-forest text-white' => $displayMode === 'research',
                        'border bg-white text-gray-700 hover:bg-gray-100' =>
                            $displayMode !== 'research',
                    ])>依研究分類</button>
            </div>
        </div>
        <div class="space-y-2">
            <div class="flex flex-wrap items-center gap-2">
                <span class="mr-2 text-sm font-medium text-gray-700">樣區篩選</span>
                <button type="button" wire:click="selectAllSites" @class([
                    'rounded-md border px-3 py-1 text-sm',
                    'border-forest bg-forest text-white' => $selectedSites === [],
                    'border-gray-300 bg-white text-gray-700 hover:bg-gray-100' =>
                        $selectedSites !== [],
                ])>全部</button>
                @foreach (['fushan' => '福山', 'nanjenshan' => '南仁山', 'shoushan' => '壽山'] as $site => $label)
                    <button type="button" wire:click="toggleSite('{{ $site }}')"
                        @class([
                            'rounded-md border px-3 py-1 text-sm',
                            'border-forest bg-forest text-white' => in_array(
                                $site,
                                $selectedSites,
                                true),
                            'border-gray-300 bg-white text-gray-700 hover:bg-gray-100' => !in_array(
                                $site,
                                $selectedSites,
                                true),
                        ])>{{ $label }}</button>
                @endforeach
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <span class="mr-2 text-sm font-medium text-gray-700">研究篩選</span>
                <button type="button" wire:click="selectAllResearches" @class([
                    'rounded-md border px-3 py-1 text-sm',
                    'border-forest bg-forest text-white' => $selectedResearches === [],
                    'border-gray-300 bg-white text-gray-700 hover:bg-gray-100' =>
                        $selectedResearches !== [],
                ])>全部</button>
                @foreach (['tree' => '樹木', 'seedling' => '幼苗', 'seed' => '物候'] as $research => $label)
                    <button type="button" wire:click="toggleResearch('{{ $research }}')"
                        @class([
                            'rounded-md border px-3 py-1 text-sm',
                            'border-forest bg-forest text-white' => in_array(
                                $research,
                                $selectedResearches,
                                true),
                            'border-gray-300 bg-white text-gray-700 hover:bg-gray-100' => !in_array(
                                $research,
                                $selectedResearches,
                                true),
                        ])>{{ $label }}</button>
                @endforeach
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <span class="mr-2 text-sm font-medium text-gray-700">篩選條件</span>
                <button type="button" wire:click="setFilterMatch('intersection')"
                    @class([
                        'rounded-md border px-3 py-1 text-sm',
                        'border-forest bg-forest text-white' => $filterMatch === 'intersection',
                        'border-gray-300 bg-white text-gray-700 hover:bg-gray-100' =>
                            $filterMatch !== 'intersection',
                    ])>交集</button>
                <button type="button" wire:click="setFilterMatch('union')" @class([
                    'rounded-md border px-3 py-1 text-sm',
                    'border-forest bg-forest text-white' => $filterMatch === 'union',
                    'border-gray-300 bg-white text-gray-700 hover:bg-gray-100' =>
                        $filterMatch !== 'union',
                ])>聯集</button>
            </div>
        </div>
    </div>

    <p class="text-sm text-gray-600">共有 {{ count($speciesList) }} 種植物。</p>

    <div class="overflow-x-auto rounded-md border border-gray-200">
        <table class="min-w-full divide-y divide-gray-200 text-left text-sm">
            <thead class="bg-gray-50 text-gray-700">
                <tr>
                    <th scope="col" class="px-3 py-3 font-semibold"><button wire:click="sort('family')"
                            class="border-0 bg-transparent p-0 font-semibold text-inherit hover:text-forest">科
                            {{ $this->sortIndicator('family') }}</button></th>
                    <th scope="col" class="px-3 py-3 font-semibold"><button wire:click="sort('canonical_name')"
                            class="border-0 bg-transparent p-0 font-semibold text-inherit hover:text-forest">學名
                            {{ $this->sortIndicator('canonical_name') }}</button></th>
                    <th scope="col" class="px-3 py-3 font-semibold"><button wire:click="sort('chname')"
                            class="border-0 bg-transparent p-0 font-semibold text-inherit hover:text-forest">中文名
                            {{ $this->sortIndicator('chname') }}</button></th>
                    @if ($displayMode === 'site')
                        @foreach (['fushan' => '福山', 'nanjenshan' => '南仁山', 'shoushan' => '壽山'] as $site => $label)
                            <th scope="col" class="px-3 py-3 text-center font-semibold"><button
                                    wire:click="sort('{{ $site }}')"
                                    class="border-0 bg-transparent p-0 font-semibold text-inherit hover:text-forest">{{ $label }}
                                    {{ $this->sortIndicator($site) }}</button></th>
                        @endforeach
                    @else
                        @foreach (['tree' => '樹木', 'seedling' => '幼苗', 'seed' => '物候'] as $research => $label)
                            <th scope="col" class="px-3 py-3 text-center font-semibold"><button
                                    wire:click="sort('{{ $research }}')"
                                    class="border-0 bg-transparent p-0 font-semibold text-inherit hover:text-forest">{{ $label }}
                                    {{ $this->sortIndicator($research) }}</button></th>
                        @endforeach
                    @endif
                </tr>
            </thead>
            <tbody wire:key="species-results-{{ $filterVersion }}" class="divide-y divide-gray-200 bg-white">
                @forelse ($speciesList as $species)
                    <tr class="cursor-pointer hover:bg-gray-50"
                        onclick="window.location.href='{{ route('front.species.legacy', ['spcode' => $species['code']]) }}'">
                        <td class="px-3 py-3">{{ $species['family'] }}@if (!empty($species['chfamily']))
                                <span class="ml-1 text-gray-500">{{ $species['chfamily'] }}</span>
                            @endif
                        </td>
                        <td class="px-3 py-3">{!! \App\Support\PlantCatalog\ScientificNameFormatter::format($species['canonical_name']) !!}</td>
                        <td class="px-3 py-3">{{ $species['chname'] }}</td>
                        @foreach ($displayMode === 'site' ? ['fushan', 'nanjenshan', 'shoushan'] : ['tree', 'seedling', 'seed'] as $column)
                            <td class="px-3 py-3 text-center">
                                @if ($displayMode === 'site' ? $this->siteCellMatches($species, $column) : $this->researchCellMatches($species, $column))
                                    @if ($displayMode === 'site')
                                        <span class="text-lg font-bold text-forest" aria-label="有監測">✓</span>
                                    @elseif ($column === 'tree')
                                        <img class="mx-auto h-[18px] w-[18px] object-contain"
                                            src="{{ asset('images/icon/tree.png') }}" alt="樹木">
                                    @elseif ($column === 'seed')
                                        <i class="fa-solid fa-apple-whole text-forest" aria-label="物候"></i>
                                    @elseif ($column === 'seedling')
                                        <i class="fa-solid fa-seedling text-forest" aria-label="幼苗"></i>
                                    @endif
                                @endif
                            </td>
                        @endforeach
                    </tr>
                    @empty
                        <tr>
                            <td colspan="6"
                                class="px-3 py-8 text-center text-gray-600">找不到符合條件的植物。</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
