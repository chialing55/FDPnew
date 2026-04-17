@extends('layouts/mortality')

@section('pagejs')
    <script>
        $('.list3').addClass('now');
        $('.list3 hr').css('color', '#91A21C');

        document.addEventListener('DOMContentLoaded', function() {
            const importButton = document.getElementById('census-record-import-button');
            const importHint = document.getElementById('census-record-import-hint');
            const existingCensusImports = @json($existingCensusImports ?? []);
            const derivedProcessCensus = {{ $derivedProcessCensus ?? 'null' }};
            const commentsRemaining = {{ empty($commentsRemaining) ? 'false' : 'true' }};
            const readyToImport = {{ empty($readyToImportRecords) ? 'false' : 'true' }};
            const censusValue = Number.isInteger(derivedProcessCensus) ? derivedProcessCensus : null;
            const alreadyImported = Number.isInteger(censusValue) && existingCensusImports.includes(censusValue);

            if (!importButton || !importHint) {
                return;
            }

            if (censusValue === null) {
                importButton.disabled = true;
                importButton.style.background = '#d1d5db';
                importButton.style.color = '#6b7280';
                importButton.style.cursor = 'not-allowed';
                importHint.textContent = '目前無法由 `import_stage.date` 自動判斷對應 census，按鈕已停用。';
                return;
            }

            if (!readyToImport) {
                importButton.disabled = true;
                importButton.style.background = '#d1d5db';
                importButton.style.color = '#6b7280';
                importButton.style.cursor = 'not-allowed';
                importHint.textContent = '請先確認 `stemid` 與 `team_id` 都已完成整理後再匯入。';
                return;
            }

            if (commentsRemaining) {
                importButton.disabled = true;
                importButton.style.background = '#d1d5db';
                importButton.style.color = '#6b7280';
                importButton.style.cursor = 'not-allowed';
                importHint.textContent = '請先完成 `comments` 整理並寫入 `comments_json` 後再匯入。';
                return;
            }

            if (alreadyImported) {
                importButton.disabled = true;
                importButton.style.background = '#d1d5db';
                importButton.style.color = '#6b7280';
                importButton.style.cursor = 'not-allowed';
                importHint.textContent = `census ${censusValue} 的 \`census_records\` 已存在，按鈕已停用。`;
                return;
            }

            importButton.disabled = false;
            importButton.style.background = '#3f5f5b';
            importButton.style.color = '#fff';
            importButton.style.cursor = 'pointer';
            importHint.textContent = '';
        });
    </script>
@endsection

@section('rightbox')
    <div class='text_box' style="width:min(900px, 92vw); text-align:left;">
        <h1>死亡率調查資料處理</h1>
        <hr>

        @if (session('status'))
            <div style="margin:14px 0; padding:10px 12px; border:1px solid rgba(34,197,94,.35); background:rgba(34,197,94,.12); color:#14532d; border-radius:6px;">
                {{ session('status') }}
            </div>
        @endif

        @if (session('process_summary'))
            @php($summary = session('process_summary'))
            <div style="margin:14px 0; padding:12px; border:1px solid rgba(0,0,0,.08); background:rgba(255,255,255,.72); border-radius:6px;">
                <div style="font-weight:700; margin-bottom:8px;">處理結果</div>
                <div>`map = '='` 更新筆數：{{ $summary['updated_map_count'] ?? 0 }}</div>
                <div>`q20` 拆成 `qx/qy` 更新筆數：{{ $summary['updated_q20_split_count'] ?? 0 }}</div>
                <div>`q10` 拆成 `subqx/subqy` 更新筆數：{{ $summary['updated_q10_split_count'] ?? 0 }}</div>
                <div>`stemid` 正規化更新筆數：{{ $summary['updated_stemid_count'] ?? 0 }}</div>
                <div style="margin-top:8px; color:#92400e;">`q20` 格式異常或空值筆數：{{ $summary['invalid_q20_count'] ?? 0 }}</div>
            </div>
        @endif

        @if (session('people_process_summary'))
            @php($peopleSummary = session('people_process_summary'))
            <div style="margin:14px 0; padding:12px; border:1px solid rgba(0,0,0,.08); background:rgba(255,255,255,.72); border-radius:6px;">
                <div style="font-weight:700; margin-bottom:8px;">調查者處理結果</div>
                <div>使用 census：{{ $peopleSummary['census'] ?? '-' }}</div>
                <div>新增 `people` 筆數：{{ $peopleSummary['created_people_count'] ?? 0 }}</div>
                <div>新增 `teams` 筆數：{{ $peopleSummary['created_team_count'] ?? 0 }}</div>
                <div>回填 `import_stage.team_id` 筆數：{{ $peopleSummary['updated_stage_count'] ?? 0 }}</div>
            </div>
        @endif

        @if (session('tree_individual_process_summary'))
            @php($treeSummary = session('tree_individual_process_summary'))
            <div style="margin:14px 0; padding:12px; border:1px solid rgba(0,0,0,.08); background:rgba(255,255,255,.72); border-radius:6px;">
                <div style="font-weight:700; margin-bottom:8px;">tree_individuals 同步結果</div>
                <div>新增筆數：{{ $treeSummary['created_count'] ?? 0 }}</div>
                <div>更新或重新啟用筆數：{{ $treeSummary['updated_count'] ?? 0 }}</div>
                <div>改為停用筆數：{{ $treeSummary['deactivated_count'] ?? 0 }}</div>
            </div>
        @endif

        @if (session('comment_process_summary'))
            @php($commentSummary = session('comment_process_summary'))
            <div style="margin:14px 0; padding:12px; border:1px solid rgba(0,0,0,.08); background:rgba(255,255,255,.72); border-radius:6px;">
                <div style="font-weight:700; margin-bottom:8px;">Comments 整理結果</div>
                <div>已轉入 `comments_json` 筆數：{{ $commentSummary['updated_count'] ?? 0 }}</div>
                <div>因多個 comments 而跳過筆數：{{ $commentSummary['skipped_multiple_count'] ?? 0 }}</div>
                <div>因沒有對照 option 而跳過筆數：{{ $commentSummary['skipped_unmapped_count'] ?? 0 }}</div>
            </div>
        @endif

        @if (session('census_record_import_summary'))
            @php($importSummary = session('census_record_import_summary'))
            <div style="margin:14px 0; padding:12px; border:1px solid rgba(0,0,0,.08); background:rgba(255,255,255,.72); border-radius:6px;">
                <div style="font-weight:700; margin-bottom:8px;">`census_records` 匯入結果</div>
                <div>使用 census：{{ $importSummary['census'] ?? '-' }}</div>
                <div>新增 `census_records` 筆數：{{ $importSummary['created_count'] ?? 0 }}</div>
                <div>更新 `census_records` 筆數：{{ $importSummary['updated_count'] ?? 0 }}</div>
                <div>寫入 `census_record_comments` 筆數：{{ $importSummary['comment_count'] ?? 0 }}</div>
                <div>寫入 `stem_corrections` 筆數：{{ $importSummary['stem_correction_count'] ?? 0 }}</div>
                <div>因找不到 `tree_individuals` 而跳過筆數：{{ $importSummary['skipped_missing_tree_count'] ?? 0 }}</div>
                @if (!empty($importSummary['skipped_missing_tree_stemids']))
                    <div style="margin-top:8px;">跳過的 `stemid`：</div>
                    <div style="margin-left:16px; color:#92400e; line-height:1.6;">
                        {{ implode('、', $importSummary['skipped_missing_tree_stemids']) }}
                    </div>
                @endif
            </div>
        @endif

        <div style="margin-top:16px;">

            <div style="font-weight:700; margin-bottom:10px;">
                @if (!empty($derivedProcessCensus) && !empty($derivedProcessSurveyYear))
                    {{ "本次處理 census {$derivedProcessCensus}, {$derivedProcessSurveyYear} 年資料（由 import_stage 推出）" }}
                @elseif (!empty($derivedProcessCensus))
                    {{ "本次處理 census {$derivedProcessCensus}（由 import_stage 推出）" }}
                @else
                    目前無法由 import_stage 推出本次處理 census。
                @endif
            </div>
            @if (!empty($processCensusStatus))
                <div style="margin-top:8px; color:#b91c1c;">{{ $processCensusStatus }}</div>
            @endif
        </div>

        <div style="margin-top:16px;">
            <h2 style="margin-bottom:10px;">基本處理</h2>
            <ol style="padding-left:20px; line-height:1.8;">
                <li>將 `map = '='` 的資料改成 `q20` 值。</li>
                <li>將 `q20` 依逗號拆成 `qx`、`qy`。</li>
                <li>將 `q10` 符號轉成 `subqx`、`subqy`。</li>
                <li>將 `stemid` 依規則正規化成 `111111.0` / `111111.1` 這種格式。</li>
            </ol>
        </div>

        <div style="margin-top:18px; display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
        <form method="POST" action="{{ route('admin.fushan.mortality.process.basic') }}" style="margin:0;">
            @csrf
            <button type="submit"
                {{ !empty($basicProcessed) ? 'disabled' : '' }}
                onclick="{{ !empty($basicProcessed) ? 'return false;' : "return confirm('確定要執行 import_stage 的基本處理嗎？');" }}"
                style="padding:10px 18px; border:0; border-radius:6px; background:{{ !empty($basicProcessed) ? '#d1d5db' : '#3f5f5b' }}; color:{{ !empty($basicProcessed) ? '#6b7280' : '#fff' }}; cursor:{{ !empty($basicProcessed) ? 'not-allowed' : 'pointer' }};">
                執行基本處理
            </button>
        </form>

        @if (!empty($basicProcessed))
            <span style="color:#6b7280;">偵測到 `qx` 已有資料，表示基礎處理已執行，按鈕已停用。</span>
        @endif
        </div>

        <div style="margin-top:28px;">
            <h2 style="margin-bottom:20px;">tree_individuals 同步</h2>
            <ol style="padding-left:20px; line-height:1.8;">
                <li>依 `import_stage.stemid` 檢查目前追蹤清單。</li>
                <li>若 `tree_individuals` 尚無該 `stemid`，就新增並將 `is_active` 設為 `1`。</li>
                <li>若 `tree_individuals` 已有該 `stemid`，就更新基本欄位並將 `is_active` 設為 `1`。</li>
                <li>若 `tree_individuals` 內有資料，但這批 `import_stage` 中找不到，則改為 `is_active = 0`。</li>
            </ol>
        </div>

        <div style="margin-top:18px; display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
        <form method="POST" action="{{ route('admin.fushan.mortality.process.tree-individuals') }}" style="margin:0;">
            @csrf
            <button type="submit"
                {{ empty($basicProcessed) || empty($hasImportStageStemids) ? 'disabled' : '' }}
                onclick="{{ empty($basicProcessed) || empty($hasImportStageStemids) ? 'return false;' : "return confirm('確定要同步 tree_individuals 嗎？');" }}"
                style="padding:10px 18px; border:0; border-radius:6px; background:{{ empty($basicProcessed) || empty($hasImportStageStemids) ? '#d1d5db' : '#3f5f5b' }}; color:{{ empty($basicProcessed) || empty($hasImportStageStemids) ? '#6b7280' : '#fff' }}; cursor:{{ empty($basicProcessed) || empty($hasImportStageStemids) ? 'not-allowed' : 'pointer' }};">
                同步 tree_individuals
            </button>
        </form>

        @if (empty($basicProcessed))
            <span style="color:#6b7280;">請先完成基本處理後再同步。</span>
        @elseif (empty($hasImportStageStemids))
            <span style="color:#6b7280;">偵測到 `import_stage.stemid` 目前沒有資料，按鈕已停用。</span>
        @endif
        </div>

        <div style="margin-top:28px;">
            <h2 style="margin-bottom:20px;">調查者處理</h2>
            <ol style="padding-left:20px; line-height:1.8;">
                <li>將 `import_stage.people` 用 `、` 拆成個別調查者。</li>
                <li>抽出唯一人名後寫入 `people` 資料表。</li>
                <li>依每筆 `people` 的人員組合建立 `teams`、`team_members`，`census` 由 `import_stage.date` 自動判斷。</li>
                <li>將對應 `team_id` 回填到 `import_stage.team_id`。</li>
            </ol>
        </div>

        <div style="margin-top:18px; display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
        <form id="people-process-form" method="POST" action="{{ route('admin.fushan.mortality.process.people') }}" style="margin:0;">
            @csrf
            <input type="hidden" name="census" value="{{ $derivedProcessCensus ?? '' }}">
            <button type="submit"
                {{ !empty($peopleProcessed) || empty($derivedProcessCensus) ? 'disabled' : '' }}
                onclick="{{ !empty($peopleProcessed) || empty($derivedProcessCensus) ? 'return false;' : "return confirm('確定要執行調查者處理嗎？');" }}"
                style="padding:10px 18px; border:0; border-radius:6px; background:{{ !empty($peopleProcessed) || empty($derivedProcessCensus) ? '#d1d5db' : '#3f5f5b' }}; color:{{ !empty($peopleProcessed) || empty($derivedProcessCensus) ? '#6b7280' : '#fff' }}; cursor:{{ !empty($peopleProcessed) || empty($derivedProcessCensus) ? 'not-allowed' : 'pointer' }};">
                執行調查者處理
            </button>
        </form>

        @if (!empty($peopleProcessed))
            <span style="color:#6b7280;">偵測到 `team_id` 已有資料，表示調查者處理已執行，按鈕已停用。</span>
        @elseif (empty($derivedProcessCensus))
            <span style="color:#6b7280;">目前無法由 `import_stage.date` 判斷對應 census，按鈕已停用。</span>
        @endif
        </div>

        <div style="margin-top:28px;">
            <h2 style="margin-bottom:20px;">Comments 整理</h2>
            <ol style="padding-left:20px; line-height:1.8;">
                <li>將 `comments` 用 `;`、`,`、`；`、`，` 拆開，逐一對照目前所有啟用中的 `comment_options.comment_en`。</li>
                <li>只要一筆資料中的每個 comment 都能對到 `comment_id`，就整筆一起寫入 `comments_json`。</li>
                <li>格式如 `[{ "kind": "option", "comment_id": 12 }]`，若有多個則會一起存成陣列。</li>
                <li>處理成功後將原本的 `comments` 清空。</li>
                <li>若其中任一個 comment 沒有對照到 option，該筆就先跳過。</li>
            </ol>
        </div>

        <div style="margin-top:18px; display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
        <form method="POST" action="{{ route('admin.fushan.mortality.process.comments') }}" style="margin:0;">
            @csrf
            <button type="submit"
                {{ empty($commentsRemaining) ? 'disabled' : '' }}
                onclick="{{ empty($commentsRemaining) ? 'return false;' : "return confirm('確定要執行 comments 整理嗎？');" }}"
                style="padding:10px 18px; border:0; border-radius:6px; background:{{ empty($commentsRemaining) ? '#d1d5db' : '#3f5f5b' }}; color:{{ empty($commentsRemaining) ? '#6b7280' : '#fff' }}; cursor:{{ empty($commentsRemaining) ? 'not-allowed' : 'pointer' }};">
                執行 comments 整理
            </button>
        </form>

        @if (empty($commentsRemaining))
            <span style="color:#6b7280;">偵測到 `comments` 已無待整理資料，按鈕已停用。</span>
        @endif
        </div>

        <div style="margin-top:12px;">
            <a href="{{ route('admin.fushan.mortality.process.comments.review') }}"
                style="display:inline-block; padding:10px 18px; border:1px solid rgba(0,0,0,.15); border-radius:6px; background:rgba(255,255,255,.9); color:#2f3e3b; text-decoration:none;">
                開啟整理資料表
            </a>
        </div>

        <div style="margin-top:28px;">
            <h2 style="margin-bottom:20px;">匯入 `census_records`</h2>
            <ol style="padding-left:20px; line-height:1.8;">
                <li>依 `import_stage` 欄位匯入 `census_records`。</li>
                <li>將 `comments_json` 轉入 `census_record_comments`。</li>
                <li>將 `stem_corrections_json` 轉入 `stem_corrections`。</li>
                <li>`census` 由 `import_stage.date` 自動判斷。</li>
                <li>`stem_corrections.field_name` 取整理欄位，`old_value` 取原始欄位值，`new_value` 取整理時填入的 text。</li>
            </ol>
        </div>

        <div style="margin-top:18px; display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
        <form id="census-record-import-form" method="POST" action="{{ route('admin.fushan.mortality.process.census-records') }}" style="margin:0;">
            @csrf
            <input type="hidden" name="census" value="{{ $derivedProcessCensus ?? '' }}">
            <button id="census-record-import-button" type="submit"
                {{ empty($readyToImportRecords) || !empty($commentsRemaining) || empty($derivedProcessCensus) ? 'disabled' : '' }}
                onclick="if (this.disabled) { return false; } return confirm('確定要將資料匯入 census_records 嗎？');"
                style="padding:10px 18px; border:0; border-radius:6px; background:{{ empty($readyToImportRecords) || !empty($commentsRemaining) || empty($derivedProcessCensus) ? '#d1d5db' : '#3f5f5b' }}; color:{{ empty($readyToImportRecords) || !empty($commentsRemaining) || empty($derivedProcessCensus) ? '#6b7280' : '#fff' }}; cursor:{{ empty($readyToImportRecords) || !empty($commentsRemaining) || empty($derivedProcessCensus) ? 'not-allowed' : 'pointer' }};">
                匯入 `census_records`
            </button>
        </form>

        @if (empty($derivedProcessCensus))
            <span id="census-record-import-hint" style="color:#6b7280;">目前無法由 `import_stage.date` 自動判斷對應 census，按鈕已停用。</span>
        @elseif (empty($readyToImportRecords))
            <span id="census-record-import-hint" style="color:#6b7280;">請先確認 `stemid` 與 `team_id` 都已完成整理後再匯入。</span>
        @elseif (!empty($commentsRemaining))
            <span id="census-record-import-hint" style="color:#6b7280;">請先完成 `comments` 整理並寫入 `comments_json` 後再匯入。</span>
        @else
            <span id="census-record-import-hint" style="color:#6b7280;"></span>
        @endif
        </div>

        <div style="margin-top:28px;">
        <h2 style="margin-bottom:20px;">管理 `comment_other` 資料表</h2>
            <a href="{{ route('admin.fushan.mortality.process.comment-other.review') }}"
                style="display:inline-block; padding:10px 18px; border:1px solid rgba(0,0,0,.15); border-radius:6px; background:rgba(255,255,255,.9); color:#2f3e3b; text-decoration:none;">
                管理 `comment_other` 資料表{{ !empty($commentOtherRemainingCount) ? '（' . $commentOtherRemainingCount . '）' : '' }}
            </a>
        </div>
    </div>
@endsection
