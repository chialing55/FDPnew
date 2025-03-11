@extends('layouts/tree') 
@section('pagejs')
<script>
  // var element = document.getElementById("#list1");
  // element.classList.add("now");
$(function() {
  $('.list1').addClass('now');
  $('.list1 hr').css('color', '#91A21C');
})
// $('.list1 hr').css('color', '#91A21C');

</script>

@endsection
@section('rightbox')
<div class='flex text_outbox'>
    <div class='text_box'>
        <h2>下載第五次調查紀錄紙</h2>
        <hr>
        <div  style='flex-direction: column;' >
            <div style='margin: 20px 0px;'>
                請選擇輸出樣區：                    
                <select class="fs100 entryqx" style='width:60px; ' name='qx' >
                    <option value="" >20x</option>
                    @for ($i=0; $i<25;$i++)     
                    <option value="{{$i}}">{{$i}} </option>
                     @endfor
                    </select>-<select class="fs100" style='width:60px;' name='qy' >
                    <option value="">20y</option>
                    @for ($i=0; $i<25;$i++)
                    <option value="{{$i}}">{{$i}} </option>
                     @endfor
                </select>
                <p style='margin-top: 10px; text-align: right;'><button class="button1"  >送出</button></p>
            </div>
        </div>
        @if($user=='chialing')
        <div style='flex-direction: column;' >            
            <div style='margin: 20px 0px;' class='iflex'>
                輸出樣線 
                    
                <select class="fs100 entryqx" style='width:60px; ' name='qx2' >
                    <option value="" >20x</option>
                    @for ($i=0; $i<25;$i++)     
                    <option value="{{$i}}">{{$i}}</option>
                    @endfor
                </select> 的全線資料
                <button class="button2"  style='margin-left: 20px;'>送出</button>
            </div>
            <div id="downloadMessage" style="display: none;">下載中...</div>
            <div id="downloadMessage2" style="display: none;">載入中...</div>
        </div>
        @endif
        <hr>
        <ol>
            <li><a href='{{asset('/fs_tree_file/05_第五次調查_新增植株表.pdf')}}' target="_blank">第五次調查新增植株表</a></li>
        </ol>
    </div> 
    <div class='text_box'>
        <h2>每木調查相關表單</h2>
        <hr>
            <ol>
              <li class='text_note_li ' ><a href='https://bit.ly/3psIquc' target="_blank">每木調查工作日誌</a></li>
              <li lass='text_note_li '><a href='https://bit.ly/3YcMFY4' target="_blank">每木調查除錯進度統整表</a></li>
              <li lass='text_note_li '><a href='https://bit.ly/3QnjkYM' target="_blank">標本鑑定清單</a></li>
              <li lass='text_note_li '><a href='https://forms.gle/FKDWx2rmLRyE8ED1A' target="_blank">輸入系統錯誤表單</a></li>
              <li><a href="https://docs.google.com/spreadsheets/d/1PILnqnKb08RxlBilYkM8SKXneXa6YLj4SyV4Cp6P300/edit?usp=sharing">錯誤率資料表單</a></li>
             
              {{-- 連結google表單 --}}
            </ol>
    </div>

    <div class='text_box'>
        <h2>每木調查相關文件</h2>
        <hr>
            <ol>
                <li><a href='https://bit.ly/3Dw5zPX' target="_blank">每木調查方法介紹</a></li>
                <li><a href='{{asset('/fs_tree_file/02_福山樣區複查code與status.pdf')}}' target="_blank">福山樣區複查code與status</a></li>
                <li><a href='{{asset('/fs_tree_file/03_Code%20R的規則%20ver.4.pdf')}}' target="_blank">Code R的規則</a></li>
                <li><a href='https://docs.google.com/document/d/1VvgtNYBD-T8KjfrErSrsjqBqktpiCuEbTT9N9r48D2g/edit' target="_blank">2023 每木調查 note 縮寫說明</li>
                <li><a href='{{asset('/fs_tree_file/04_每日檢查確認表.pdf')}}' target="_blank">每日檢查確認表</a></li>
                
                <li><a href='{{asset('/fs_tree_file/06_問題表格.pdf')}}' target="_blank">問題表格</a></li>
                <li><a href='{{asset('/fs_tree_file/09_福山種子網分布圖.pdf')}}' target="_blank">福山種子網分布圖</a></li>
                <li>每木調查資料整理SOP
                    <ul>
                        <li><a href='https://docs.google.com/document/d/11yTG5wNF6upQwvdRVY1YdyYYm0o8sKzk/edit?usp=sharing&ouid=104328473015955473420&rtpof=true&sd=true' target="_blank">福山每木調查抽查辦法</a></li>
                        <li><a href='https://docs.google.com/document/d/1FqichUcy9F02fuVpepcqAbvm14X9-SJF/edit?usp=sharing&ouid=104328473015955473420&rtpof=true&sd=true' target="_blank">福山每木調查除錯辦法</a></li>
                        <li><a href='https://docs.google.com/document/d/1IO-WwwSyiCVmh-eR4cSyhjUP1BvS0vI2/edit?usp=sharing&ouid=104328473015955473420&rtpof=true&sd=true' target="_blank">福山每木調查資料修正辦法</a></li>
                        <li><a href='https://docs.google.com/document/d/1z1voCrgPnnkPb9qvoaPk6taaYdtHiriH/edit?usp=sharing&ouid=104328473015955473420&rtpof=true&sd=true' target="_blank">福山每木調查資料掃描辦法</a></li>
                    </ul>
                </li>
                

            </ol>
    </div>
    <div class='text_box'>
        <h2>輸入資料檢查流程</h2>
        <hr>
            <ol>
                <li><a href='https://nsysu-plantecology.notion.site/9bf6aa3ad5914bfca473f7442043e495' target="_blank">舊樹輸入檢查流程</a></li>
                <li><a href='https://nsysu-plantecology.notion.site/0f0f5f2cfb71478ba08583954cb6b968' target="_blank">新樹輸入檢查流程</a></li>
                <li><a href='https://nsysu-plantecology.notion.site/52760b64db264444afb684ef47ab4d50' target="_blank">輸入完成檢查流程</a></li>
            </ol>
    </div>
</div>



@endsection