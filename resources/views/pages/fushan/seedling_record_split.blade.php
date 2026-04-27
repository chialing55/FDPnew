@extends('layouts/seedling')

@section('pagejs')
<script>
$('.list1').addClass('now');
$('.list1 hr').css('color', '#91A21C');
</script>
@endsection

@section('rightbox')
<div class='flex text_outbox'>
    <div class='text_box' style='max-width: 760px;'>
        <h2>{{ $title }}</h2>
        <hr>
        <p>範圍 {{ $start }}-{{ $end }} 的紀錄紙預估負載較高，為避免 PDF 輸出失敗，系統已自動切成較小區段。</p>
        <p style='font-size: 90%; color: #4b5563;'>
            原始範圍估計：
            {{ $metrics['record_count'] }} 筆資料，
            {{ $metrics['estimated_pages'] }} 頁，
            note 總字數 {{ $metrics['note_char_count'] }}，
            長 note {{ $metrics['long_note_count'] }} 筆。
        </p>

        <ul style='margin-top: 18px; line-height: 1.9;'>
            @foreach ($chunks as $chunk)
                <li>
                    <a href="{{ route('admin.fushan.seedling.pdf.record', ['start' => $chunk['start'], 'end' => $chunk['end']]) }}" target="_blank">
                        {{ $chunk['start'] }}-{{ $chunk['end'] }}
                    </a>
                    <span style='font-size: 90%; color: #6b7280;'>
                        ({{ $chunk['metrics']['record_count'] }} 筆 / {{ $chunk['metrics']['estimated_pages'] }} 頁)
                    </span>
                </li>
            @endforeach
        </ul>
    </div>
</div>
@endsection
