<div class="flex text_outbox" style="flex-direction: column; align-items: center;">
    @include('livewire.partials.dataviewer-styles')

    <div class="text_box" style="width: min(1600px, calc(100vw - 80px)); box-sizing: border-box; margin: 0 auto; overflow-x: auto;">
        <div class="loading-container" wire:loading.class="visible">
            <div class="loading-spinner"></div>
        </div>

        <div class="experiment-viewer-tabs" role="tablist" aria-label="小苗實驗類型">
            @foreach($this->experimentOptions() as $code => $name)
                <button
                    type="button"
                    role="tab"
                    aria-selected="{{ $experiment === $code ? 'true' : 'false' }}"
                    class="experiment-viewer-tab {{ $experiment === $code ? 'is-active' : '' }}"
                    wire:click="selectExperiment('{{ $code }}')"
                >{{ $name }}</button>
            @endforeach
        </div>

        @include('livewire.partials.dataviewer-summary', [
            'title' => $this->experimentName().'資料檢視',
            'latestLabel' => '最新調查年月',
            'latestValue' => $latestSurveyPeriod,
            'total' => $total,
        ])
        <hr>
        <div style="margin-bottom: 8px; color: #555; font-size: .95em;">
            可點選調查日期、{{ $experiment === 'greenhouse' ? '處理' : '樣區' }}、編號及種類欄位標題排序；篩選條件只套用於目前的實驗類型。
        </div>

        @include('livewire.partials.dataviewer-pagination')

        <table
            id="fs-seedling-experiment-dataviewer-table"
            class="tablesorter dataviewer-table"
            wire:key="fs-seedling-experiment-{{ $experiment }}-{{ $plot }}-{{ $surveyPeriod }}-{{ md5($individualNo) }}-{{ $species }}-{{ $status }}-{{ $sortField }}-{{ $sortDirection }}-{{ $page }}-{{ count($data) }}"
        >
            <thead>
                <tr>
                    <th>調查年月</th>
                    <th>@include('livewire.partials.dataviewer-sortable-heading', ['field' => 'survey_date', 'label' => '調查日期'])</th>
                    <th>@include('livewire.partials.dataviewer-sortable-heading', ['field' => 'plot', 'label' => $experiment === 'greenhouse' ? '處理' : 'Plot'])</th>
                    @if($experiment === 'groundcover_phase2')
                        <th>Density</th>
                    @endif
                    <th>@include('livewire.partials.dataviewer-sortable-heading', ['field' => 'individual_no', 'label' => 'No.'])</th>
                    <th>@include('livewire.partials.dataviewer-sortable-heading', ['field' => 'species', 'label' => '種類'])</th>
                    <th>長度</th>
                    @if($experiment === 'groundcover_phase2')
                        <th>子葉數</th>
                    @endif
                    <th>葉片數</th>
                    <th>Status</th>
                    <th style="min-width: 220px;">備註</th>
                </tr>
                <tr>
                    <td>@include('livewire.partials.select-filter', ['model' => 'surveyPeriod', 'options' => $periods])</td>
                    <td></td>
                    <td>@include('livewire.partials.select-filter', ['model' => 'plot', 'options' => $plots])</td>
                    @if($experiment === 'groundcover_phase2')
                        <td></td>
                    @endif
                    <td>
                        <input type="text" class="fs100" style="width: 82px;" wire:model="individualNo" wire:change="search" wire:keydown.enter="search" placeholder="all">
                    </td>
                    <td>
                        @include('livewire.partials.select-filter', ['model' => 'species', 'options' => $speciesOptions])
                    </td>
                    <td>@include('livewire.partials.numeric-filter', ['operatorModel' => 'lengthOperator', 'valueModel' => 'lengthValue'])</td>
                    @if($experiment === 'groundcover_phase2')
                        <td></td>
                    @endif
                    <td></td>
                    <td>@include('livewire.partials.select-filter', ['model' => 'status', 'options' => $statusOptions])</td>
                    <td></td>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $row)
                    @php
                        $isNewPeriod = !$loop->first && $row['survey_period'] !== $data[$loop->index - 1]['survey_period'];
                    @endphp
                    <tr wire:key="fs-seedling-experiment-row-{{ $row['id'] }}" @if($isNewPeriod) style="border-top: 3px solid #6f7f18;" @endif>
                        <td>{{ $row['survey_period'] }}</td>
                        <td>{{ $row['survey_date'] }}</td>
                        <td>{{ $row['plot'] }}</td>
                        @if($experiment === 'groundcover_phase2')
                            <td>{{ $row['density'] }}</td>
                        @endif
                        <td>{{ $row['individual_no'] }}</td>
                        <td>{{ $row['species'] }}</td>
                        <td>{{ $row['length'] }}</td>
                        @if($experiment === 'groundcover_phase2')
                            <td>{{ $row['cotyledon_count'] }}</td>
                        @endif
                        <td>{{ $row['leaf_count'] }}</td>
                        <td>{{ $row['status'] }}</td>
                        <td>{{ $row['note'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $experiment === 'groundcover_phase2' ? 11 : 9 }}" style="padding: 24px; text-align: center; color: #66725d;">目前沒有符合條件的資料。</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
