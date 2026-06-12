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
        <div class='text_box mortality-entry-shell' style="margin-bottom:16px;">
            <div class="app-feedback-note app-feedback-note--success" style="margin:0;">
                {{ session('status') }}
            </div>
        </div>
    @endif

    @if ($entry === '1' && !$recordTablesMatchTargetCensus)
        <div class='text_box mortality-entry-shell' style="margin-bottom:16px;">
            <h2>尚未建立輸入表單</h2>
            <hr>
            <p style="line-height:1.8; margin:0;">請管理員先到<a href="{{ route('admin.fushan.mortality.import') }}">資料匯入</a>頁面下方產生輸入表單。</p>
            @if (!empty($generateBlockedReason))
                <div style="margin-top:10px; color:#9a3412;">{{ $generateBlockedReason }}</div>
            @endif
        </div>
    @endif

    @if (!$recordTablesReady && $entry !== '1')
        <div class='text_box mortality-entry-shell' style="margin-bottom:16px;">
            <h2>尚未建立輸入表單</h2>
            <hr>
            <p style="line-height:1.8; margin:0;">record1 / record2 目前沒有資料，請管理員先到資料匯入頁面下方產生輸入表單。</p>
        </div>
    @else
        @livewire($site . '.mortality-showentry', ['entry' => $entry, 'user' => $user, 'site' => $site])
    @endif
@endsection
