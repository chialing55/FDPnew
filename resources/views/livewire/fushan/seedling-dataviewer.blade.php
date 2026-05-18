
<div class='flex text_outbox' style='flex-direction: column; align-items: center;'>
    <div class='text_box' style='margin: 0 auto;'>
        <div class="loading-container" wire:loading.class="visible">
            <div class="loading-spinner"></div>
        </div>
        <h2>福山小苗資料檢視<span style="margin-left: 20px ; font-weight: 500; font-size: 70%;">共 {{ $total }} 筆</span></h2>
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
            id='fs-seedling-dataviewer-table'
            class='tablesorter'
            wire:key="fs-seedling-dataviewer-{{ $trap }}-{{ $plot }}-{{ $ym }}-{{ md5($mtag) }}-{{ md5($tag) }}-{{ md5($species) }}-{{ $status }}-{{ $recruit }}-{{ $sprout }}-{{ $page }}-{{ count($data) }}"
        >
            <thead>
                <tr>
                    <th>trap</th>
                    <th>plot</th>
                    <th>census</th>
                    <th>調查年月</th>
                    <th>mtag</th>
                    <th>tag</th>
                    <th>種類</th>
                    <th>狀態</th>
                    <th>長度</th>
                    <th>葉片數</th>
                    <th>新舊</th>
                    <th>萌櫱</th>
                    <th>x</th>
                    <th>y</th>
                    <th style='width: 220px;'>note</th>
                </tr>
                <tr>
                    <td>
                        <select class="fs100" wire:model='trap' wire:change="search">
                            <option value="all">all</option>
                            @foreach($traps as $trapOption)
                                <option value="{{$trapOption}}">{{$trapOption}}</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <select class="fs100" wire:model='plot' wire:change="search">
                            <option value="all">all</option>
                            @foreach($plots as $plotOption)
                                <option value="{{$plotOption}}">{{$plotOption}}</option>
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
                        <select class="fs100" wire:model='mtag' wire:change="search">
                            <option value="all">all</option>
                            @foreach($mtagOptions as $mtagOption)
                                <option value="{{ $mtagOption }}">{{ $mtagOption }}</option>
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
                        <select class="fs100" wire:model='status' wire:change="search">
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
                    <td>
                        <select class="fs100" wire:model='recruit' wire:change="search">
                            <option value="all">all</option>
                            @foreach($recruitOptions as $recruitOption)
                                <option value="{{ $recruitOption }}">{{ $recruitOption }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <select class="fs100" wire:model='sprout' wire:change="search">
                            <option value="all">all</option>
                            @foreach($sproutOptions as $sproutOption)
                                <option value="{{ $sproutOption }}">{{ $sproutOption }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $row)
                    @php
                        $isNewPlot = !$loop->first && ($row['trap'] !== $data[$loop->index - 1]['trap'] || $row['plot'] !== $data[$loop->index - 1]['plot']);
                    @endphp
                    <tr
                        wire:key="fs-seedling-row-{{ $row['trap'] }}-{{ $row['plot'] }}-{{ $row['census'] }}-{{ $row['tag'] }}"
                        @if($isNewPlot) style="border-top: 3px solid #6f7f18;" @endif
                    >
                        <td>{{$row['trap']}}</td>
                        <td>{{$row['plot']}}</td>
                        <td>{{$row['census']}}</td>
                        <td>{{$row['ym']}}</td>
                        <td>{{$row['mtag']}}</td>
                        <td>{{$row['tag']}}</td>
                        <td>{{$row['species']}}</td>
                        <td>{{$row['status']}}</td>
                        <td>{{$row['height']}}</td>
                        <td>{{$row['leaf']}}</td>
                        <td>{{$row['recruit']}}</td>
                        <td>{{$row['sprout']}}</td>
                        <td>{{$row['x']}}</td>
                        <td>{{$row['y']}}</td>
                        <td>{{$row['note']}}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
