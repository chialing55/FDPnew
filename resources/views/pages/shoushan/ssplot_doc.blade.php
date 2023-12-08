@extends('layouts/ssplot') 
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

@php 
$plot = array('B-F-01', 'B-F-04', 'B-F-06', 'B-F-13', 'B-F-14', 'B-F-19', 'G-F-01', 'G-F-02', 'G-F-03', 'G-F-06', 'Q-F-03', 'S-F-01', 'S-F-02', 'S-F-04', 'S-F-06', 'S-F-07', 'S-F-09', 'S-F-11', 'S-F-14', 'S-F-15', 'S-F-16', 'S-F-17', 'S-F-21', 'S-F-38');

@endphp

{{-- 紀錄紙 --}}
<div class='flex text_outbox'>

    <div class='text_box'>
        <h2>下載紀錄紙</h2>
        <hr>
        <h6>森林觀測樣區紀錄紙</h6>
        <div style='flex-direction: column;' >
            <div style='margin: 20px 0px;'>
                請選擇輸出樣區：                    
                <select class="fs100 entryplot" style='width:120px; ' name='plot' >
                    
                    <option value=""></option>
                    @for ($i=0; $i<count($plot);$i++)
                    <option value="{{$plot[$i]}}">{{$plot[$i]}} </option>
                     @endfor
                    </select>
                </select>
                <p style='margin-top: 10px; text-align: right;'><button class="button1"  >送出</button></p>
            </div>
        </div>
        <hr>
        <h6>1.05 ha 樣區紀錄紙</h6>
        <div style='flex-direction: column;' >
            <div style='margin: 20px 0px;'>
                請選擇輸出樣區：                    
                <select class="fs100 entryqx" name='qx' id='qx'>
                <option value=''></option>
                @for ($i=-4; $i<11;$i++)     
                <option value="{{$i}}">{{$i}}</option>
                @endfor
                </select>-<select class="fs100" name='qy' id='qy'>
                    <option value=''></option>
                @for ($i=13; $i<20;$i++)
                <option value="{{$i}}">{{$i}} </option>
                @endfor
                </select>
                <p style='margin-top: 10px; text-align: right;'><button class="button2"  >送出</button></p>
            </div>
        </div>

    </div>

    <div class='text_box'>
        <h2>樣區調查相關文件</h2>
        <hr>
        <ol>
            
            <li></li>

        </ol>
    </div>

    <div class='text_box'>
        <h2>相關研究資料</h2>
        <hr>
        <ol>
            <li></li>
        </ol>
    </div>
{{--     <div class='text_box'>
        <h2>輸入資料檢查流程</h2>
        <hr>
        <ol>
            <li></li>
        </ol>
        
    </div> --}}
</div>

@endsection