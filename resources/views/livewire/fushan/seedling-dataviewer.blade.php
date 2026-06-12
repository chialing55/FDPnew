
<div class='flex text_outbox' style='flex-direction: column; align-items: center;'>
    <style>
        #fs-seedling-dataviewer-table .sortable-heading {
            appearance: none;
            background: transparent !important;
            border: none !important;
            box-shadow: none !important;
            color: inherit;
            cursor: pointer;
            font: inherit;
            font-weight: inherit;
            outline: none;
            padding: 0;
            text-decoration: none;
            white-space: nowrap;
        }

        #fs-seedling-dataviewer-table .sortable-heading:hover {
            text-decoration: none;
        }
    </style>
    <div class='text_box' style='margin: 0 auto;'>
        <div class="loading-container" wire:loading.class="visible">
            <div class="loading-spinner"></div>
        </div>
        <h2>福山小苗資料檢視<span style="margin-left: 20px ; font-weight: 500; font-size: 70%;">最新調查年月：{{ $latestSurveyYm }}</span><span style="margin-left: 20px ; font-weight: 500; font-size: 70%;">共 {{ $total }} 筆</span></h2>
        <hr>
        <div style="margin-bottom: 8px; color: #555; font-size: 0.95em;">
            可點選 census、trap、mtag、tag、種類欄位標題排序；調查年月依 census 排序。
        </div>
        @include('livewire.partials.dataviewer-pagination')
        <table
            id='fs-seedling-dataviewer-table'
            class='tablesorter'
            wire:key="fs-seedling-dataviewer-{{ $trap }}-{{ $plot }}-{{ $ym }}-{{ md5($mtag) }}-{{ md5($tag) }}-{{ md5($species) }}-{{ $status }}-{{ $recruit }}-{{ $sprout }}-{{ $sortField }}-{{ $sortDirection }}-{{ $page }}-{{ count($data) }}"
        >
            <thead>
                <tr>
                    <th><button type="button" class="sortable-heading" wire:click="sortBy('census')">census{{ $this->sortIndicator('census') }}</button></th>
                    <th>調查年月</th>
                    <th><button type="button" class="sortable-heading" wire:click="sortBy('trap')">trap{{ $this->sortIndicator('trap') }}</button></th>
                    <th>plot</th>
                    <th><button type="button" class="sortable-heading" wire:click="sortBy('mtag')">mtag{{ $this->sortIndicator('mtag') }}</button></th>
                    <th><button type="button" class="sortable-heading" wire:click="sortBy('tag')">tag{{ $this->sortIndicator('tag') }}</button></th>
                    <th><button type="button" class="sortable-heading" wire:click="sortBy('species')">種類{{ $this->sortIndicator('species') }}</button></th>
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
                        @include('livewire.partials.datalist-filter', [
                            'model' => 'mtag',
                            'options' => $mtagOptions,
                            'listId' => 'fs-seedling-mtag-options',
                            'width' => '70px',
                        ])
                    </td>
                    <td>
                        @include('livewire.partials.datalist-filter', [
                            'model' => 'tag',
                            'options' => $tagOptions,
                            'listId' => 'fs-seedling-tag-options',
                            'width' => '70px',
                        ])
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
