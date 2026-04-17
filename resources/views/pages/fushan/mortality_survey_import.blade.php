@extends('layouts/mortality')

@section('pagejs')
    <script>
        $('.list4').addClass('now');
        $('.list4 hr').css('color', '#91A21C');
    </script>
@endsection

@section('rightbox')
    <div class='text_box' style="width:min(900px, 92vw); text-align:left;">
        <h2>匯入調查資料</h2>
        <hr>

        @if (session('status'))
            <div style="margin:14px 0; padding:10px 12px; border:1px solid rgba(34,197,94,.35); background:rgba(34,197,94,.12); color:#14532d; border-radius:6px;">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div style="margin:14px 0; padding:10px 12px; border:1px solid rgba(220,38,38,.35); background:rgba(220,38,38,.08); color:#991b1b; border-radius:6px;">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        @if (session('survey_import_summary'))
            @php($summary = session('survey_import_summary'))
            <div style="margin:14px 0; padding:12px; border:1px solid rgba(0,0,0,.08); background:rgba(255,255,255,.72); border-radius:6px;">
                <div style="font-weight:700; margin-bottom:8px;">匯入結果</div>
                <div>目標 census：{{ $summary['target_census'] ?? '—' }}</div>
                <div>調查年度：{{ $summary['survey_year'] ?? '—' }}</div>
                <div>資料批次：{{ $summary['data_batch'] ?? '—' }}</div>
                <div>目標資料表：{{ $summary['target_table'] ?? '—' }}</div>
                <div>本次是否新建資料表：{{ !empty($summary['created_table']) ? '是' : '否' }}</div>
                <div>匯入檔案：{{ $summary['file_name'] ?? '—' }}</div>
                <div>寫入筆數：{{ $summary['imported_count'] ?? 0 }}</div>
            </div>
        @endif

        {{-- <div style="margin:16px 0; padding:14px; border:1px solid rgba(0,0,0,.08); background:rgba(255,255,255,.78); border-radius:8px; line-height:1.8;">
            <div>最新已匯入 `census_records`：{{ $latestImportedCensus ?? '—' }}</div>
            <div>上一個 census 年度：{{ $latestCensus?->survey_year ?? '—' }}</div>
            <div>下一次要處理的 census：{{ $nextCensus?->census ?? '—' }}</div>
            <div>下一次調查年度：{{ $nextCensus?->survey_year ?? '—' }}</div>
            <div>上一批次 / 下一批次：{{ $latestCensus?->data_batch ?? '—' }} / {{ $nextCensus?->data_batch ?? '—' }}</div>
            <div style="margin-top:8px; font-weight:700; color:{{ $needsNewImport ? '#b45309' : '#14532d' }};">
                {{ $decisionMessage }}
            </div>
        </div> --}}

        @if ($nextCensus)
            <div style="margin:16px 0;">
            <p style="font-weight:700; margin-bottom:10px;">即將進行的調查 / 輸入</p>
                <table style="width:100%; border-collapse:collapse; background:rgba(255,255,255,.82);">
                    <thead>
                        <tr style="background:rgba(63,95,91,.08);">
                            <th style="padding:10px 12px; border:1px solid rgba(0,0,0,.08); text-align:center;">census</th>
                            <th style="padding:10px 12px; border:1px solid rgba(0,0,0,.08); text-align:center;">年度</th>
                            <th style="padding:10px 12px; border:1px solid rgba(0,0,0,.08); text-align:center;">資料批次</th>
                            <th style="padding:10px 12px; border:1px solid rgba(0,0,0,.08); text-align:center;">目標資料表</th>
                            <th style="padding:10px 12px; border:1px solid rgba(0,0,0,.08); text-align:center;">是否需匯入新資料</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="padding:10px 12px; border:1px solid rgba(0,0,0,.08); text-align:center;">{{ $nextCensus->census }}</td>
                            <td style="padding:10px 12px; border:1px solid rgba(0,0,0,.08); text-align:center;">{{ $nextCensus->survey_year ?? '—' }}</td>
                            <td style="padding:10px 12px; border:1px solid rgba(0,0,0,.08); text-align:center;">{{ $nextCensus->data_batch ?? '—' }}</td>
                            <td style="padding:10px 12px; border:1px solid rgba(0,0,0,.08); text-align:center;">{{ $targetTable ?? '—' }}</td>
                            <td style="padding:10px 12px; border:1px solid rgba(0,0,0,.08); text-align:center; font-weight:700; color:{{ $needsNewImport ? '#b45309' : '#14532d' }};">
                                {{ $needsNewImport ? '需要' : '不需要' }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @endif

        @if ($needsNewImport)
            <form method="POST" action="{{ route('admin.fushan.mortality.survey-import.upload') }}" enctype="multipart/form-data"
                style="margin-top:18px; padding:16px; border:1px solid rgba(0,0,0,.08); background:rgba(255,255,255,.78); border-radius:8px;">
                @csrf
                <div style="font-weight:700; margin-bottom:10px;">上傳並匯入新資料</div>
                <div style="margin-bottom:10px; color:#475569;">
                    會匯入到 `data_batch_{{ $nextCensus->data_batch }}`；若資料表不存在，系統會自動建立。
                </div>
                <div style="margin-bottom:10px; color:#475569;">
                    空白分頁列會讓 `page + 1`，`DBH(old)` 會寫入 `dbh1`，
                    `q20 / q10 / stemid` 會依目前資料整理規則同步正規化。
                </div>

                <input type="file" name="survey_file" accept=".csv,.txt"
                    style="display:block; margin-bottom:14px; height:38px; border:1px solid rgba(0,0,0,.15); border-radius:6px; background:rgba(255,255,255,.9); padding:12px 12px 0 12px; cursor:pointer;">
                <button type="submit"
                    style="padding:10px 18px; border:0; border-radius:6px; background:#3f5f5b; color:#fff; cursor:pointer;">
                    匯入調查資料
                </button>
            </form>
        @else
            <div style="margin-top:18px; padding:14px; border:1px solid rgba(34,197,94,.35); background:rgba(34,197,94,.08); color:#14532d; border-radius:8px;">
                @if (!empty($targetTableHasData) && !empty($targetTable))
                    `{{ $targetTable }}` 已經完成資料上傳。
                @else
                    依目前 `data_batch` 判斷，這一次不需要匯入新的調查資料。
                @endif
            </div>
        @endif

    </div>
@endsection
