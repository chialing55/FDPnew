@extends('layouts.seedling-experiment')

@section('workspace')
    @php
        $pages = [
            'overview' => ['title' => '福山 小苗實驗', 'description' => '請由上方工作列選擇要進行的作業。'],
            'documents' => ['title' => '相關文件', 'description' => '下載小苗實驗調查使用的記錄紙與相關文件。'],
            'upload' => [
                'title' => '上傳資料',
                'description' => '請上傳地被實驗第一期、第二期或溫室實驗原始資料；修改後資料由系統內部產生。',
            ],
            'entry-guide' => [
                'title' => '資料輸入注意事項',
                'description' => '此頁將放置小苗實驗的欄位定義、輸入規則與操作流程。',
            ],
            'groundcover-phase2' => [
                'title' => '地被實驗第二期',
                'description' => '此頁將提供地被實驗第二期資料的新增與編輯介面。',
            ],
            'greenhouse' => ['title' => '溫室實驗', 'description' => '此頁將提供溫室實驗資料的新增與編輯介面。'],
            'data' => ['title' => '資料檢視', 'description' => '此頁將提供小苗實驗資料的查詢、篩選與檢視。'],
            'download' => ['title' => '資料下載', 'description' => '此頁將提供小苗實驗資料的下載條件與檔案輸出。'],
        ];
        $content = $pages[$page] ?? $pages['overview'];
    @endphp

    <div class="text_outbox flex" style="flex-direction: column; align-items: center;">
        @if ($page === 'documents')
            <div class="text_outbox flex"
                style="flex-direction:column; align-items:center; justify-content:flex-start; gap:18px;">
                @include('pages.fushan.seedling-experiment.partials.record-paper-card', [
                    'summary' => $recordPaper,
                    'experiment' => 'groundcover_phase2',
                ])
                @include('pages.fushan.seedling-experiment.partials.record-paper-card', [
                    'summary' => $greenhouseRecordPaper,
                    'experiment' => 'greenhouse',
                ])
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
                    <div class="app-feedback-note app-feedback-note--success" style="margin-top:18px;">{{ session('status') }}
                    </div>
                @endif
                @if ($errors->any())
                    <div class="app-feedback-note app-feedback-note--error" style="margin-top:18px;">
                        {{ $errors->first() }}
                    </div>
                @endif

                @php
                    $uploadCards = [
                        'phase1_raw' => [
                            'title' => '地被實驗第一期｜原始資料',
                            'columns' => 'Date、Plot、No.、Species、長度、葉片數、Status、備註',
                            'button' => '上傳、整理並匯入正式資料',
                        ],
                        'phase2_raw' => [
                            'title' => '地被實驗第二期｜原始資料',
                            'columns' =>
                                '調查順序排序、Date、Plot、Density、No.、Species、長度、子葉數、葉片數、Status、備註',
                            'button' => '上傳、整理並匯入正式資料',
                        ],
                        'greenhouse_deployment' => [
                            'title' => '溫室實驗｜原始資料',
                            'columns' =>
                                '調查(輸入)順序、遮蔭處理排序、Date、Sp.、Treatment、No.、長度、葉片數、Status、備註',
                            'button' => '上傳、整理並匯入正式資料',
                        ],
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
                        <button type="submit"
                            onclick="this.disabled=true; this.form.submit();">{{ $card['button'] ?? '上傳並匯入' }}</button>
                    </form>
                @endforeach

            </div>
        @elseif ($page === 'data')
            @livewire('fushan.seedling-experiment-dataviewer')
        @elseif ($page === 'download')
            @php
                $downloads = [
                    'groundcover_phase1' => [
                        'title' => '地被實驗第一期',
                        'columns' => 'Date、Plot、No.、Species、長度、葉片數、Status、備註',
                    ],
                    'groundcover_phase2' => [
                        'title' => '地被實驗第二期',
                        'columns' =>
                            '調查順序排序、Date、Plot、Density、No.、Species、長度、子葉數、葉片數、Status、備註',
                    ],
                    'greenhouse' => [
                        'title' => '溫室實驗',
                        'columns' =>
                            '調查(輸入)順序、遮蔭處理排序、Date、Sp.、Treatment、No.、長度、葉片數、Status、備註',
                    ],
                ];
            @endphp

            <div class="text_outbox flex" style="flex-direction:column; align-items:center;">
                <section class="text_box" style="width:min(1100px, 100%); box-sizing:border-box; overflow-x:auto;">
                    <h2>小苗實驗資料下載</h2>
                    <hr>
                    <table class="tablesorter" style="min-width:760px;">
                        <thead>
                            <tr>
                                <th>資料表</th>
                                <th>資料範圍</th>
                                <th>說明</th>
                                <th>下載</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($downloads as $experiment => $download)
                                <tr>
                                    <td>{{ $download['title'] }}</td>
                                    <td>全部資料</td>
                                    <td>{{ $download['columns'] }}</td>
                                    <td>
                                        <form method="GET"
                                            action="{{ route('admin.fushan.seedling-experiment.download.txt', ['experiment' => $experiment]) }}"
                                            style="margin:0;">
                                            <button type="submit">下載 txt</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <x-seedling-experiment.download-note />
                </section>
            </div>
        @elseif ($page === 'groundcover-phase2')
            @livewire('fushan.seedling-experiment-entry')
        @elseif ($page === 'greenhouse')
            @livewire('fushan.seedling-experiment-entry', ['experimentCode' => 'greenhouse'])
        @elseif ($page === 'entry-guide')
            <div>
                <h2>小苗實驗資料輸入注意事項</h2>
                <div class="note">
                    <ul style="font-weight:800;">
                        <li>開始輸入前，請先選擇本次實際調查日期。</li>
                        <li>每個分組輸入後都要按「儲存」，才能確實將資料儲存。</li>
                        <li>新一期預設 Status 為 A；前一期為 D 者，長度、葉片數及 Status 預設為 -4、-4、D。</li>
                        <li>經人工確認已為 G 的個體需要恢復追蹤時，可使用「G 復活」加入本期名單。</li>

                        <li>所有個體儲存完成後，請按「確認本月輸入完成」；系統檢查完成後才會建立下一個調查月份。</li>
                    </ul>

                    <div class="text_outbox flex" style="flex-direction:column;">
                        <div class="text_box_note_out" style="width:100%;">
                            <div class="text_box text_box_note">
                                <h2>共同操作</h2>
                                <ol>
                                    <li><b>調查月份</b>－原則為每月調查一次；系統依目前調查月份顯示固定名單。</li>
                                    <li><b>調查順序</b>－系統依調查順序顯示處理或樣區及個體，不需另外選擇樣站。</li>
                                    <li><b>Date</b>本次實際調查日期。</li>
                                    <li><b>Status</b></li>
                                    <ul>
                                        <li> A: 個體存活。填寫本次實際量測值。</li>
                                        <li> D: 個體死亡，仍有看到殘餘的枝幹。系統將自動帶入 -4。於下次調查時保留資料，以避免判斷錯誤。</li>
                                        <li> G: 個體死亡，枝幹已消失。系統將自動帶入 -4。結束追蹤。如再出現，可於輸入介面選取復原。</li>
                                    </ul>

                                    <li><b>儲存</b>－系統逐筆檢查並儲存。正確資料會先儲存；錯誤資料不會儲存，請依提示修正後再次按「儲存」。長度留白視為尚未輸入，該次儲存直接跳過。</li>
                                    <li><b>完成調查</b>－所有個體都完成儲存後，才可確認本月輸入完成。</li>
                                </ol>
                            </div>
                        </div>

                        <div class="text_box_note_out" style="width:100%;">
                            <div class="text_box text_box_note">
                                <h2>地被實驗第二期資料輸入</h2>
                                <ol>
                                    <li>資料依樣區組分頁，欄位為 Date、Plot、Density、No.、Species、長度、子葉數、葉片數、Status 與備註。</li>
                                    <li><b>香楠</b>－子葉數必須為 0。</li>
                                    <li><b>黃杞</b>－子葉數最多為 2，且不可比前一次增加。</li>
                                </ol>
                            </div>
                        </div>

                        <div class="text_box_note_out" style="width:100%;">
                            <div class="text_box text_box_note">
                                <h2>溫室實驗資料輸入</h2>
                                <ol>
                                    <li>資料依 Treatment 分頁，Treatment 與個體皆依調查順序排列。</li>
                                    <li>欄位為 Date、Treatment、No.、Species、長度、葉片數、Status 與備註。</li>

                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
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
