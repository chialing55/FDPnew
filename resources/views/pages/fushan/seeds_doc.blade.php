@extends('layouts/seeds') 
@section('pagejs')
<script>
  // var element = document.getElementById("#list1");
  // element.classList.add("now");
$('.list1').addClass('now');
$('.list1 hr').css('color', '#91A21C');

</script>

<script>

</script>
@endsection
@section('rightbox')


{{-- 紀錄紙 --}}
<div class='flex text_outbox'>

    <div class='text_box'>
        <h2>種子雨調查相關文件</h2>
        <hr>
        <ol>
            
            <li><a href='{{asset('/fs_seeds_file/種子分類記錄紙.pdf')}}' target="_blank">種子分類記錄紙</a></li>
            <li><a href='{{asset('/fs_seeds_file/種子樣本標籤紙_v2.pdf')}}' target="_blank">種子樣本標籤紙</a></li>
            <li><a href='{{asset('/fs_seeds_file/拍照用比例尺.pdf')}}' target="_blank">拍照用比例尺</a></li>
        </ol>
    </div>

    <div class='text_box'>
        <h2>種子雨研究相關文件</h2>
        <hr>
        <ol>
            <li><a href='{{asset('/fs_tree_file/09_福山種子網分布圖.pdf')}}' target="_blank">種子網分布圖</a></li>
            <li><a href='{{asset('/fs_tree_file/種子網架設方法.pdf')}}' target="_blank">種子網架設方法</a></li>
            <li><a href='{{asset('/fs_seeds_file/種子網調查操作守則_東華版.pdf')}}' target="_blank">調查操作守則</a></li>
            <li><a href='{{asset('/fs_seeds_file/trapmaps_HOBO架設位置.pdf')}}' target="_blank">HOBO架設位置</a></li>
        </ol>
    </div>
{{--     <div class='text_box'>
        <h2>輸入資料檢查流程圖</h2>
        <hr>
        <ol>
            <li><a href='https://hospitable-nickel-b27.notion.site/6e0835f6dd3f43f080ad0fdebbdb7670?pvs=4' target="_blank">小苗輸入檢查流程</a></li>
            <li><a href='https://hospitable-nickel-b27.notion.site/2dd68747525f4ae2a638245e05856cdb?pvs=4' target="_blank">新苗輸入檢查流程</a></li>
            <li><a href='https://hospitable-nickel-b27.notion.site/7d9691e6d60a495dbcc52416833c44f2?pvs=4' target="_blank">輸入完成檢查流程</a></li>
        </ol>
        
    </div> --}}
</div>

@endsection