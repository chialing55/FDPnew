@php
$personlist=['','蔡佳秀'];


@endphp

<div>
<h2>種子雨資料輸入</h2>
    <div style='margin-top:10px'>
        <p>請先詳閱<a href="{{asset('/fushan/seedls/note')}}">種子輸入注意事項</a></p> 
        <p> 輸入者 {{$user}}，輸入日期 {{date("Y-m-d")}}</p>
    </div>
@if($entry=='')
    @if($census==$census2)
    <div class='text_box'>
        <h6>新建種子雨收集日期</h6>
            <form wire:submit.prevent="submitForm" method="POST">
                <p>census：{{$census}}</p>
                <p>調查日期：<input name='date' id='date' type='date' placeholder="YYYY-MM-DD" style='width:120px; ' class='fs100' wire:model="date"></p>
                <p>調查人員：<select name='person1' id='person1' class="" style='width:85px;' wire:model="person1">
                    @for ($i=0; $i<count($personlist);$i++)     
                    <option value="{{$personlist[$i]}}">{{$personlist[$i]}}</option>
                    @endfor
                    </select>, <select name='person2'  id='person2' class="" style='width:85px;' wire:model="person2">
                    @for ($i=0; $i<count($personlist);$i++)     
                    <option value="{{$personlist[$i]}}">{{$personlist[$i]}}</option>
                    @endfor
                    </select>,  <input name='person3' id='person3' type=text style='width:150px' wire:model="person3"/> 
                    (請以半形「,」分隔，並留一空格)
                </p>
                <p>note：<input name='note' id='note' type='text' style='width:220px; ' class='fs100' wire:model="note"></p>
                <button type='submit'>輸入</button>
            </form>
       
    </div>
    @elseif($census>$census2)
    <div class='text_box keepenter'>
        <b>輸入第 {{$census2}} 次 ({{$census2date['date']}}) 資料</b>  <button type='submit' wire:click='direntry({{$census2}})' style='margin-left: 20px;'>輸入</button>
    </div>
    @endif
@else
    <div class='text_box keepenter'> <b>第 {{$thiscensus}} 次 ({{$census2date['date']}}) 調查資料未完成</b>   <button type='submit' wire:click='direntry({{$thiscensus}})' style='margin-left: 20px;'>繼續輸入</button>
    </div>
@endif
    <div class='text_box dateinfo'>
        <table id='dateinfotable' class='tablesorter'>
            <h6>最近五次調查日期</h6>
            <thead>
                <tr>
                    <th>census</th>
                    <th>date</th>
                    <th>workers</th>
                </tr>
            </thead>
            <tbody>
                @foreach($dateinfo as $info)
                <tr>
                    <td>{{$info['census']}}</td>
                    <td>{{$info['date']}}</td>
                    <td>{{$info['workers']}}</td>

                </tr>
                @endforeach
            </tbody>
        </table>

    </div>

    <div class='text_box dateinfo' style='background-color:#ececc4'>
        <h6>修改調查日期</h6>
        <hr>
        <form wire:submit.prevent="deleteForm" method='POST' style='margin-top:10px'>
            <span style='margin-right:20px'> 將 census <input name='chcensus' style="width:60px" wire:model='chcensus'/>
            日期資料刪除再重新輸入</span>
            <button type='submit' >刪除</button>
            <span class='savenote'>{{$note2}}</span>
        </form>
    </div>

    <div class='text_box entrytableout'>
        <h6>第 {{$thiscensus}} 次 ({{$census2date['date']}}) 調查輸入</h6>
       
        <div id='simplenote' class='text_box'>
            <ul>
            <li><b>輸入資料後需按 <button class='datasavebutton' style='width: auto;'>儲存</button> ，才能確實將資料儲存。</b></li>
            <li>Trap欄位會自動補0。</li>
            <li>若為小種子、無法計算種子數量，種子數及活性欄位皆填NA。</li>
            <li>若不符合規則，會在檢查欄位顯示錯誤之處，若未更改，將無法完成輸入。</li>
            <li><b>輸入完成後請按下<button class='datasavebutton' style='width: auto;'>輸入完成</button></b>，檢查通過後，即會將資料匯入大表。</li>
            </ul>
        </div>

        <div class='entrytablediv'>
            {{-- <h2>測試</h2> --}}
            <span class='seedssavenote savenote'></span>
           <div id='seedstableout' class='seedstable fs100'>
                <div class='pages'>
                    <div class='pagenote'></div>
                    <div class='prev'>上一頁</div>
                    <div class='next'>下一頁</div>
                    <div style='margin-left: 20px;'><button name='creattable'>開啟新空白頁</button></div>
                </div>

                <div id='seedstable' class='fs100' >

                    <p style='margin-top:5px; text-align: center'><button name='datasave' class='datasavebutton' style='width:550px'>儲存</button></p>

                </div>
            </div>
            <div id='seedstableout_empty' class='seedstable fs100'>
                <div class='pages'>
                    <button name='show_seedstable'>檢視輸入資料</button>
                </div>
                <div id='seedstable_empty' class='fs100' >
                    <p style='margin-top:5px; text-align: center'><button name='newdatasave' class='datasavebutton' style='width:550px'>儲存</button></p>

                </div>
            </div>

        </div>
        <div style='margin-top: 20px;'>
        <button class='finish finishbutton' onclick="finish()">輸入完成</button>
        <span class='finishnote savenote'></span>


        </div>        
    </div>

    <div>
    </div>
 
</div>
