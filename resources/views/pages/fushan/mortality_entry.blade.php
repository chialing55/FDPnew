@extends('layouts/mortality')

@section('pagejs')
    @php
        echo "<script>const entry = " . json_encode($entry) . "; const user = " . json_encode($user) . ";</script>";
    @endphp

    <script>
        $(function() {
            $('.list4').addClass('now');
            $('.list4 hr').css('color', '#91A21C');
        });
    </script>
@endsection

@section('rightbox')
    @if (session('status'))
        <div class='text_box' style="width:min(960px, 92vw); text-align:left; margin-bottom:16px;">
            <div style="padding:10px 12px; border:1px solid rgba(34,197,94,.35); background:rgba(34,197,94,.12); color:#14532d; border-radius:6px;">
                {{ session('status') }}
            </div>
        </div>
    @endif

    @if ($entry === '1' && !$recordTablesMatchTargetCensus)
        <div class='text_box' style="width:min(960px, 92vw); text-align:left; margin-bottom:16px;">
            <h2>第一次輸入表單準備</h2>
            <hr>

            <div style="line-height:1.8;">
                <div>本次預計輸入：census {{ $nextCensus?->census ?? '—' }}，{{ $nextCensus?->survey_year ?? '—' }} 年資料</div>
                <div>來源資料表：{{ $targetTable ?? '—' }}</div>
                {{-- <div>`record1` 目前 census：{{ empty($record1CensusValues) ? '無資料' : implode(', ', $record1CensusValues) }}</div>
                <div>`record2` 目前 census：{{ empty($record2CensusValues) ? '無資料' : implode(', ', $record2CensusValues) }}</div> --}}
            </div>

            <div style="margin-top:14px; color:{{ $recordTablesNeedRefresh ? '#9a3412' : '#475569' }};">
                @if (!empty($generateBlockedReason))
                    {{ $generateBlockedReason }}
                @else
                    {{ $recordTablesStatusMessage }}
                @endif
            </div>

            <form method="POST" action="{{ route('admin.fushan.mortality.entry.generate') }}" style="margin-top:14px;">
                @csrf
                <button type="submit"
                    {{ empty($canGenerateRecords) ? 'disabled' : '' }}
                    onclick="{{ empty($canGenerateRecords) ? 'return false;' : ($recordTablesNeedRefresh ? "return confirm('目前偵測到輸入表單仍有舊年度資料，確定要清除後更新為此次資料嗎？');" : "return confirm('確定要建立第一次與第二次輸入表單資料嗎？');") }}"
                    style="padding:10px 18px; border:0; border-radius:6px; background:{{ empty($canGenerateRecords) ? '#d1d5db' : ($recordTablesNeedRefresh ? '#b45309' : '#3f5f5b') }}; color:{{ empty($canGenerateRecords) ? '#6b7280' : '#fff' }}; cursor:{{ empty($canGenerateRecords) ? 'not-allowed' : 'pointer' }};">
                    {{ $recordTablesNeedRefresh ? '清除舊資料並更新為此次資料' : '建立 ' . ($nextCensus?->survey_year ?? '—') . ' 年輸入表單' }}
                </button>
            </form>
        </div>
    @endif

    @livewire($site . '.mortality-showentry', ['entry' => $entry, 'user' => $user, 'site' => $site])
@endsection
