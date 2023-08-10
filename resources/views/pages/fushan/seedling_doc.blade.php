@extends('layouts/seedling') 
@section('pagejs')
<script>
  // var element = document.getElementById("#list1");
  // element.classList.add("now");
$('.list1').addClass('now');
$('.list1 hr').css('color', '#91A21C');

</script>

<script>
  //紀錄紙下載，超過資料量範圍
    var msg = '{{Session::get('alert')}}';
    var exist = '{{Session::has('alert')}}';
    if(exist){
      alert(msg);
    }

</script>
@endsection
@section('rightbox')


{{-- 紀錄紙 --}}
<div class='flex text_outbox'>
    <div class='slrecord text_box' >

        <h2>下載第{{$census}}次調查的記錄紙</h2>
        <hr>

        <div class='' style='margin-bottom: 20px;' >
                
                    <p>請選擇輸出的樣區範圍：</p>
                    <div>
                    <input type="text" value='' id='start' style='width:30px'/> - <input type="text" value='' id='end' style='width:30px'/>
                    <button type='submit' class="button1" style='margin-left: 20px;'>送出</button>
                    </div>
                
        </div>
        <hr>
        <div class='' style='margin-top: 20px'>
                <p>選擇固定輸出範圍的檔案：</p>
                <ul>
                    <li><a href="/fsseedling-record-pdf/1/32" target="_blank">1-32</a></li>
                    <li><a href="/fsseedling-record-pdf/33/44" target="_blank">33-44</a></li>
                    <li><a href="/fsseedling-record-pdf/45/60" target="_blank">45-60</a></li>
                    <li><a href="/fsseedling-record-pdf/61/88" target="_blank">61-88</a></li>
                    <li><a href="/fsseedling-record-pdf/89/99" target="_blank">89-99</a></li>
                    <li><a href="/fsseedling-record-pdf/100/107" target="_blank">100-107</a></li>
                </ul>

        </div>
      
    </div>
    <div class='text_box'>
        <h2>小苗調查相關文件</h2>
        <hr>
        <ol>
            <li><a href='{{asset('/fs_tree_file/09_福山種子網分布圖.pdf')}}' target="_blank">福山種子網分布圖</a></li>
        </ol>
    </div>
    <div class='text_box'>
        <h2>輸入資料檢查流程圖</h2>
        <hr>
        <ol>
            <li><a href='https://hospitable-nickel-b27.notion.site/6e0835f6dd3f43f080ad0fdebbdb7670?pvs=4' target="_blank">小苗輸入檢查流程</a></li>
            <li><a href='https://hospitable-nickel-b27.notion.site/2dd68747525f4ae2a638245e05856cdb?pvs=4' target="_blank">新苗輸入檢查流程</a></li>
            <li><a href='https://hospitable-nickel-b27.notion.site/7d9691e6d60a495dbcc52416833c44f2?pvs=4' target="_blank">輸入完成檢查流程</a></li>
        </ol>
        
    </div>
</div>

@endsection