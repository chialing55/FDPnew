<div class='flex text_outbox' style='flex-direction: column; align-items: center;'>
    <div class='text_box' style='margin: 0 auto;'>
        <div class="loading-container" wire:loading.class="visible">
            <div class="loading-spinner"></div>
        </div>
        <h2>南仁山小苗資料檢視<span style="margin-left: 20px ; font-weight: 500; font-size: 70%;">共 {{ $total }} 筆</span></h2>
        <hr>
        @include('livewire.partials.dataviewer-pagination')
        <table
            id='njs-seedling-dataviewer-table'
            class='tablesorter'
            wire:key="njs-seedling-dataviewer-{{ $plot }}-{{ $quadrat }}-{{ $ym }}-{{ md5($tag) }}-{{ md5($species) }}-{{ $page }}-{{ count($data) }}"
        >
            <thead>
                <tr>
                    <th>樣區</th>
                    <th>調查樣方</th>
                    <th>census</th>
                    <th>調查年月</th>
                    <th>tag</th>
                    <th>種類</th>
                    <th>狀態</th>
                    <th>高度</th>
                    <th>子葉數</th>
                    <th>葉片數</th>
                    <th>葉片被蛀比例</th>
                    <th>葉片被覆蓋比例</th>
                    <th>病斑比例</th>
                    <th>死亡原因</th>
                    <th style='width: 200px;'>備注</th>
                </tr>
                <tr>
                    <td>
                        @include('livewire.partials.select-filter', ['model' => 'plot', 'options' => $plots])
                    </td>
                    <td>
                        @include('livewire.partials.select-filter', ['model' => 'quadrat', 'options' => $quadrats])
                    </td>
                    <td></td>
                    <td>
                        @include('livewire.partials.select-filter', ['model' => 'ym', 'options' => $months])
                    </td>
                    <td>
                        @include('livewire.partials.datalist-filter', [
                            'model' => 'tag',
                            'options' => $tagOptions,
                            'listId' => 'njs-seedling-tag-options',
                            'width' => '90px',
                        ])
                    </td>
                    <td>
                        @include('livewire.partials.datalist-filter', [
                            'model' => 'species',
                            'options' => $speciesOptions,
                            'listId' => 'njs-seedling-species-options',
                            'width' => '150px',
                        ])
                    </td>
                    <td>
                        @include('livewire.partials.select-filter', ['model' => 'status', 'options' => $statusOptions])
                    </td>
                    <td>
                        @include('livewire.partials.numeric-filter', ['operatorModel' => 'heightOperator', 'valueModel' => 'heightValue'])
                    </td>
                    <td></td>
                    <td></td>
                    <td>
                        @include('livewire.partials.numeric-filter', ['operatorModel' => 'leafEatenOperator', 'valueModel' => 'leafEatenValue'])
                    </td>
                    <td>
                        @include('livewire.partials.numeric-filter', ['operatorModel' => 'leafCoveredOperator', 'valueModel' => 'leafCoveredValue'])
                    </td>
                    <td>
                        @include('livewire.partials.numeric-filter', ['operatorModel' => 'diseaseSpotOperator', 'valueModel' => 'diseaseSpotValue'])
                    </td>
                    <td></td>
                    <td></td>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $row)
                    @php
                        $isNewQuadrat = !$loop->first && $row['quadrat'] !== $data[$loop->index - 1]['quadrat'];
                    @endphp
                    <tr
                        wire:key="njs-seedling-row-{{ $row['quadrat'] }}-{{ $row['census'] }}-{{ $row['tag'] }}"
                        @if($isNewQuadrat) style="border-top: 3px solid #6f7f18;" @endif
                    >
                        <td>{{$row['plot_name']}}</td>
                        <td>{{$row['quadrat']}}</td>
                        <td>{{$row['census']}}</td>
                        <td>{{$row['ym']}}</td>
                        <td>{{$row['tag']}}</td>
                        <td>{{$row['species']}}</td>
                        <td>{{$row['status']}}</td>
                        <td>{{$row['height']}}</td>
                        <td>{{$row['cotyledon']}}</td>
                        <td>{{$row['leaf']}}</td>
                        <td>{{$row['leaf_eaten_percent']}}</td>
                        <td>{{$row['leaf_covered_percent']}}</td>
                        <td>{{$row['disease_spot_percent']}}</td>
                        <td>{{$row['death_cause']}}</td>
                        <td>{{$row['remark']}}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
