@php
    $isGreenhouse = $experiment === 'greenhouse';
    $title = $isGreenhouse ? '溫室實驗記錄紙' : '地被實驗第二期記錄紙';
    $statusKey = $isGreenhouse ? 'greenhouse_status' : 'status';
    $errorKey = $isGreenhouse ? 'greenhouse_record_paper' : 'record_paper';
    $prepareRoute = $isGreenhouse
        ? 'admin.fushan.seedling-experiment.greenhouse-record-paper.prepare'
        : 'admin.fushan.seedling-experiment.record-paper.prepare';
    $downloadRoute = $isGreenhouse
        ? 'admin.fushan.seedling-experiment.greenhouse-record-paper.download'
        : 'admin.fushan.seedling-experiment.record-paper.download';
    $filePrefix = $isGreenhouse ? '溫室實驗' : '小苗試驗';
@endphp

<div class="text_box" style="width:min(900px, 100%); box-sizing:border-box;">
    <h2>{{ $title }}</h2>
    <hr>
    <p>記錄紙依調查順序列出本期仍需追蹤的小苗，並帶入前一次調查的高度、葉片數、Status 與備註。</p>
    <p>批次建立後名單即固定；同一份記錄紙可以重複下載。</p>

    @if (session($statusKey))
        <div class="app-feedback-note app-feedback-note--success" style="margin:18px 0;">{{ session($statusKey) }}</div>
    @endif
    @if ($errors->has($errorKey))
        <div class="app-feedback-note app-feedback-note--error" style="margin:18px 0;">{{ $errors->first($errorKey) }}</div>
    @endif

    @if (!($summary['available'] ?? false))
        <div class="app-feedback-note app-feedback-note--error" style="margin-top:18px;">
            {{ $summary['message'] ?? '目前無法產生記錄紙。' }}
        </div>
    @elseif ($summary['active_survey'] ?? null)
        @php [$surveyYear, $surveyMonth] = explode('-', $summary['active_survey']->survey_period); @endphp
        <p>
            調查月份：<strong>{{ $surveyYear }} 年 {{ (int) $surveyMonth }} 月</strong><br>
            固定名單：{{ number_format($summary['active_survey_count']) }} 株
        </p>
        <a class="button1" href="{{ route($downloadRoute, ['survey' => $summary['active_survey']->id]) }}">
            下載{{ $filePrefix }}紀錄{{ str_replace('-', '', $summary['active_survey']->survey_period) }}紀錄紙.xlsx
        </a>
    @elseif ($summary['next_period'] ?? null)
        @php [$nextYear, $nextMonth] = explode('-', $summary['next_period']); @endphp
        <p>
            最新正式資料：{{ $summary['latest_period'] }}<br>
            下一次調查：<strong>{{ $nextYear }} 年 {{ (int) $nextMonth }} 月</strong><br>
            預計固定名單：{{ number_format($summary['eligible_count']) }} 株
        </p>

        @if (($summary['missing_order_count'] ?? 0) > 0)
            <div class="app-feedback-note app-feedback-note--error" style="margin-top:18px;">
                仍有 {{ number_format($summary['missing_order_count']) }} 株缺少調查順序，請先補齊後再產生。
            </div>
        @else
            <form method="POST" action="{{ route($prepareRoute) }}">
                @csrf
                <button type="submit" class="button1" onclick="this.disabled=true; this.form.submit();">
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
