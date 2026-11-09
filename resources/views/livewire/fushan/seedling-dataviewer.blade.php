
<div class='flex text_outbox' style='flex-direction: column; align-items: center;'>
    @include('livewire.partials.dataviewer-styles')
    <div class='text_box' style='margin: 0 auto;'>
        <div class="loading-container" wire:loading.class="visible">
            <div class="loading-spinner"></div>
        </div>
        @include('livewire.partials.dataviewer-summary', [
            'title' => '福山小苗資料檢視',
            'latestLabel' => '最新調查年月',
            'latestValue' => $latestSurveyYm,
            'total' => $total,
        ])
        <hr>
        <div style="margin-bottom: 8px; color: #555; font-size: 0.95em;">
            可點選 census、trap、mtag、tag、種類欄位標題排序；調查年月依 census 排序。
        </div>
        @include('livewire.partials.dataviewer-pagination')
        <table
            id='fs-seedling-dataviewer-table'
            class='tablesorter dataviewer-table'
            wire:key="fs-seedling-dataviewer-{{ $trap }}-{{ $plot }}-{{ $ym }}-{{ md5($mtag) }}-{{ md5($tag) }}-{{ md5($species) }}-{{ $status }}-{{ $recruit }}-{{ $sprout }}-{{ $sortField }}-{{ $sortDirection }}-{{ $page }}-{{ count($data) }}"
        >
            <thead>
                <tr>
                    <th>@include('livewire.partials.dataviewer-sortable-heading', ['field' => 'census', 'label' => 'census'])</th>
                    <th>調查年月</th>
                    <th>@include('livewire.partials.dataviewer-sortable-heading', ['field' => 'trap', 'label' => 'trap'])</th>
                    <th>plot</th>
                    <th>@include('livewire.partials.dataviewer-sortable-heading', ['field' => 'mtag', 'label' => 'mtag'])</th>
                    <th>@include('livewire.partials.dataviewer-sortable-heading', ['field' => 'tag', 'label' => 'tag'])</th>
                    <th>@include('livewire.partials.dataviewer-sortable-heading', ['field' => 'species', 'label' => '種類'])</th>
                    <th>長度</th>
                    <th>葉片數</th>
                    <th>新舊</th>
                    <th>狀態</th>
                    <th>萌櫱</th>
                    <th style='width: 220px;'>note</th>
                </tr>
                <tr>
                    <td></td>
                    <td>
                        @include('livewire.partials.select-filter', ['model' => 'ym', 'options' => $months])
                    </td>
                    <td>
                        @include('livewire.partials.select-filter', ['model' => 'trap', 'options' => $traps])
                    </td>
                    <td>
                        @include('livewire.partials.select-filter', ['model' => 'plot', 'options' => $plots])
                    </td>
                    <td>
                        <input
                            type="text"
                            class="fs100"
                            style="width: 70px;"
                            wire:model="mtag"
                            wire:change="search"
                            wire:keydown.enter="search"
                            placeholder="all"
                        >
                    </td>
                    <td>
                        <input
                            type="text"
                            class="fs100"
                            style="width: 70px;"
                            wire:model="tag"
                            wire:change="search"
                            wire:keydown.enter="search"
                            placeholder="all"
                        >
                    </td>
                    <td>
                        @include('livewire.partials.datalist-filter', [
                            'model' => 'species',
                            'options' => $speciesOptions,
                            'listId' => 'fs-seedling-species-options',
                            'width' => '150px',
                        ])
                    </td>
                    <td>
                        @include('livewire.partials.numeric-filter', ['operatorModel' => 'heightOperator', 'valueModel' => 'heightValue'])
                    </td>
                    <td></td>
                    <td>
                        @include('livewire.partials.select-filter', ['model' => 'recruit', 'options' => $recruitOptions])
                    </td>
                    <td>
                        @include('livewire.partials.select-filter', ['model' => 'status', 'options' => $statusOptions])
                    </td>
                    <td>
                        @include('livewire.partials.select-filter', ['model' => 'sprout', 'options' => $sproutOptions])
                    </td>
                    <td></td>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $row)
                    @php
                        $isNewCensus = !$loop->first && $row['census'] !== $data[$loop->index - 1]['census'];
                    @endphp
                    <tr
                        wire:key="fs-seedling-row-{{ $row['census'] }}-{{ $row['trap'] }}-{{ $row['plot'] }}-{{ $row['tag'] }}"
                        @if($isNewCensus) style="border-top: 3px solid #6f7f18;" @endif
                    >
                        <td>{{$row['census']}}</td>
                        <td>{{$row['ym']}}</td>
                        <td>{{$row['trap']}}</td>
                        <td>{{$row['plot']}}</td>
                        <td>{{$row['mtag']}}</td>
                        <td>{{$row['tag']}}</td>
                        <td>{{$row['species']}}</td>
                        <td>{{$row['height']}}</td>
                        <td>{{$row['leaf']}}</td>
                        <td>{{$row['recruit']}}</td>
                        <td>{{$row['status']}}</td>
                        <td>{{$row['sprout']}}</td>
                        <td>{{$row['note']}}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
