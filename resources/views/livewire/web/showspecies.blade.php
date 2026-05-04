@php
    $chartMethods = [];

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
        .species-research-flags {
            display: flex;
            gap: 8px;
            align-items: center;
            justify-content: end;
            margin-top: 4px;
        }

        .species-research-flag {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 42px;
            height: 32px;
        }

        .species-research-icon,
        .species-research-fallen-tree-icon {
            display: block;
            object-fit: contain;
            margin: 0 auto;
        }

        .species-research-icon {
            width: 19px;
            height: 19px;
        }

        .species-research-fallen-tree-icon {
            width: 22px;
            height: 22px;
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

        .figouter .species-chart-title {
            cursor: default;
        }

        .species-chart-sections {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 24px;
            align-items: start;
        }

        .species-chart-panel {
            min-width: 0;
        }

        .species-chart-item {
            margin: 18px 0 28px;
        }

        .species-chart-frame {
            display: block;
            position: relative;
            width: 100%;
            min-height: 320px;
            margin: 10px 0;
        }

        .species-chart-frame canvas {
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
            background: repeating-linear-gradient(
                -45deg,
                #f9fafb,
                #f9fafb 12px,
                #f3f4f6 12px,
                #f3f4f6 24px
            );
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

        @media (max-width: 1024px) {
            .species-chart-sections {
                grid-template-columns: 1fr;
            }
        }
    </style>
    <div class='spheader'>
        <div>
            <h2><i>{!! str_replace([' var. ', ' ssp. '], [' </i>var.<i> ', ' </i>ssp.<i> '], $speciesinfo['now_simname']) !!}</i>
                <sapn style='margin-left: 30px'>{{ $speciesinfo['csp'] }}</sapn>
            </h2>
            <p>{{ $speciesinfo['spcode'] }}
                @if ($speciesinfo['now_simname'] != $speciesinfo['spcode_simname'])
                    <span style="margin-left: 20px ;">(<i>{!! str_replace([' var. ', ' ssp. '], [' <i>var.</i> ', ' <i>ssp.</i> '], $speciesinfo['spcode_simname']) !!}</i>)</span>
                @endif
            </p>
            <p style='margin-bottom: 20px'>{{ $speciesinfo['apgfamily'] }}<sapn style='margin-left: 30px'>
                    {{ $speciesinfo['chapgfamily'] }}</sapn>
            </p>
            <p>{{ strtoupper($speciesinfo['life_form']) }}</p>
        </div>


        <div style='display: inline-flex; align-items: flex-start;'>
            <div class="species-research-flags px-3 py-2">
                @if (($researches['tree'] ?? 0) != 0)
                    <span class="species-research-flag">
                        <img class="species-research-icon" src="{{ asset('images/icon/tree.png') }}" alt="tree">
                    </span>
                @endif
                @if (($researches['seed'] ?? 0) != 0)
                    <span class="species-research-flag">
                        <i class="fa-solid fa-apple-whole"></i>
                    </span>
                @endif
                @if (($researches['seedling'] ?? 0) != 0)
                    <span class="species-research-flag">
                        <i class="fa-solid fa-seedling"></i>
                    </span>
                @endif
                @if (($researches['mortality'] ?? 0) != 0)
                    <span class="species-research-flag">
                        <img class="species-research-fallen-tree-icon" src="{{ asset('images/icon/fallen-tree.png') }}"
                            alt="fallen tree">
                    </span>
                @endif
            </div>
            @if ($leafphoto == 'yes')
                <p class='text_box' style='padding: 0px;'><a
                        href='{{ asset("/FDPfiles/splist/leafphoto/{$speciesinfo['csp']}.jpg") }}'
                        data-fancybox="gallery"
                        data-caption="葉片照片">
                        <img src="{{ asset("/FDPfiles/splist/leafphoto/{$speciesinfo['csp']}.jpg") }}"
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
                    辨識要點<span style='margin-left:30px; font-size: 80%;'
                        class='font-normal text-gray-700'>*種子雨收集與小苗調查用</span>
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
                    參考照片
                </div>
            </div>
            <div class="species-photo-grid">
                @for ($i = 0; $i < count($photoinfo); $i++)
                    <div class='photocombo photoimgbox species-photo-card' data-key='{{ $i }}'>
                        <div class='photo'>
                            <a href='{{ asset("/FDPfiles/splist/photo/{$photoinfo[$i]['spcode']}/{$photoinfo[$i]['filename']}") }}'
                                data-fancybox="gallery"
                                data-caption="類型: {{ $photoinfo[$i]['type'] }} / {{ $photoinfo[$i]['fresh'] }} / {{ $photoinfo[$i]['status'] }}<br>photo by: {{ $photoinfo[$i]['photoby'] }}@if ($photoinfo[$i]['des'] != '')
<br>{{ $photoinfo[$i]['des'] }}
@endif">
                                <img src="{{ asset("/FDPfiles/splist/photo/{$photoinfo[$i]['spcode']}/s_{$photoinfo[$i]['filename']}") }}"
                                    width="250">
                            </a>

                        </div>
                        <div class='photodes species-photo-description'>
                            類型: {{ $photoinfo[$i]['type'] }} / {{ $photoinfo[$i]['fresh'] }} /
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

    <div class="loading-container" wire:loading.class="visible">
        <div class="loading-spinner"></div>
    </div>

    <div class="species-chart-sections pb-4">
        @if ($countInd > 0)
            <div class='species-chart-panel rounded-md bg-white px-6 py-4'>

                <div class="mb-2 flex items-start pr-4">
                    <div class="mr-2 w-1 self-stretch bg-yellow-400"></div>

                    {{-- 標題 --}}
                    <div class="whitespace-normal font-semibold leading-relaxed text-gray-800">
                        福山每木調查成果
                    </div>
                </div>
                <div class='mt-2'>
                <p>
                    共標定 {{ $countInd }} 棵樹 以及 {{ $countB }} 個分支。
                    最大樹的胸徑為 {{ $maxDBH }} cm。<br>
                </p>
                </div>
                {{-- {{print_r($censusA)}} --}}
                <div class='figouter'>
                    <div class="species-chart-item">
                        <h6 class="species-chart-title">各次調查植株數量圖</h6>

                        <div class="fig fig1 species-chart-frame" wire:ignore>
                            <div class="species-chart-placeholder">圖表載入中...</div>
                            <canvas id="myChartFig1"></canvas>
                        </div>
                    </div>
                    <div class="species-chart-item">
                        <h6 class="species-chart-title">{{ $latestTreeCensusYear }}年調查徑級結構</h6>
                        <div class="fig fig2 species-chart-frame" wire:ignore>
                            <div class="species-chart-placeholder">圖表載入中...</div>
                            <canvas id="myChartFig2"></canvas>

                        </div>
                    </div>

                    <div class="species-chart-item">
                        <h6 class="species-chart-title">{{ $latestTreeCensusYear }}年調查植株位置分布</h6>
                        <div class="fig fig3 species-chart-frame species-chart-map-frame" wire:ignore>
                            <div class="species-chart-placeholder">圖表載入中...</div>
                            <canvas id="myChartFig3"></canvas>

                        </div>
                    </div>

                </div>
            </div>
        @endif
        @if ($countSeeds > 0 || $countFlower > 0 || $countSeedlings > 0)
            <div class='species-chart-panel rounded-md bg-white px-6 py-4'>
                <div class="mb-2 flex items-start pr-4">
                    <div class="mr-2 w-1 self-stretch bg-yellow-400"></div>

                    {{-- 標題 --}}
                    <div class="whitespace-normal font-semibold leading-relaxed text-gray-800">
                        福山種子雨與小苗監測成果
                    </div>
                </div>
                <div class='mt-2'>
                <p>
                    共收集到
                    @if ($countFlower > 0)
                        {{ $countFlower }} 筆落花。
                    @endif
                    @if ($countSeeds > 0)
                        {{ $countSeeds }} 顆種子。
                    @endif
                    @if ($countSeedlings > 0)
                        記錄到 {{ $countSeedlings }} 筆小苗。
                    @endif
                </p>
                </div>

                <div class='figouter'>
                    @if ($countFlower > 0)
                        <div class="species-chart-item">
                            <h6 class="species-chart-title">開花量時間變化</h6>

                            <div class="fig fig4 species-chart-frame" wire:ignore>
                                <div class="species-chart-placeholder">圖表載入中...</div>
                                <canvas id="myChartFig4"></canvas>
                            </div>
                        </div>
                    @endif
                    @if ($countSeeds > 0)
                        <div class="species-chart-item">
                            <h6 class="species-chart-title">結果量時間變化</h6>
                            <div class="fig fig5 species-chart-frame" wire:ignore>
                                <div class="species-chart-placeholder">圖表載入中...</div>
                                <canvas id="myChartFig5"></canvas>

                            </div>
                        </div>
                    @endif
                    @if ($countSeedlings > 0)
                        <div class="species-chart-item">
                            <h6 class="species-chart-title">小苗數量時間變化</h6>
                            <div class="fig fig6 species-chart-frame" wire:ignore>
                                <div class="species-chart-placeholder">圖表載入中...</div>
                                <canvas id="myChartFig6"></canvas>

                            </div>
                        </div>
                    @endif
                </div>

            </div>
        @endif
    </div>
</div>
