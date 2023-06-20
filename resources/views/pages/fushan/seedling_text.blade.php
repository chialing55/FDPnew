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

        <div class='iflex' style='flex-direction: column;' >
                
                    <p>請選擇輸出的樣區範圍：</p>
                    <div>
                    <input type="text" value='' id='start' style='width:30px'/> - <input type="text" value='' id='end' style='width:30px'/>
                    <button type='submit' class="button1" style='margin-left: 20px;'>送出</button>
                    </div>
                
        </div>
        <div class='iflex' style='flex-direction: column; margin-left:100px; border-left: 1px inset #000000; padding-left:50px'>
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
</div>

@endsection