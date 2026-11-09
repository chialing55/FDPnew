@extends('layouts.seedling-experiment')

@section('workspace')
    @php
        $pages = [
            'overview' => ['title' => '福山 小苗實驗', 'description' => '請由上方工作列選擇要進行的作業。'],
            'documents' => ['title' => '相關文件', 'description' => '下載小苗實驗調查使用的記錄紙與相關文件。'],
            'upload' => ['title' => '上傳資料', 'description' => '請上傳地被實驗第一期、第二期或溫室實驗原始資料；修改後資料由系統內部產生。'],
            'entry-guide' => ['title' => '資料輸入注意事項', 'description' => '此頁將放置小苗實驗的欄位定義、輸入規則與操作流程。'],
            'groundcover-phase2' => ['title' => '地被實驗第二期', 'description' => '此頁將提供地被實驗第二期資料的新增與編輯介面。'],
            'greenhouse' => ['title' => '溫室實驗', 'description' => '此頁將提供溫室實驗資料的新增與編輯介面。'],
            'data' => ['title' => '資料檢視', 'description' => '此頁將提供小苗實驗資料的查詢、篩選與檢視。'],
            'download' => ['title' => '資料下載', 'description' => '此頁將提供小苗實驗資料的下載條件與檔案輸出。'],
        ];
        $content = $pages[$page] ?? $pages['overview'];
    @endphp

    <div class="flex text_outbox" style="flex-direction: column; align-items: center;">
        @if ($page === 'documents')
            <div class="flex text_outbox" style="align-items: flex-start; justify-content: center;">
                <div class="text_box">
                    <h2>地被實驗第二期記錄紙</h2>
                    <hr>
                    <p>記錄紙依調查順序列出本期仍需追蹤的小苗，並帶入前一次調查的高度、葉片數、Status 與備註。</p>
                    <p>批次建立後名單即固定；同一份記錄紙可以重複下載，不會因下載而重新計算名單。</p>

                    @if (session('status'))
                        <div class="app-feedback-note app-feedback-note--success" style="margin:18px 0;">{{ session('status') }}</div>
                    @endif
                    @if ($errors->has('record_paper'))
                        <div class="app-feedback-note app-feedback-note--error" style="margin:18px 0;">
                            {{ $errors->first('record_paper') }}
                        </div>
                    @endif

                    @if (!($recordPaper['available'] ?? false))
                        <div class="app-feedback-note app-feedback-note--error" style="margin-top:18px;">
                            {{ $recordPaper['message'] ?? '目前無法產生記錄紙。' }}
                        </div>
                    @elseif ($recordPaper['active_survey'] ?? null)
                        @php
                            [$surveyYear, $surveyMonth] = explode('-', $recordPaper['active_survey']->survey_period);
                        @endphp
                        <p>
                            已建立調查月份：
                            <strong>{{ $surveyYear }} 年 {{ (int) $surveyMonth }} 月</strong><br>
                            固定名單：{{ number_format($recordPaper['active_survey_count']) }} 株
                        </p>
                        <a class="button1"
                            href="{{ route('admin.fushan.seedling-experiment.record-paper.download', ['survey' => $recordPaper['active_survey']->id]) }}">
                            下載小苗試驗紀錄{{ str_replace('-', '', $recordPaper['active_survey']->survey_period) }}紀錄紙.xlsx
                        </a>
                    @elseif ($recordPaper['next_period'] ?? null)
                        @php
                            [$nextYear, $nextMonth] = explode('-', $recordPaper['next_period']);
                        @endphp
                        <p>
                            最新正式資料：{{ $recordPaper['latest_period'] }}<br>
                            下一次調查：<strong>{{ $nextYear }} 年 {{ (int) $nextMonth }} 月</strong><br>
                            預計固定名單：{{ number_format($recordPaper['eligible_count']) }} 株
                        </p>

                        @if (($recordPaper['missing_order_count'] ?? 0) > 0)
                            <div class="app-feedback-note app-feedback-note--error" style="margin-top:18px;">
                                仍有 {{ number_format($recordPaper['missing_order_count']) }} 株缺少調查順序，請先補齊後再產生。
                            </div>
                        @else
                            <form method="POST" action="{{ route('admin.fushan.seedling-experiment.record-paper.prepare') }}">
                                @csrf
                                <button type="submit" class="button1"
                                    onclick="this.disabled=true; this.form.submit();">
                                    產生 {{ $nextYear }} 年 {{ (int) $nextMonth }} 月記錄紙
                                </button>
                            </form>
                        @endif
                    @else
                        <div class="app-feedback-note app-feedback-note--error" style="margin-top:18px;">
                            尚無正式調查資料，無法判斷下一個調查月份。
                        </div>
                    @endif
                </div>
            </div>
        @elseif ($page === 'upload')
            <div class="experiment-upload-intro">
                <h2>資料上傳（限管理員）</h2>
                <p>{{ $content['description'] }}</p>
                <p>支援 XLSX、CSV、ODS，單檔上限 50 MB。原始資料會保留 Excel 值、檔名、工作表與列號。</p>

                <section class="experiment-upload-flow" aria-labelledby="upload-flow-title">
                    <h3 id="upload-flow-title">上傳處理流程</h3>
                    <ol>
                        <li>原始資料上傳完成後，完整保存至 raw 資料表。</li>
                        <li>系統自動複製資料並套用已確認的通用與各期人工修正規則，產生 cleaned 修改後資料；原始資料不會被修改。</li>
                        <li>系統依調查日期逐批建立樣區、個體及調查紀錄等正式資料。</li>
                        <li>完成後回報原始資料、修改後資料與正式資料三個階段的處理筆數。</li>
                    </ol>
                    <p>若正式匯入意外遇到不合資料，會停在該次調查；raw 與 cleaned 資料仍會保留，以便修正後續傳。</p>
                </section>

                @if (session('status'))
                    <div class="app-feedback-note app-feedback-note--success" style="margin-top:18px;">{{ session('status') }}</div>
                @endif
                @if ($errors->any())
                    <div class="app-feedback-note app-feedback-note--error" style="margin-top:18px;">
                        {{ $errors->first() }}
                    </div>
                @endif

                @php
                    $uploadCards = [
                        'phase1_raw' => ['title' => '地被實驗第一期｜原始資料', 'columns' => 'Date、Plot、No.、Species、長度、葉片數、Status、備註', 'button' => '上傳、整理並匯入正式資料'],
                        'phase2_raw' => ['title' => '地被實驗第二期｜原始資料', 'columns' => '調查順序排序、Date、Plot、Density、No.、Species、長度、子葉數、葉片數、Status、備註', 'button' => '上傳、整理並匯入正式資料'],
                        'greenhouse_order' => ['title' => '溫室實驗｜調查順序參考檔', 'columns' => '其中一次調查資料；需含調查(輸入)順序、日期、物種、處理、編號、高度、葉片、狀態、附註', 'button' => '上傳調查順序'],
                        'greenhouse_raw' => ['title' => '溫室實驗｜完整歷史資料', 'columns' => 'Date、Sp.、Treatment、No.、長度、葉片數、Status、附註；本機整理完成後，也可上傳含調查順序的完整檔', 'button' => '上傳、合併排序並匯入正式資料'],
                    ];
                @endphp

            </div>

            <div class="experiment-upload-grid">
                @foreach ($uploadCards as $dataset => $card)
                    <form class="text_box" method="POST"
                        action="{{ route('admin.fushan.seedling-experiment.upload.store', ['dataset' => $dataset]) }}"
                        enctype="multipart/form-data">
                        @csrf
                        <h2>{{ $card['title'] }}</h2>
                        <hr>
                        <div class="experiment-upload-meta">目前已有 {{ number_format($uploadCounts[$dataset] ?? 0) }} 筆</div>
                        <p>{{ $card['columns'] }}</p>
                        <input type="file" name="spreadsheet" accept=".xlsx,.csv,.ods" required>
                        <button type="submit" onclick="this.disabled=true; this.form.submit();">{{ $card['button'] ?? '上傳並匯入' }}</button>
                    </form>
                @endforeach

            </div>

            @if (($uploadCounts['greenhouse_raw'] ?? 0) > 0)
                <div style="width:min(1250px, calc(100% - 80px)); margin:18px auto 0; text-align:right;">
                    <a class="button" href="{{ route('admin.fushan.seedling-experiment.greenhouse-merged.download') }}">下載含調查順序的溫室完整 CSV</a>
                </div>
            @endif
        @elseif ($page === 'data')
            @livewire('fushan.seedling-experiment-dataviewer')
        @elseif ($page === 'groundcover-phase2')
            @livewire('fushan.seedling-experiment-entry')
        @elseif ($page === 'entry-guide')
            <div class="text_box" style="width:min(900px, 100%); box-sizing:border-box; text-align:left;">
                <h2>小苗實驗資料輸入注意事項</h2>
                <hr>
                <ol>
                    <li>調查原則為每月一次；開始輸入前先選擇本次實際調查日期。</li>
                    <li>系統依調查順序逐 Plot 顯示固定名單，不需另外選擇樣站。</li>
                    <li>Status 為 A 時，請輸入本次實際量測的長度、子葉數及葉片數。</li>
                    <li>Status 為 D 或 G 時，量測欄位皆為 -4；選擇狀態後系統會自動帶入。</li>
                    <li>新一期預設 Status 為 A、子葉數為 0；前一期為 D 者延續顯示 -4、-4、-4、D。</li>
                    <li>D 表示仍看到個體或屍體，下個月繼續調查；G 表示完全消失，本次後結束追蹤。</li>
                    <li>每個 Plot 輸入後都要按「儲存」；長度留白的資料會跳過，其餘資料逐筆儲存，單筆錯誤不影響其他正確資料。</li>
                    <li>所有個體完成儲存後才能確認本月完成；系統隨後自動建立下一個月份批次。</li>
                </ol>
            </div>
        @else
            <div class="text_box" style="width:min(900px, calc(100vw - 140px)); box-sizing:border-box; text-align:left;">
                <h2>{{ $content['title'] }}</h2>
                <hr>
                <p>{{ $content['description'] }}</p>
                <p>工作介面已建立；資料欄位與實際操作功能可在需求確認後接續加入。</p>
            </div>
        @endif
    </div>
@endsection
