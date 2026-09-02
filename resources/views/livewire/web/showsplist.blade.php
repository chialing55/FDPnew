<div class="space-y-5 rounded-md bg-white p-5 shadow-sm sm:p-6">
    <div>
        <p class="text-sm text-gray-600">{{ __('web.plants_intro') }}</p>
    </div>

    <div class="space-y-4 rounded-md bg-gray-50 p-4">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <label class="block w-full sm:max-w-md">
                <span class="mb-1 block text-sm font-medium text-gray-700">{{ __('web.plants_search') }}</span>
                <input type="search" wire:model.live.debounce.250ms="search" placeholder="{{ __('web.plants_search_placeholder') }}"
                    class="h-10 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm" />
            </label>
            <div class="flex gap-2" role="group" aria-label="{{ __('web.plants_display_mode') }}">
                <button type="button" wire:click="setDisplayMode('site')" @class([
                    'rounded-md px-3 py-2 text-sm',
                    'bg-forest text-white' => $displayMode === 'site',
                    'border bg-white text-gray-700 hover:bg-gray-100' =>
                        $displayMode !== 'site',
                ])>{{ __('web.plants_by_site') }}</button>
                <button type="button" wire:click="setDisplayMode('research')"
                    @class([
                        'rounded-md px-3 py-2 text-sm',
                        'bg-forest text-white' => $displayMode === 'research',
                        'border bg-white text-gray-700 hover:bg-gray-100' =>
                            $displayMode !== 'research',
                    ])>{{ __('web.plants_by_research') }}</button>
            </div>
        </div>
        <div class="space-y-2">
            <div class="flex flex-wrap items-center gap-2">
                <span class="mr-2 text-sm font-medium text-gray-700">{{ __('web.plants_site_filter') }}</span>
                <button type="button" wire:click="selectAllSites" @class([
                    'rounded-md border px-3 py-1 text-sm',
                    'border-forest bg-forest text-white' => $selectedSites === [],
                    'border-gray-300 bg-white text-gray-700 hover:bg-gray-100' =>
                        $selectedSites !== [],
                ])>{{ __('web.all') }}</button>
                @foreach (['fushan', 'nanjenshan', 'shoushan'] as $site)
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
                        ])>{{ __('web.site_' . $site) }}</button>
                @endforeach
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <span class="mr-2 text-sm font-medium text-gray-700">{{ __('web.plants_research_filter') }}</span>
                <button type="button" wire:click="selectAllResearches" @class([
                    'rounded-md border px-3 py-1 text-sm',
                    'border-forest bg-forest text-white' => $selectedResearches === [],
                    'border-gray-300 bg-white text-gray-700 hover:bg-gray-100' =>
                        $selectedResearches !== [],
                ])>{{ __('web.all') }}</button>
                @foreach (['tree', 'seedling', 'seed'] as $research)
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
                        ])>{{ __('web.research_' . $research) }}</button>
                @endforeach
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <span class="mr-2 text-sm font-medium text-gray-700">{{ __('web.plants_filter_match') }}</span>
                <button type="button" wire:click="setFilterMatch('intersection')"
                    @class([
                        'rounded-md border px-3 py-1 text-sm',
                        'border-forest bg-forest text-white' => $filterMatch === 'intersection',
                        'border-gray-300 bg-white text-gray-700 hover:bg-gray-100' =>
                            $filterMatch !== 'intersection',
                    ])>{{ __('web.intersection') }}</button>
                <button type="button" wire:click="setFilterMatch('union')" @class([
                    'rounded-md border px-3 py-1 text-sm',
                    'border-forest bg-forest text-white' => $filterMatch === 'union',
                    'border-gray-300 bg-white text-gray-700 hover:bg-gray-100' =>
                        $filterMatch !== 'union',
                ])>{{ __('web.union') }}</button>
            </div>
        </div>
    </div>

    <p class="text-sm text-gray-600">{{ trans_choice('web.plants_count', count($speciesList), ['count' => count($speciesList)]) }}</p>

    {{-- 手機版：每個物種以卡片呈現，樣區及研究改用標籤。 --}}
    <div wire:key="species-cards-{{ $filterVersion }}" class="grid gap-3 md:hidden">
        @forelse ($speciesList as $species)
            <a href="{{ route('front.species', ['spcode' => $species['code']]) }}"
                class="block rounded-lg border border-gray-200 bg-white p-4 text-gray-800 no-underline shadow-sm transition hover:border-forest hover:bg-gray-50 hover:no-underline">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <h2 class="m-0 text-lg font-bold leading-snug text-forest">
                            {{ $species['chname'] ?: __('web.plants_unnamed') }}
                        </h2>
                        <p class="mt-1 break-words text-sm leading-relaxed text-gray-700">
                            {!! \App\Support\PlantCatalog\ScientificNameFormatter::format($species['canonical_name']) !!}
                        </p>
                    </div>
                    <span class="shrink-0 text-lg text-gray-400" aria-hidden="true">›</span>
                </div>

                <p class="mt-2 text-xs text-gray-500">
                    {{ $species['family'] }}@if (!empty($species['chfamily']))
                        · {{ $species['chfamily'] }}
                    @endif
                </p>

                <div class="mt-3 space-y-2">
                    <div class="flex flex-wrap items-center gap-1.5">
                        <span class="mr-1 text-xs font-semibold text-gray-500">{{ __('web.select_site') }}</span>
                        @foreach (['fushan', 'nanjenshan', 'shoushan'] as $siteIndex => $site)
                            @if ($this->siteCellMatches($species, $site))
                                <span class="{{ $this->tagClasses() }}" style="{{ $this->tagStyle('site', $siteIndex + 1) }}">
                                    {{ __('web.site_' . $site) }}
                                </span>
                            @endif
                        @endforeach
                    </div>

                    <div class="flex flex-wrap items-center gap-1.5">
                        <span class="mr-1 text-xs font-semibold text-gray-500">{{ __('web.plants_research') }}</span>
                        @foreach (['tree', 'seedling', 'seed'] as $researchIndex => $research)
                            @if ($this->researchCellMatches($species, $research))
                                <span class="{{ $this->tagClasses() }}" style="{{ $this->tagStyle('subject', $researchIndex + 1) }}">
                                    {{ __('web.research_' . $research) }}
                                </span>
                            @endif
                        @endforeach
                    </div>
                </div>
            </a>
        @empty
            <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-8 text-center text-sm text-gray-600">
                {{ __('web.plants_empty') }}
            </div>
        @endforelse
    </div>

    {{-- 平板橫向與桌機版維持可排序表格。 --}}
    <div class="hidden overflow-x-auto rounded-md border border-gray-200 md:block">
        <table class="min-w-full divide-y divide-gray-200 text-left text-sm">
            <thead class="bg-gray-50 text-gray-700">
                <tr>
                    <th scope="col" class="px-3 py-3 font-semibold"><span role="button" tabindex="0"
                            wire:click="sort('family')" wire:keydown.enter="sort('family')"
                            class="cursor-pointer select-none hover:text-forest">{{ __('web.family') }}
                            {{ $this->sortIndicator('family') }}</span></th>
                    <th scope="col" class="px-3 py-3 font-semibold"><span role="button" tabindex="0"
                            wire:click="sort('canonical_name')" wire:keydown.enter="sort('canonical_name')"
                            class="cursor-pointer select-none hover:text-forest">{{ __('web.scientific_name') }}
                            {{ $this->sortIndicator('canonical_name') }}</span></th>
                    <th scope="col" class="px-3 py-3 font-semibold"><span role="button" tabindex="0"
                            wire:click="sort('chname')" wire:keydown.enter="sort('chname')"
                            class="cursor-pointer select-none hover:text-forest">{{ __('web.chinese_name') }}
                            {{ $this->sortIndicator('chname') }}</span></th>
                    @if ($displayMode === 'site')
                        @foreach (['fushan', 'nanjenshan', 'shoushan'] as $site)
                            <th scope="col" class="px-3 py-3 text-center font-semibold"><span role="button" tabindex="0"
                                    wire:click="sort('{{ $site }}')" wire:keydown.enter="sort('{{ $site }}')"
                                    class="cursor-pointer select-none hover:text-forest">{{ __('web.site_' . $site) }}
                                    {{ $this->sortIndicator($site) }}</span></th>
                        @endforeach
                    @else
                        @foreach (['tree', 'seedling', 'seed'] as $research)
                            <th scope="col" class="px-3 py-3 text-center font-semibold"><span role="button" tabindex="0"
                                    wire:click="sort('{{ $research }}')" wire:keydown.enter="sort('{{ $research }}')"
                                    class="cursor-pointer select-none hover:text-forest">{{ __('web.research_' . $research) }}
                                    {{ $this->sortIndicator($research) }}</span></th>
                        @endforeach
                    @endif
                </tr>
            </thead>
            <tbody wire:key="species-results-{{ $filterVersion }}" class="divide-y divide-gray-200 bg-white">
                @forelse ($speciesList as $species)
                    <tr class="cursor-pointer hover:bg-gray-50"
                        onclick="window.location.href='{{ route('front.species', ['spcode' => $species['code']]) }}'">
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
                                        <span class="text-lg font-bold text-forest" aria-label="{{ __('web.monitored') }}">✓</span>
                                    @elseif ($column === 'tree')
                                        <img class="mx-auto h-[18px] w-[18px] object-contain"
                                            src="{{ asset('images/icon/tree.png') }}" alt="{{ __('web.research_tree') }}">
                                    @elseif ($column === 'seed')
                                        <i class="fa-solid fa-apple-whole text-forest" aria-label="{{ __('web.research_seed') }}"></i>
                                    @elseif ($column === 'seedling')
                                        <i class="fa-solid fa-seedling text-forest" aria-label="{{ __('web.research_seedling') }}"></i>
                                    @endif
                                @endif
                            </td>
                        @endforeach
                    </tr>
                    @empty
                        <tr>
                            <td colspan="6"
                                class="px-3 py-8 text-center text-gray-600">{{ __('web.plants_empty') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
