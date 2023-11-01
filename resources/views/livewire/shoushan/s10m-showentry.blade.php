<div>
    <h2> 2023 年 壽山森林觀測樣區 第 {{$entry}} 次資料輸入</h2>
    <div style='margin-top:10px'>
        <p>請先詳閱 <a href="{{asset('/shoushan/plot/10m_note')}}"><b>資料輸入注意事項</b></a></p> 
        <p> 輸入者 {{$user}}，輸入日期 {{date("Y-m-d")}}</p>
    </div>


    <div style='font-weight: 800; margin-bottom: 20px; display: inline-flex;' >
        <span style='margin-right: 20px;'>選擇要輸入的樣區</span>

        <select class="fs100 entryplot" style='width:120px; ' wire:model='selectPlot' wire:change="searchplot($event.target.value, 1, 1)">
            
            <option value=""></option>
            @for ($i=0; $i<count($plots);$i++)
            <option value="{{$i}}">{{$plots[$i]}} </option>
             @endfor
        </select>


        @if (!$record)
        <span style='padding-left:20px'>{{$entrynote}}</span>
        @else
            @if($selectPlot>0)
                <span style='padding-left:20px'><a class='a_' wire:click="searchplot({{($selectPlot-1)}}, 1, 1)">上一個</a></span>

            @endif
            @if($selectPlot<count($plots))

                <span style='padding-left:20px'><a class='a_' wire:click="searchplot({{($selectPlot+1)}}, 1, 1)">下一個</a></span>

            @endif  
        @endif

    </div>

    @if($record && $record!='')
        <div class='text_box'>
           
            <h6>樣區環境資料</h6>
            <hr>

            <div id='simplenote' class='text_box'>
                <ul>
                <li><b>輸入資料後需按 <button class='datasavebutton' style='width: auto;'>儲存</button> ，才能確實將資料儲存。</b></li>
                <li>除地形外，其餘欄位皆須輸入數字。</li>
                </ul>
            </div>

            <div class='entrytablediv'>
                {{-- <h2>測試</h2> --}}
                <span class='envisavenote savenote'>
                    
               </span>
                <div id='envitable{{$plot}}{{$sqx.$sqy}}' class='test fs100' ></div>
                <p style='margin-top:5px; text-align: center'><button name='envisave{{$plot}}{{$sqx.$sqy}}' class='datasavebutton' style='width:550px'>儲存</button></p>
            </div>
        </div>



       

@php
// print_r($record[1][1]);
$plot2list=array("11","12","22","21");
$nowplotkey=array_keys($plot2list, $sqx.$sqy);
$nowplot=$plot2list[$nowplotkey[0]];

@endphp

        <div class='text_box'>
           
        <h6>每木調查資料</h6>
        <hr>

            <div id='simplenote' class='text_box'>
                <ul>
                    <li><b>輸入資料後需按 <button class='datasavebutton' style='width: auto;'>儲存</button> ，才能確實將資料儲存。</b>請確實依照紙本資料輸入，以減少兩次輸入的不一致。</li>
                    <li>日期格式： YYYY-MM-DD。每筆資料皆需輸入日期，<b>日期為 0000-00-00 者視同未輸入</b>。</li>
                    <li>status 為 0(全株死亡),-1(全株失蹤),-2(全株 dbh < 1 cm),-3(枝幹死亡)，則 dbh 需為0，且 code 不得有值。tatus 為空值，則 dbh 不得為 0。</li>
                    <li>dbh 必須<b>大於或等於</b>上次調查，或勾選縮水。</li>
                    <li>code：C(更改pom)，I(量測點表面不平)，P(枝幹倒伏)，R(無行拓殖分株)。code R 只能出現在分支。<b>code 代碼間可共存</b>，多碼時照字母排列，<b>中間不留空格</b>。</li>
                    <li>note： TAB=#。統一使用<b>「中文」標點符號</b>。<b>「半形」英文符號</b>。<b>「半形」阿拉伯數字</b>，數字後留一格空白。先確認原始 note，加「。」，再輸入本次note。不同類型 note 間用「。」分隔。</li>
                    <li>plot，5x，5y，tag，b，csp 等欄位需要修改時，請至「特殊修改<i class='fa-regular fa-note-sticky'></i>」填寫。<b>只需填寫需修改的部分。</b></li>
                    <li>若調查後的 dbh < 1 cm，請在表格內填寫 1，再至「特殊修改<i class='fa-regular fa-note-sticky'></i>」的 dbh(<1) 欄位填寫正確之調查資料。</li>
                    <li>新樹資料可以修改或刪除。</li>
                    
                </ul>
            </div>
            <h2 style='display: inline-block;'>({{$sqx}}, {{$sqy}}) </h2>
            <div class='tablenote'>
                @if($record!='無')
                <span style='margin-right: 20px' class='totalnum'></span>
                @else
                <span style='margin-right: 20px'> 沒有舊資料</span>
                @endif
            @if(($sqx.$sqy)!=$plot2list[0])
            @php 
                $prev=$plot2list[($nowplotkey[0]-1)];
            @endphp
                <span style='margin-left:30px'><a class="a_" wire:click.once="searchplot({{$selectPlot}}, {{$prev[0]}}, {{$prev[1]}})">上一個樣區</a></span>
            @endif
            @if((($sqx.$sqy)!=$plot2list[3]))
            @php 
                $next=$plot2list[($nowplotkey[0]+1)];
            @endphp
                <span style='margin-left: 30px;'><a class="a_" wire:click.once="searchplot({{$selectPlot}}, {{$next[0]}}, {{$next[1]}})">下一個樣區</a></span>
            @endif
            </div>


            <div id='datatable{{$plot}}{{$sqx.$sqy}}' style='margin-top: 20px;' class='fs100' ></div>


            <p style='margin-top:5px; text-align: center;'><span class='datasavenote savenote' style='margin: 0 30px 0 0'></span><button name='datasave{{$plot}}{{$sqx.$sqy}}' class='datasavebutton'>儲存</button></p>
            
            <div class='alternotetalbeouter'>
                <h6 class='alterh6'>特殊修改<span style='margin-left: 20px; font-size: 80%; font-weight: 500;'>*只需填寫需修改的資料</span></h6>

                <p ><span class='alterstemid'></span>
                <span class='altersavenote savenote'></span></p>
                <div id='alternotetable' style='margin-top: 5px;' class='fs100' ></div>


                <p style='margin-top:10px; text-align: right;'><button name='alternotesave' class='datasavebutton' style='width: auto;' >儲存</button>

                <button name='deletealternote' class='deletealternotebutton' onclick="deletealternoteButtonClick(this)">刪除此資料</button>
                <button class='close' onclick="$('.alternotetalbeouter').hide(); $('.alternotetable').html();" >X</button>

                </p>


            </div>
        </div>
<div style='margin-left: 30px;'>
<button class='recruit recruitbutton' onclick="$('.recruittableout').toggle();">新增樹與漏資料樹</button>
</div>


<div class=' text_box recruittableout' style='margin-top: 20px;'>

   <h6>新增樹與漏資料樹</h6>
   <hr>
   <div id='simplenote' class='text_box'>
        <ul>
            <li><b>輸入資料後需按 <button class='datasavebutton' style='width: auto;'>儲存</button> ，才能確實將資料儲存。</b></li>
            <li>需有日期資料才會進入輸入檢查。資料不完整不予儲存。</li>
            <li>dbh - 新增樹的 dbh <b>必須 ≥ 1</b>。</li>
            <li>新增狀態 - 預設為新增。若為漏資料的樹，請記得點選。</li>
            <li>正榕與雀榕: 若分支不在同一小區，需在code記R。其餘物種，分支皆需與主幹在同一小區。</li>
            <li>未通過檢查以致無法儲存的資料將保留在輸入表單中。已儲存的資料可按右鍵以刪除。</li>
            {{-- <li>若需新增植物種類，請洽資料管理員。</li> --}}
            
        </ul>
    </div>
    <div class='entrytablediv'>
        <p class='recruitsavenote savenote'></p>
        
        
        <div id='recruittable{{$plot}}{{$sqx.$sqy}}' style='margin-top: 10px;'></div>
        <p style='text-align: center;'><span class='recruitsavenote savenote' style='margin: 0 30px 0 0'></span><button name='recruitsave{{$plot.$sqx.$sqy}}' class="save2 datasavebutton">儲存</button></p>
        
        <p style='margin-top:5px;'><button name='clearrecruittable' class="save2">清空新增表單</button></p>
    </div>
</div>



        <div class='text_box'>

            <h6>地被覆蓋資料</h6>
            <hr>
            <div id='simplenote' class='text_box'>
                <ul>
                <li><b>輸入資料後需按 <button class='datasavebutton' style='width: auto;'>儲存</button> ，才能確實將資料儲存。</b></li>
                <li>layer: u (地被層，understory)，o (上木層，overstory)。</li>
                </ul>
            </div>

            <h2 style='display: inline-block;'>({{$sqx}}, {{$sqy}}) </h2>

           
            <div class='entrytablediv covtable'>

                <p class='covsavenote savenote'></p>

                <div id='covtable{{$plot.$sqx.$sqy}}' style='margin-top: 10px;'></div>
                <p style='text-align: center'><span class='covsavenote savenote'></span><button name='covsave{{$plot.$sqx.$sqy}}' class="datasavebutton" style='width: 400px;'>儲存</button></p>

            </div>
                <div class='entrytablediv nocovdata'>
                <p>尚未有覆蓋度資料</p>
               </div>
        </div>
        <div style='margin-left: 30px;'>
            <button class='addcov rollbutton' onclick="$('.addcovtableout').toggle();">新增覆蓋度資料</button>
        </div>

        
            <div class=' text_box addcovtableout'>

               <h6>新增覆蓋度資料</h6>
               <hr>
               <div id='simplenote' class='text_box'>
                    <ul>
                        <li><b>輸入資料後需按 <button class='datasavebutton' style='width: auto;'>儲存</button> ，才能確實將資料儲存。</b></li>
                        <li>需有日期資料才會進入輸入檢查。資料不完整不予儲存。</li>
                        <li>layer: u (地被層，understory)，o (上木層，overstory)。</li>
                        <li>上木層地被沒有量測高度資料，紀錄為0。</li>
                        <li>未通過檢查以致無法儲存的資料將保留在輸入表單中。已儲存的資料可按右鍵以刪除。</li>
                        {{-- <li>若需新增植物種類，請洽資料管理員。</li> --}}
                        
                    </ul>
                </div>
                <div class='entrytablediv'>
                    <p class='addcovsavenote savenote'></p>
                    
                    
                    <div id='addcovtable{{$plot.$sqx.$sqy}}' style='margin-top: 10px;'></div>
                    <p style='text-align: center;'><span class='addcovsavenote savenote' style='margin: 0 30px 0 0'></span><button name='addcovsave{{$plot.$sqx.$sqy}}' class="save2 datasavebutton" style='width:400px'>儲存</button></p>
                    <span class='datasavenote savenote'></span>
                    <p style='margin-top:5px;'><button name='clearaddcovtable' class="save2">清空新增表單</button></p>
                </div>
            </div>

         @if($selectPlot==(count($plots)-1))
        
        <div style='margin: 30px 0 0 30px;'>
        <button class='finish finishbutton' onclick="finish({{$entry}})">輸入完成</button>
        <span class='finishnote savenote'></span>
        </div>
        @endif

    @endif
</div>
