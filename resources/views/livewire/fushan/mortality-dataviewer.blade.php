<div class='flex text_outbox' style='flex-direction: column; align-items: center;'>
    @once
        <style>
            #fs-mortality-dataviewer-table thead tr.mortality-dataviewer-label-row th {
                height: 128px;
                vertical-align: bottom;
                padding: 0 4px;
                box-sizing: border-box;
            }

            #fs-mortality-dataviewer-table .mortality-dataviewer-header {
                height: 128px;
                display: flex;
                align-items: flex-end;
                justify-content: center;
                overflow: hidden;
                padding: 0;
                box-sizing: border-box;
            }

            #fs-mortality-dataviewer-table .mortality-dataviewer-header-text {
                writing-mode: vertical-rl;
                transform: rotate(180deg);
                white-space: nowrap;
                line-height: 1.05;
                display: inline-block;
                font-weight: 500;
                font-size: 11px;
            }

            #fs-mortality-dataviewer-table thead tr.mortality-dataviewer-filter-row td {
                vertical-align: top;
            }
        </style>
    @endonce
    <div class='text_box' style='margin: 0 auto;'>
        <div class="loading-container" wire:loading.class="visible">
            <div class="loading-spinner"></div>
        </div>
        <h2>死亡率調查資料檢視<span style="margin-left: 20px ; font-weight: 500; font-size: 70%;">最新調查年份：{{ $latestSurveyYear }}</span><span style="margin-left: 20px ; font-weight: 500; font-size: 70%;">共 {{ $total }} 筆</span></h2>
        <hr>
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
        <table
            id='fs-mortality-dataviewer-table'
            class='tablesorter'
            wire:key="fs-mortality-dataviewer-{{ md5($map) }}-{{ $year }}-{{ md5($stemid) }}-{{ md5($species) }}-{{ $status }}-{{ $mode }}-{{ $page }}-{{ count($data) }}"
        >
            <thead>
                @php
                    $headers = [
                        'map',
                        'year',
                        'date',
                        'stemid',
                        'csp',
                        'qx',
                        'qy',
                        'DBH',
                        'Status',
                        'Mode',
                        'Living length',
                        'Branches',
                        'Illumination',
                        'Leaning',
                        'Liana',
                        'Fungi',
                        'Wounded stem',
                        'Deformity',
                        'Rotten',
                        'Leaves',
                        'Leaf damage',
                        'comments',
                    ];
                @endphp
                <tr class="mortality-dataviewer-label-row">
                    @foreach($headers as $header)
                        <th @if($header === 'comments') style='width: 240px;' @endif>
                            <div class="mortality-dataviewer-header">
                                <span class="mortality-dataviewer-header-text">{{ $header }}</span>
                            </div>
                        </th>
                    @endforeach
                </tr>
                <tr class="mortality-dataviewer-filter-row">
                    <td>
                        <select class="fs100" wire:model='map' wire:change="setFilter('map', $event.target.value)">
                            <option value="all">all</option>
                            @foreach($maps as $mapOption)
                                <option value="{{ $mapOption }}">{{ $mapOption }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <select class="fs100" wire:model='year' wire:change="setFilter('year', $event.target.value)">
                            <option value="all">all</option>
                            @foreach($yearOptions as $yearOption)
                                <option value="{{ $yearOption }}">{{ $yearOption }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td></td>
                    <td>
                        <select class="fs100" style='width: 92px;' wire:model='stemid' wire:change="setFilter('stemid', $event.target.value)">
                            <option value="all">all</option>
                            @foreach($stemidOptions as $stemidOption)
                                <option value="{{ $stemidOption }}">{{ $stemidOption }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <select class="fs100" style='width: 82px;' wire:model='species' wire:change="setFilter('species', $event.target.value)">
                            <option value="all">all</option>
                            @foreach($speciesOptions as $speciesOption)
                                <option value="{{ $speciesOption['spcode'] }}">{{ $speciesOption['label'] }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <select class="fs100" wire:model='qx' wire:change="setFilter('qx', $event.target.value)">
                            <option value="all">all</option>
                            @foreach($qxOptions as $qxOption)
                                <option value="{{ $qxOption }}">{{ $qxOption }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <select class="fs100" wire:model='qy' wire:change="setFilter('qy', $event.target.value)">
                            <option value="all">all</option>
                            @foreach($qyOptions as $qyOption)
                                <option value="{{ $qyOption }}">{{ $qyOption }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <div style="display: inline-flex; gap: 4px; align-items: center;">
                            <select class="fs100" style="width: 40px;" wire:model="dbhOperator" wire:change="setFilter('dbhOperator', $event.target.value)">
                                @foreach($operatorOptions as $operatorOption)
                                    <option value="{{ $operatorOption }}">{{ $operatorOption }}</option>
                                @endforeach
                            </select>
                            <input type="number" step="any" class="fs100" style="width: 40px;" wire:model="dbhValue" wire:change="setFilter('dbhValue', $event.target.value)">
                        </div>
                    </td>
                    <td>
                        <select class="fs100" wire:model='status' wire:change="setFilter('status', $event.target.value)">
                            <option value="all">all</option>
                            @foreach($statusOptions as $statusOption)
                                <option value="{{ $statusOption }}">{{ $statusOption }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <select class="fs100" wire:model='mode' wire:change="setFilter('mode', $event.target.value)">
                            <option value="all">all</option>
                            @foreach($modeOptions as $modeOption)
                                <option value="{{ $modeOption }}">{{ $modeOption }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td></td>
                    <td>
                        <div style="display: inline-flex; gap: 4px; align-items: center;">
                            <select class="fs100" style="width: 40px;" wire:model="branchesOperator" wire:change="setFilter('branchesOperator', $event.target.value)">
                                @foreach($operatorOptions as $operatorOption)
                                    <option value="{{ $operatorOption }}">{{ $operatorOption }}</option>
                                @endforeach
                            </select>
                            <input type="number" step="any" class="fs100" style="width: 40px;" wire:model="branchesValue" wire:change="setFilter('branchesValue', $event.target.value)">
                        </div>
                    </td>
                    <td>
                        <select class="fs100" wire:model='illumination' wire:change="setFilter('illumination', $event.target.value)">
                            <option value="all">all</option>
                            @foreach($illuminationOptions as $illuminationOption)
                                <option value="{{ $illuminationOption }}">{{ $illuminationOption }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td></td>
                    <td>
                        <select class="fs100" wire:model='liana' wire:change="setFilter('liana', $event.target.value)">
                            <option value="all">all</option>
                            @foreach($lianaOptions as $lianaOption)
                                <option value="{{ $lianaOption }}">{{ $lianaOption }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <select class="fs100" wire:model='fungi' wire:change="setFilter('fungi', $event.target.value)">
                            <option value="all">all</option>
                            @foreach($fungiOptions as $fungiOption)
                                <option value="{{ $fungiOption }}">{{ $fungiOption }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <select class="fs100" wire:model='woundedStem' wire:change="setFilter('woundedStem', $event.target.value)">
                            <option value="all">all</option>
                            @foreach($woundedStemOptions as $woundedStemOption)
                                <option value="{{ $woundedStemOption }}">{{ $woundedStemOption }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <select class="fs100" wire:model='deformity' wire:change="setFilter('deformity', $event.target.value)">
                            <option value="all">all</option>
                            @foreach($deformityOptions as $deformityOption)
                                <option value="{{ $deformityOption }}">{{ $deformityOption }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <select class="fs100" wire:model='rotten' wire:change="setFilter('rotten', $event.target.value)">
                            <option value="all">all</option>
                            @foreach($rottenOptions as $rottenOption)
                                <option value="{{ $rottenOption }}">{{ $rottenOption }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <div style="display: inline-flex; gap: 4px; align-items: center;">
                            <select class="fs100" style="width: 40px;" wire:model="leavesOperator" wire:change="setFilter('leavesOperator', $event.target.value)">
                                @foreach($operatorOptions as $operatorOption)
                                    <option value="{{ $operatorOption }}">{{ $operatorOption }}</option>
                                @endforeach
                            </select>
                            <input type="number" step="any" class="fs100" style="width: 40px;" wire:model="leavesValue" wire:change="setFilter('leavesValue', $event.target.value)">
                        </div>
                    </td>
                    <td>
                        <select class="fs100" wire:model='leafDamage' wire:change="setFilter('leafDamage', $event.target.value)">
                            <option value="all">all</option>
                            @foreach($leafDamageOptions as $leafDamageOption)
                                <option value="{{ $leafDamageOption }}">{{ $leafDamageOption }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td></td>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $row)
                    @php
                        $isNewMap = !$loop->first && ($row['map'] !== $data[$loop->index - 1]['map']);
                    @endphp
                    <tr
                        wire:key="fs-mortality-row-{{ $row['id'] }}"
                        @if($isNewMap) style="border-top: 3px solid #6f7f18;" @endif
                    >
                        <td>{{ $row['map'] }}</td>
                        <td>{{ $row['year'] }}</td>
                        <td>{{ $row['date'] }}</td>
                        <td>{{ $row['stemid'] }}</td>
                        <td>{{ $row['species'] }}</td>
                        <td>{{ $row['qx'] }}</td>
                        <td>{{ $row['qy'] }}</td>
                        <td>{{ $row['dbh'] }}</td>
                        <td>{{ $row['status'] }}</td>
                        <td>{{ $row['mode'] }}</td>
                        <td>{{ $row['living_length'] }}</td>
                        <td>{{ $row['branches'] }}</td>
                        <td>{{ $row['illumination'] }}</td>
                        <td>{{ $row['leaning'] }}</td>
                        <td>{{ $row['liana'] }}</td>
                        <td>{{ $row['fungi'] }}</td>
                        <td>{{ $row['wounded_stem'] }}</td>
                        <td>{{ $row['deformity'] }}</td>
                        <td>{{ $row['rotten'] }}</td>
                        <td>{{ $row['leaves'] }}</td>
                        <td>{{ $row['leaf_damage'] }}</td>
                        <td>{{ $row['comments'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
