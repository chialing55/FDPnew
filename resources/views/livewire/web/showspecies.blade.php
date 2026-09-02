@php
    $chartMethods = [];

    if (($researchesBySite['nanjenshan']['seedling'] ?? 0) != 0) {
        $chartMethods[] = 'fig7data';
    }

    if (($researchesBySite['shoushan']['tree'] ?? 0) != 0) {
        $chartMethods = array_merge($chartMethods, ['fig8data', 'fig9data', 'fig10data']);
    }

    if ($countInd > 0) {
        $chartMethods = array_merge($chartMethods, ['fig2data', 'fig3data']);
    }

    if ($countFlower > 0) {
        $chartMethods[] = 'fig4data';
    }

    if ($countSeeds > 0) {
        $chartMethods[] = 'fig5data';
    }

    if ($countSeedlings > 0) {
        $chartMethods[] = 'fig6data';
    }

    if ($countInd > 0) {
        $chartMethods[] = 'fig1data';
    }

@endphp

<div class="space-y-4" data-species-chart-root data-chart-methods='@json($chartMethods)'>
    <style>
        [data-species-chart-root],
        [data-species-chart-root] *,
        [data-species-chart-root] *::before,
        [data-species-chart-root] *::after {
            box-sizing: border-box;
        }

        .species-research-flags {
            display: flex;
            flex-direction: column;
            gap: 8px;
            align-items: flex-end;
            margin-top: 4px;
        }

        .species-research-flag-row {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            align-items: center;
            justify-content: flex-end;
        }

        .species-research-subjects {
            display: contents;
        }

        .spheader h2 {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 0.35rem;
        }

        .species-description-box {
            width: 100%;
            box-sizing: border-box;
            background-color: #fffbeb;
        }

        .species-description-notes {
            margin-left: 2rem;
            line-height: 1.8;
        }

        .species-description-list {
            display: grid;
            row-gap: 0.6rem;
        }

        .species-description-row {
            display: grid;
            grid-template-columns: 3rem minmax(0, 1fr);
            column-gap: 0.35rem;
            align-items: start;
        }

        .species-description-label,
        .species-description-content p {
            line-height: 1.8;
        }

        .species-description-label {
            color: #1f2937;
            white-space: nowrap;
        }

        .species-description-content p {
            margin: 0;
        }

        .species-photo-box {
            width: 100%;
            box-sizing: border-box;
        }

        .species-photo-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 32px 28px;
            padding-top: 1rem;
        }

        .species-photo-card {
            margin: 0;
            width: 250px;
        }

        .species-photo-description {
            font-size: 0.78rem;
            line-height: 1.45;
            color: #4b5563;
        }

        .species-chart-title {
            margin-bottom: 10px;
            font-weight: 600;
            color: #374151;
        }

        .species-chart-group-title {
            margin: 1.5rem 0 0.75rem;
            border-left: 4px solid #facc15;
            padding-left: 0.75rem;
            font-weight: 600;
            color: #1f2937;
        }

        .figouter .species-chart-title {
            cursor: default;
        }

        .species-chart-sections {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 24px;
            align-items: start;
            width: 100%;
        }

        .species-chart-panel {
            min-width: 0;
            width: 100%;
        }

        .species-chart-item {
            margin: 18px 0 28px;
        }

        .species-chart-frame {
            display: block;
            position: relative;
            width: 100%;
            max-width: 100%;
            min-height: 320px;
            margin: 10px 0;
            overflow: hidden;
        }

        .species-chart-frame canvas {
            display: block;
            width: 100% !important;
            max-width: 100% !important;
            opacity: 0;
            transition: opacity 0.18s ease;
        }

        .species-chart-frame.is-ready canvas {
            opacity: 1;
        }

        .species-chart-placeholder {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #e5e7eb;
            background: repeating-linear-gradient(-45deg,
                    #f9fafb,
                    #f9fafb 12px,
                    #f3f4f6 12px,
                    #f3f4f6 24px);
            color: #6b7280;
            font-size: 0.85rem;
            pointer-events: none;
        }

        .species-chart-frame.is-ready .species-chart-placeholder {
            display: none;
        }

        .species-chart-map-frame {
            min-height: 500px;
        }

        .species-result-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 0.25rem;
        }

        .species-result-tab {
            appearance: none;
            border: 0;
            border-radius: 0.5rem 0.5rem 0 0;
            padding: 0.6rem 1.25rem;
            color: #4b5563;
            background: #e5e7eb;
            text-decoration: none !important;
        }

        .species-result-content {
            width: 100%;
            min-height: 12rem;
            border: 1px solid #e5e7eb;
            border-radius: 0 0.5rem 0.5rem 0.5rem;
            background: #fff;
            padding: 1rem;
        }

        .species-result-tab:hover {
            background: #f3f4f6;
            color: #355416;
            font-weight: 600;
            text-decoration: none !important;
        }

        .species-result-tab.is-active {
            background: #6b7f32;
            color: #fff;
            font-weight: 700;
        }

        .species-result-tab.is-active:hover {
            background: #6b7f32;
            color: #fff;
        }

        .species-result-building {
            min-height: 12rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 0 0 0.5rem 0.5rem;
            background: #fff;
            color: #6b7280;
            width: 100%;
        }

        @media (max-width: 1024px) {
            .species-chart-sections {
                grid-template-columns: 1fr;
            }

            .species-chart-frame {
                width: 100%;
                max-width: 560px;
                margin-inline: auto;
            }

            .species-chart-map-frame {
                max-width: 560px;
            }

            .spheader h2 {
                display: flex;
                flex-direction: column;
                align-items: flex-start;
                gap: 0.35rem;
            }

            .spheader h2 > span {
                margin-left: 0 !important;
            }

            .species-scientific-name {
                white-space: nowrap;
            }

            .species-research-flags {
                align-items: flex-start;
            }

            .species-research-flag-row {
                justify-content: start;
            }

            .species-research-subjects {
                justify-content: flex-start;
            }
        }

        @media (max-width: 767px) {
            .spheader h2 {
                display: flex;
                flex-direction: column;
                gap: 0.35rem;
            }

            .spheader h2 span,
            .spheader p span,
            .spheader p sapn {
                margin-left: 0 !important;
            }

            .species-research-flags {
                align-items: flex-start;
                padding-inline: 0;
            }

            .species-research-subjects {
                justify-content: flex-start;
            }

            .species-result-content {
                padding: 0.75rem;
            }

            .species-chart-panel,
            .species-description-box,
            .species-photo-box {
                padding-inline: 1rem;
            }

            .species-chart-frame {
                width: 100%;
                max-width: 340px;
                min-height: 0;
                margin: 0.75rem auto 1rem;
            }

            .species-chart-map-frame {
                max-width: 100%;
                min-height: 360px;
            }

            .species-chart-item {
                margin-block: 1rem 1.5rem;
            }
        }
    </style>
    <div>
        <a href="{{ route('front.splist') }}"
            class="inline-flex items-center gap-1 text-sm text-gray-600 no-underline hover:font-semibold hover:text-forest hover:no-underline">
            <span aria-hidden="true">←</span>
            <span>{{ __('web.back_to_plant_list') }}</span>
        </a>
    </div>
    <div class='spheader'>
        <div>
            <h2><span class="species-chinese-name">{{ ($speciesinfo['checklist_chname'] ?? null) ?: $speciesinfo['csp'] }}</span>
                <span class="species-scientific-name">{!! \App\Support\PlantCatalog\ScientificNameFormatter::format($speciesinfo['now_simname']) !!}</span>
            </h2>
            <p>
                @if ($speciesinfo['now_simname'] != $speciesinfo['spcode_simname'])
                    <span style="margin-left: 20px ;">({!! \App\Support\PlantCatalog\ScientificNameFormatter::format($speciesinfo['spcode_simname']) !!})</span>
                @endif
            </p>
            <p style='margin-bottom: 20px'>{{ $speciesinfo['apgfamily'] }}<sapn style='margin-left: 30px'>
                    {{ $speciesinfo['chapgfamily'] }}</sapn>
            </p>
            <p>{{ strtoupper($speciesinfo['life_form']) }}</p>
        </div>


        <div style='display: inline-flex; align-items: flex-start;'>
            <div class="species-research-flags px-3 py-2">
                @php
                    $researchTopicTags = [
                        'tree' => ['subject_tree_survey', 'subjects/long-term-tree-dynamics', 1],
                        'mortality' => ['subject_mortality_survey', 'subjects/long-term-tree-dynamics', 1],
                        'seedling' => ['subject_seedling_dynamics', 'subjects/long-term-seedling-dynamics', 2],
                        'seed' => ['subject_plant_phenology', 'subjects/plant-reproduction-phenology', 6],
                    ];
                @endphp
                @foreach ($researchSites as $site)
                    <div class="species-research-flag-row">
                        @php($siteIndex = array_search($site, ['fushan', 'nanjenshan', 'shoushan'], true))
                        <a class="{{ $this->tagClasses() }}"
                            style="{{ $this->tagStyle('site', 'sites/' . $site, ($siteIndex === false ? 0 : $siteIndex) + 1) }}"
                            href="{{ url('/sites/' . $site) }}">
                            {{ __('web.site_' . $site) }}
                        </a>
                        <div class="species-research-subjects">
                            @foreach ($researchTopicTags as $code => [$labelKey, $slug, $fallbackId])
                                @if (($researchesBySite[$site][$code] ?? 0) != 0)
                                    <a class="{{ $this->tagClasses() }}"
                                        style="{{ $this->tagStyle('subject', $slug, $fallbackId) }}"
                                        href="{{ url('/' . $slug) }}">
                                        {{ __('web.' . $labelKey) }}
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
            @if ($leafphoto == 'yes' && $featuredPhoto)
                <p class='text_box' style='padding: 0px;'><a
                        href='{{ asset("/FDPfiles/splist/photo/{$featuredPhoto['spcode']}/{$featuredPhoto['filename']}") }}'
                        data-fancybox="gallery" data-caption="{{ __('web.reference_photos') }}">
                        <img src="{{ asset("/FDPfiles/splist/photo/{$featuredPhoto['spcode']}/s_{$featuredPhoto['filename']}") }}"
                            width="230"></a>
                </p>
            @endif
        </div>
    </div>
    @if (count($desinfo) > 0)
        <div class='species-description-box rounded-md px-6 py-4'>
            <div class="mb-2 flex items-start pr-4">
                <div class="mr-2 w-1 self-stretch bg-yellow-400"></div>

                {{-- 標題 --}}
                <div class="whitespace-normal font-semibold leading-relaxed text-gray-800">
                    {{ __('web.identification_notes') }}<span style='margin-left:30px; font-size: 80%;'
                        class='font-normal text-gray-700'>{{ __('web.identification_note_use') }}</span>
                </div>
            </div>
            <div class='species-description-notes mt-4 text-sm text-gray-700'>
                <div class="species-description-list">
                    @foreach ($desinfo as $type => $typeNotes)
                        <div class="species-description-row">
                            <div class="species-description-label">{{ $type }}：</div>
                            <div class="species-description-content">
                                @foreach ($typeNotes as $index => $note)
                                    <p>{{ $note }}</p>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>
    @endif

    @if (count($photoinfo) > 0)
        <div class='rounded-md bg-white px-6 py-4'>
            <div class="mb-2 flex items-start pr-4">
                <div class="mr-2 w-1 self-stretch bg-yellow-400"></div>

                {{-- 標題 --}}
                <div class="whitespace-normal font-semibold leading-relaxed text-gray-800">
                    {{ __('web.reference_photos') }}
                </div>
            </div>
            <div class="species-photo-grid">
                @for ($i = 0; $i < count($photoinfo); $i++)
                    <div class='photocombo photoimgbox species-photo-card' data-key='{{ $i }}'>
                        <div class='photo'>
                            <a href='{{ asset("/FDPfiles/splist/photo/{$photoinfo[$i]['spcode']}/{$photoinfo[$i]['filename']}") }}'
                                data-fancybox="gallery"
                                data-caption="{{ __('web.photo_type') }}: {{ $photoinfo[$i]['type'] }} / {{ $photoinfo[$i]['fresh'] }} / {{ $photoinfo[$i]['status'] }}<br>photo by: {{ $photoinfo[$i]['photoby'] }}@if ($photoinfo[$i]['des'] != '')
<br>{{ $photoinfo[$i]['des'] }}
@endif">
                                <img src="{{ asset("/FDPfiles/splist/photo/{$photoinfo[$i]['spcode']}/s_{$photoinfo[$i]['filename']}") }}"
                                    width="250">
                            </a>

                        </div>
                        <div class='photodes species-photo-description'>
                            {{ __('web.photo_type') }}: {{ $photoinfo[$i]['type'] }} / {{ $photoinfo[$i]['fresh'] }} /
                            {{ $photoinfo[$i]['status'] }}<br>
                            photo by: {{ $photoinfo[$i]['photoby'] }}
                            @if ($photoinfo[$i]['des'] != '')
                                <br>{{ $photoinfo[$i]['des'] }}
                            @endif
                        </div>
                    </div>
                @endfor
            </div>

        </div>

    @endif

    @if ($researchSites !== [])
        <section class="pt-2" x-data="{ activeSite: @js($researchSites[0]) }">
            <div class="species-result-tabs" role="tablist" aria-label="{{ __('web.select_site') }}">
                @foreach ($researchSites as $site)
                    <button type="button" class="species-result-tab"
                        :class="{ 'is-active': activeSite === '{{ $site }}' }"
                        @click="activeSite = '{{ $site }}'; $nextTick(() => window.dispatchEvent(new Event('resize')))" role="tab"
                        :aria-selected="activeSite === '{{ $site }}'">
                        {{ __('web.site_' . $site) }}
                    </button>
                @endforeach
            </div>

            @if (in_array('fushan', $researchSites, true))
                <div x-show="activeSite === 'fushan'" role="tabpanel" class="species-result-content">
                    <div class="loading-container" wire:loading.class="visible">
                        <div class="loading-spinner"></div>
                    </div>

                    <div class="species-chart-sections pb-4">
                        @if ($countInd > 0)
                            <x-web.species-result-panel :title="__('web.tree_results')">
                                <x-slot:summary>
                                    <p>{{ __('web.tree_summary', ['year' => $latestTreeCensusYear, 'trees' => $countInd, 'branches' => $countB, 'dbh' => $maxDBH]) }}</p>
                                </x-slot:summary>
                                <div class='figouter'>
                                    <x-web.species-chart :title="__('web.chart_plant_counts')" chart="fig1"
                                        canvas-id="myChartFig1" />
                                    <x-web.species-chart :title="__('web.chart_dbh', ['year' => $latestTreeCensusYear])" chart="fig2"
                                        canvas-id="myChartFig2" />
                                    <x-web.species-chart :title="__('web.chart_distribution', ['year' => $latestTreeCensusYear])" chart="fig3"
                                        canvas-id="myChartFig3" :map="true" />
                                </div>
                            </x-web.species-result-panel>
                        @endif
                        @if ($countSeeds > 0 || $countFlower > 0 || $countSeedlings > 0)
                            <x-web.species-result-panel :title="__('web.phenology')">
                                <x-slot:summary>
                                    <p>
                                        {{ __('web.phenology_collected') }}
                                        @if ($countFlower > 0)
                                            {{ __('web.fallen_flowers', ['count' => $countFlower]) }}
                                        @endif
                                        @if ($countSeeds > 0)
                                            {{ __('web.seeds_collected', ['count' => $countSeeds]) }}
                                        @endif
                                    </p>
                                </x-slot:summary>

                                <div class='figouter'>
                                    @if ($countFlower > 0)
                                        <x-web.species-chart :title="__('web.chart_flowering')" chart="fig4"
                                            canvas-id="myChartFig4" />
                                    @endif
                                    @if ($countSeeds > 0)
                                        <x-web.species-chart :title="__('web.chart_fruiting')" chart="fig5"
                                            canvas-id="myChartFig5" />
                                    @endif
                                    @if ($countSeedlings > 0)
                                        <x-web.species-chart :title="__('web.chart_seedlings')" chart="fig6"
                                            canvas-id="myChartFig6" :group-title="__('web.seedling_dynamics')"
                                            :description="__('web.seedling_summary', ['count' => $countSeedlings])" />
                                    @endif
                                </div>
                            </x-web.species-result-panel>
                        @endif
                    </div>
                </div>
            @endif

            @if (in_array('nanjenshan', $researchSites, true))
                <div x-show="activeSite === 'nanjenshan'" x-cloak role="tabpanel"
                    class="species-result-content">
                    @if (($researchesBySite['nanjenshan']['seedling'] ?? 0) != 0)
                        <div class="species-chart-sections pb-4">
                            <x-web.species-result-panel :title="__('web.seedling_dynamics')">
                                <x-slot:summary>
                                    <p>{{ __('web.seedling_summary', ['count' => $countNjsSeedlings]) }}</p>
                                </x-slot:summary>
                                <x-web.species-chart :title="__('web.chart_seedlings')" chart="fig7"
                                    canvas-id="myChartFig7" />
                            </x-web.species-result-panel>
                        </div>
                    @else
                        <div class="species-result-building">
                            <p class="mb-0">{{ __('web.site_data_building', ['site' => __('web.site_nanjenshan')]) }}</p>
                        </div>
                    @endif
                </div>
            @endif

            @if (in_array('shoushan', $researchSites, true))
                <div x-show="activeSite === 'shoushan'" x-cloak role="tabpanel"
                    class="species-result-content">
                    @if (($researchesBySite['shoushan']['tree'] ?? 0) != 0)
                        <div class="species-chart-sections pb-4">
                            <x-web.species-result-panel :title="__('web.tree_results')">
                                <x-slot:summary>
                                    <p>{{ __('web.tree_summary', ['year' => 2024, 'trees' => $countSsInd, 'branches' => $countSsB, 'dbh' => $maxSsDBH]) }}</p>
                                </x-slot:summary>
                                <div class="figouter">
                                    <x-web.species-chart :title="__('web.chart_plant_counts')" chart="fig8"
                                        canvas-id="myChartFig8" />
                                    <x-web.species-chart :title="__('web.chart_dbh', ['year' => 2024])" chart="fig9"
                                        canvas-id="myChartFig9" />
                                    <x-web.species-chart :title="__('web.chart_distribution', ['year' => 2024])" chart="fig10"
                                        canvas-id="myChartFig10" :map="true" />
                                </div>
                            </x-web.species-result-panel>
                        </div>
                    @else
                        <div class="species-result-building">
                            <p class="mb-0">{{ __('web.site_data_building', ['site' => __('web.site_shoushan')]) }}</p>
                        </div>
                    @endif
                </div>
            @endif
        </section>
    @endif
</div>
