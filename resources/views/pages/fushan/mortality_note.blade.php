@extends('layouts/mortality')

@section('pagejs')
    <script>
        $(function() {
            $('.list4').addClass('now');
            $('.list4 hr').css('color', '#91A21C');
        });
    </script>
@endsection

@section('rightbox')
    <div class='text_box' style="width:min(960px, 92vw); text-align:left;">
        <h2>死亡率調查輸入注意事項</h2>
        <hr>
        <p style="margin:10px 0 0; color:#475569;">這一頁先保留作為死亡率調查輸入前的注意事項頁，後續再補上正式內容。</p>
        <p style="margin:12px 0 0;">
            <a href="{{ route('admin.fushan.mortality.entry.1') }}" style="color:#1d4ed8; text-decoration:none;">前往第一次輸入</a>
        </p>
    </div>
@endsection
