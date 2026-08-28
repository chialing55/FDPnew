@extends('layouts.geo-tree-survey')

@section('pagejs')
    <script>
        $('.list2').addClass('now');
        $('.list2 hr').css('color', '#91A21C');
    </script>
@endsection

@section('rightbox')
    <div class="flex text_outbox" style="flex-direction: column; align-items: center;">
        <div class="text_box">
            <h2>GEO-TREES 資料檢視</h2>
            <hr>
            <p>頁面已建立，待雙次輸入、資料比對及完整資料表流程完成後串接。</p>
        </div>
    </div>
@endsection
