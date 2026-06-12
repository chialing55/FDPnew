@extends('layouts/mortality')

@section('pagejs')
    <script>
        $('.list1').addClass('now');
        $('.list1 hr').css('color', '#91A21C');
    </script>
@endsection

@section('rightbox')
    <style>
        .workflow-role-title {
            margin: 16px 0 6px;
            font-size: 20px;
            line-height: 1.35;
            font-weight: 700;
            text-decoration: none;
        }

        .record-paper-button {
            display: inline-block;
            padding: 7px 14px;
            border-radius: 6px;
            border: 0;
            background: #5f857b;
            color: #fff !important;
            text-decoration: none !important;
            transition: background-color 0.2s cubic-bezier(0.3, 0, 0.5, 1);
            font-size: 14px;
            font-weight: 500;
            line-height: 15px;
            cursor: pointer;
        }

        .record-paper-button:hover,
        .record-paper-button:active,
        .record-paper-button:visited {
            color: #fff !important;
            text-decoration: none !important;
        }

        .record-paper-button:hover {
            background: #52756d;
        }

        .record-paper-button:disabled {
            background: #d1d5db;
            color: #6b7280 !important;
            cursor: default;
        }

        .record-paper-hint {
            margin-top: 8px;
            color: #6b7280;
            font-size: 14px;
        }

        .record-paper-alert {
            margin: 8px 0 0;
            color: #92400e;
            font-size: 14px;
        }
    </style>
    <div class='text_outbox flex'>
        <div class='text_box'>
            <h2>死亡率調查相關文件</h2>
            <hr>
            @if(session('status'))
                <div class="record-paper-alert">{{ session('status') }}</div>
            @endif
            <div style="margin-top: 12px;">
                @if(!empty($canDownloadRecordPaper))
                    <a class="record-paper-button" href="{{ route('admin.fushan.mortality.record-paper') }}">下載 {{ $nextSurveyYear }} 年紀錄紙</a>
                @else
                    <button type="button" class="record-paper-button" disabled>下載 {{ $nextSurveyYear }} 年紀錄紙</button>
                    <div class="record-paper-hint">{{ $recordPaperDownloadMessage ?? '目前無法下載紀錄紙。' }}</div>
                @endif
            </div>
        </div>

        <div class='text_box'>
            <h2>工作流程</h2>
            <hr>
            <h3 class="workflow-role-title">管理員</h3>
            <ol>
                <li>先在<a href="{{ route('admin.fushan.mortality.census') }}">調查年度</a>確認本次調查與年份。</li>
                <li>到<a href="{{ route('admin.fushan.mortality.survey-import') }}">匯入調查資料</a>上傳新資料或確認本次調查清單。</li>
                @if ((int) (auth()->user()?->is_admin ?? 0) === 1)
                    <li>到<a class="admin-only-body-link"
                            href="{{ route('admin.fushan.mortality.import') }}">資料處理</a>頁面下方產生最新輸入表單。</li>
                @else
                    <li>請管理員產生最新輸入表單。</li>
                @endif
            </ol>

            <h3 class="workflow-role-title">資料輸入者</h3>
            <ol>
                <li>正式輸入前，先閱讀<a href="{{ route('admin.fushan.mortality.note') }}">輸入注意事項</a>。</li>
                <li>完成<a href="{{ route('admin.fushan.mortality.entry.1') }}">第一次輸入</a>與<a
                        href="{{ route('admin.fushan.mortality.entry.2') }}">第二次輸入</a>。</li>
                <li>完成兩次輸入後，到<a href="{{ route('admin.fushan.mortality.compare') }}">資料比對</a>確認差異。</li>
                <li>通知資料管理員。</li>
            </ol>

            <h3 class="workflow-role-title">管理員</h3>
            <ol>
                @if ((int) (auth()->user()?->is_admin ?? 0) === 1)
                    <li>確認無誤後，到<a class="admin-only-body-link" href="{{ route('admin.fushan.mortality.import') }}">資料處理</a>將
                        record1 匯入大表。</li>
                    <li>檢查是否需更新 stem 的基本資料。</li>
                    <li>更新後可產生下一年度資料表，或是匯入新一期資料。</li>
                @else
                    <li>確認無誤後，由管理員將 record1 匯入大表。</li>
                    <li>檢查是否需更新 stem 的基本資料。</li>
                    <li>更新後可產生下一年度資料表，或是匯入新一期資料。</li>
                @endif
            </ol>
        </div>
    </div>
@endsection
