@extends('layouts/mortality')

@section('pagejs')
    <script>
        $('.list3').addClass('now');
        $('.list3 hr').css('color', '#91A21C');
    </script>
@endsection

@section('rightbox')
    <div class='flex text_outbox' style='flex-direction: column; align-items: center;'>
        @if (session('status'))
            <div class='text_box' style='margin-bottom:16px;'>
                <div class="app-feedback-note app-feedback-note--success" style="margin:0;">
                    {{ session('status') }}
                </div>
            </div>
        @endif

        @livewire($site . '.mortality-import', ['user' => $user, 'site' => $site])

        <div class='text_box'>
            <h2>產生輸入表單</h2>
            <hr>
            <div style="line-height:1.8;">
                <div>本次預計輸入：census {{ $nextCensus?->census ?? '—' }}，{{ $nextCensus?->survey_year ?? '—' }} 年資料</div>
                <div>調查清單來源：{{ $targetTable ?? '—' }} 的 map_sort、map、stemid</div>
                <div>基本資料來源：fs_tree.base 的位置與 spcode</div>
                <div>物種中文名來源：fs_base.spinfo 的 csp</div>
                <div>舊 DBH / status 來源：fs_mortality.census_records 的最新資料</div>
            </div>

            <div style="margin-top:14px; color:{{ $recordTablesNeedRefresh ? '#9a3412' : '#475569' }};">
                @if (!empty($generateBlockedReason))
                    {{ $generateBlockedReason }}
                @elseif ($recordTablesMatchTargetCensus)
                    輸入表單已是本次 census，可直接前往<a href="{{ route('admin.fushan.mortality.entry.1') }}">第一次輸入</a>。
                @else
                    {{ $recordTablesStatusMessage }}
                @endif
            </div>

            <form method="POST" action="{{ route('admin.fushan.mortality.entry.generate') }}" style="margin-top:14px;">
                @csrf
                @php($generateButtonDisabled = empty($canGenerateRecords) || $recordTablesMatchTargetCensus)
                <button type="submit"
                    {{ $generateButtonDisabled ? 'disabled' : '' }}
                    onclick="{{ $generateButtonDisabled ? 'return false;' : ($recordTablesNeedRefresh ? "return confirm('目前偵測到輸入表單仍有舊年度資料，確定要清除後更新為此次資料嗎？');" : "return confirm('確定要建立第一次與第二次輸入表單資料嗎？');") }}"
                    style="padding:10px 18px; border:0; border-radius:6px; background:{{ $generateButtonDisabled ? '#d1d5db' : ($recordTablesNeedRefresh ? '#b45309' : '#3f5f5b') }}; color:{{ $generateButtonDisabled ? '#6b7280' : '#fff' }}; cursor:{{ $generateButtonDisabled ? 'not-allowed' : 'pointer' }};">
                    @if ($recordTablesMatchTargetCensus)
                        已產生輸入表單
                    @elseif ($recordTablesNeedRefresh)
                        清除舊資料並更新為此次資料
                    @else
                        建立 {{ $nextCensus?->survey_year ?? '—' }} 年輸入表單
                    @endif
                </button>
            </form>
        </div>
    </div>
@endsection
