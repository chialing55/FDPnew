<div class='flex text_outbox' style='flex-direction: column; align-items: center;'>
    <div class='text_box' style='margin: 0 auto;'>
        <div class="loading-container" wire:loading.class="visible">
            <div class="loading-spinner"></div>
        </div>
        <h2>南仁山小苗資料檢視<span style="margin-left: 20px ; font-weight: 500; font-size: 70%;">共 {{ $total }} 筆</span></h2>
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
                        <select class="fs100" wire:model='plot' wire:change="search">
                            <option value="all">all</option>
                            @foreach($plots as $plotOption)
                                <option value="{{$plotOption}}">{{$plotOption}}</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <select class="fs100" wire:model='quadrat' wire:change="search">
                            <option value="all">all</option>
                            @foreach($quadrats as $quadratOption)
                                <option value="{{$quadratOption}}">{{$quadratOption}}</option>
                            @endforeach
                        </select>
                    </td>
                    <td></td>
                    <td>
                        <select class="fs100" wire:model='ym' wire:change="search">
                            <option value="all">all</option>
                            @foreach($months as $monthOption)
                                <option value="{{$monthOption}}">{{$monthOption}}</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <select class="fs100" wire:model='tag' wire:change="search">
                            <option value="all">all</option>
                            @foreach($tagOptions as $tagOption)
                                <option value="{{ $tagOption }}">{{ $tagOption }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <select class="fs100" style='width: 150px;' wire:model='species' wire:change="search">
                            <option value="all">all</option>
                            @foreach($speciesOptions as $speciesOption)
                                <option value="{{ $speciesOption }}">{{ $speciesOption }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <select class="fs100"  wire:model='status' wire:change="search">
                            <option value="all">all</option>
                            @foreach($statusOptions as $statusOption)
                                <option value="{{ $statusOption }}">{{ $statusOption }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        @include('livewire.nanjenshan.partials.numeric-filter', ['operatorModel' => 'heightOperator', 'valueModel' => 'heightValue'])
                    </td>
                    <td></td>
                    <td></td>
                    <td>
                        @include('livewire.nanjenshan.partials.numeric-filter', ['operatorModel' => 'leafEatenOperator', 'valueModel' => 'leafEatenValue'])
                    </td>
                    <td>
                        @include('livewire.nanjenshan.partials.numeric-filter', ['operatorModel' => 'leafCoveredOperator', 'valueModel' => 'leafCoveredValue'])
                    </td>
                    <td>
                        @include('livewire.nanjenshan.partials.numeric-filter', ['operatorModel' => 'diseaseSpotOperator', 'valueModel' => 'diseaseSpotValue'])
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
