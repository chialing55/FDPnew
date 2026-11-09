<div class="seedling-entry-page">
    <section class="seedling-entry-header">
        <h2>{{ $this->title }}</h2>
        <p>
            請先詳閱
            <a href="{{ route('admin.fushan.seedling-experiment.entry-guide') }}"><strong>資料輸入注意事項</strong></a>
        </p>

        <div class="seedling-entry-meta">
            <span>輸入者：{{ $userName }}</span>
            <span>輸入日期：{{ $inputDate }}</span>
        </div>

        @if ($message !== '')
            <div class="app-feedback-note {{ $messageIsError ? 'app-feedback-note--error' : 'app-feedback-note--success' }}">
                {{ $message }}
            </div>
        @endif
    </section>

    @if ($surveyId === null)
        <div class="text_box">
            <p>目前沒有可輸入的調查批次，請先至相關文件確認記錄紙批次。</p>
        </div>
    @elseif ($surveyStatus === 'prepared')
        <section class="text_box seedling-entry-start">
            <h2>開始輸入</h2>
            <hr>
            <p>本次調查期：{{ $surveyPeriod }}，固定名單共 {{ number_format($totalCount) }} 株。</p>
            <form wire:submit="startInput">
                <label for="seedling-default-survey-date">調查日期</label>
                <input id="seedling-default-survey-date" type="date" wire:model="defaultSurveyDate">
                @error('defaultSurveyDate')
                    <div class="app-feedback-note app-feedback-note--error">{{ $message }}</div>
                @enderror
                <button type="submit" class="button1">開始輸入</button>
            </form>
            <p class="seedling-entry-hint">日期會先帶入所有資料列；若部分 Plot 在其他日期調查，可在表格內個別修改。</p>
        </section>
    @elseif ($surveyStatus === 'in_progress')
        @if ($this->currentPlot)
            <div class="seedling-entry-site-selector">
                <p>
                    <strong>選擇要輸入的樣區組</strong>
                    <select onchange="DataEntryGrid.navigate('seedling-experiment-entry-hot', this.value); this.value='{{ $currentPlotIndex }}';">
                        @foreach ($plots as $index => $plot)
                            <option value="{{ $index }}" @selected($index === $currentPlotIndex)>
                                {{ $plot['group'] }}{{ $plot['is_complete'] ? '（已完成）' : '' }}
                            </option>
                        @endforeach
                    </select>

                    <span class="seedling-entry-site-navigation">
                        @if ($currentPlotIndex > 0)
                            <a class="a_" onclick="DataEntryGrid.navigate('seedling-experiment-entry-hot', {{ $currentPlotIndex - 1 }})">上一個</a>
                        @else
                            <span class="seedling-entry-hidden-nav">上一個</span>
                        @endif

                        @if ($currentPlotIndex < count($plots) - 1)
                            <a class="a_" onclick="DataEntryGrid.navigate('seedling-experiment-entry-hot', {{ $currentPlotIndex + 1 }})">下一個</a>
                        @else
                            <span class="seedling-entry-hidden-nav">下一個</span>
                        @endif
                    </span>
                </p>
            </div>

            <section class="text_box seedling-entry-table-panel">
                <h6>小苗調查資料｜{{ $this->currentPlot['group'] }}（{{ implode('、', $this->currentPlot['plots']) }}）</h6>
                <hr>

                <div id="simplenote" class="text_box2">
                    <ul>
                        <li><b>輸入資料後需按 <button class="datasavebutton" style="width:auto;" disabled>儲存</button>，才能確實將資料儲存。</b></li>
                        <li>每筆資料分別儲存；長度留白的資料視為尚未輸入並跳過，其他資料不會因此退回。</li>
                        <li>新一期預設 Status 為 <b>A</b>、子葉數為 <b>0</b>；前一期為 <b>D</b> 者保留 -4、-4、-4、D。</li>
                        <li>Status 為 <b>D</b> 或 <b>G</b> 時，長度、子葉數與葉片數會自動填入 -4。</li>
                        <li>Status 為 <b>A</b> 時，請輸入本次實際量測值。</li>
                        <li>香楠子葉數必須為 <b>0</b>；黃杞最多為 <b>2</b>，且不可比上次增加。</li>
                    </ul>
                </div>

                <div class="seedling-entry-revival">
                    <strong>G 復活</strong>
                    @if ($gRevivalCandidates !== [])
                        <select wire:model="gRevivalIndividualId">
                            <option value="">請選擇本組已 G 的小苗</option>
                            @foreach ($gRevivalCandidates as $candidate)
                                <option value="{{ $candidate['id'] }}">{{ $candidate['label'] }}</option>
                            @endforeach
                        </select>
                        <button type="button" class="button1" wire:click="reviveG">加入本期輸入</button>
                    @else
                        <span>本組目前沒有可恢復追蹤的 G 個體。</span>
                    @endif
                </div>

                <div class="entrytablediv">
                    <x-data-entry.handsontable id="seedling-experiment-entry-hot" save-label="儲存" />
                </div>
            </section>
        @endif

        @if ($this->isLastPlotGroup)
            <section class="seedling-entry-complete">
                @if ($this->canComplete)
                    <button type="button" class="button1"
                        wire:click="completeSurvey"
                        wire:loading.attr="disabled"
                        wire:confirm="確認本月所有資料皆已輸入完成？完成後會自動建立下一個月份的調查批次。">
                        確認本月輸入完成
                    </button>
                @else
                    <button type="button" class="button1" disabled>確認本月輸入完成</button>
                    <p>所有 {{ number_format($totalCount) }} 株完成儲存後才能確認完成。</p>
                @endif
            </section>
        @endif
    @endif
</div>
