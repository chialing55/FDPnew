<div>
    <h2> 2023 年 壽山森林觀測樣區 第 {{$entry}} 次資料輸入</h2>
    <div style='margin-top:10px'>
         <p>請先詳閱 <a href="{{--{{asset('/shoushan/plot/10m_note')}}--}}"><b>資料輸入注意事項</b></a></p>  
        <p> 輸入者 {{$user}}，輸入日期 {{date("Y-m-d")}}</p>
    </div>


    <div style='font-weight: 800; margin-bottom: 20px; display: inline-flex;' >
        <span style='margin-right: 20px;'>選擇要輸入的樣區</span>

        <select class="fs100 entryplot" style='width:120px; ' wire:model='selectPlot' wire:change="searchSite($event.target.value, 1, 1)">
             
            <option value=""></option>
            @for ($i=0; $i<count($plots);$i++)
            <option value="{{$i}}">{{$plots[$i]}} </option>
             @endfor
        </select>


        @if (!$record)
        <span style='padding-left:20px'>{{$entrynote}}</span>
        @else

@php
   if ($selectPlot==0){$prevshow="prevhidden"; $nextshow='prevshow';} 
   else if ($selectPlot==count($plots)){$prevshow='prevshow'; $nextshow='prevhidden';}
   else {$prevshow='prevshow'; $nextshow='prevshow';}
@endphp
            <span class='{{$prevshow}}'><a class='a_' wire:click.once="searchSite({{($selectPlot-1)}}, 1, 1)">上一個樣方</a></span>
            <span class='{{$nextshow}}'><a class='a_' wire:click.once="searchSite({{($selectPlot+1)}}, 1, 1)">下一個樣方</a></span>
        @endif

    </div>

    @if($record && $record!='')
@php
// print_r($record[1][1]);
$plot2list=array("11","12","22","21");
$nowplotkey=array_keys($plot2list, $sqx.$sqy);
$nowplot=$plot2list[$nowplotkey[0]];
$searchSiteVar=$selectPlot;
$tableVar=$plot.$sqx.$sqy;
$alterOtherNote="";
@endphp

        <div class='text_box'>
           
            <h6>樣區環境資料</h6>
            <hr>
            <div id='simplenote' class='text_box2'>
                <ul>
                <li><b>輸入資料後需按 <button class='datasavebutton' style='width: auto;'>儲存</button> ，才能確實將資料儲存。</b></li>
                <li>除地形外，其餘欄位皆須輸入數字。</li>
                </ul>
            </div>
@include('includes.str-envi-entrytable')

        </div>

        <div class='text_box'>
           
        <h6>每木調查資料</h6>
        <hr>

            <div id='simplenote' class='text_box2'>
                <ul>
@include('includes.ss-entrynote')
                    {{-- <li>最後一個樣區為 S-F-38</li> --}}
                </ul>
            </div>


@include('includes.str-tree-entrytable')
</div>
<div style='margin-left: 30px;'>
<button class='recruit recruitbutton' onclick="$('.recruittableout').toggle();">新增樹與漏資料樹</button>
</div>


<div class=' text_box recruittableout' style='margin-top: 20px;'>

   <h6>新增樹與漏資料樹</h6>
   <hr>
   <div id='simplenote' class='text_box2'>
        <ul>
@include('includes.ss-recruit-entrynote')
            
        </ul>
    </div>

@include('includes.str-recruit-entrytable')
</div>



        <div class='text_box'>

            <h6>地被覆蓋資料</h6>
            <hr>
            <div id='simplenote' class='text_box2'>
                <ul>
                <li><b>輸入資料後需按 <button class='datasavebutton' style='width: auto;'>儲存</button> ，才能確實將資料儲存。</b></li>
                <li>layer: u (地被層，understory)，o (上木層，overstory)。</li>
                </ul>
            </div>

            <div class='entrytablediv covtable'>
                <div class='cov_pages' style='margin-bottom: 5px'>
                    <div class='cov_totalnum'></div>
                    <div class='cov_pagenote'></div>
                    <div class='cov_prev'>上一頁</div>
                    <div class='cov_next'>下一頁</div>
                </div>
                <p class='covsavenote savenote'></p>

                <div id='covtable{{$tableVar}}' style='margin-top: 10px;'></div>
                <span class='covsavenote savenote'></span>
                <p style='text-align: center'><button name='covsave{{$tableVar}}' class="datasavebutton" style='width: 400px;'>儲存</button></p>

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
               <div id='simplenote' class='text_box2'>
                    <ul>
                        <li><b>輸入資料後需按 <button class='datasavebutton' style='width: auto;'>儲存</button> ，才能確實將資料儲存。</b></li>
                        <li>需有日期資料才會進入輸入檢查。資料不完整不予儲存。</li>
                        <li>新增種類需為名錄內植物。如有需新增名錄，請洽管理員。</li>
                        <li>layer: u (地被層，understory)，o (上木層，overstory)。</li>
                        <li>上木層地被沒有量測高度資料，紀錄為0。</li>
                        <li>未通過檢查以致無法儲存的資料將保留在輸入表單中。已儲存的資料可按右鍵以刪除。</li>
                        {{-- <li>若需新增植物種類，請洽資料管理員。</li> --}}
                        
                    </ul>
                </div>
                <div class='entrytablediv'>
                    <p class='addcovsavenote savenote'></p>
                    <div id='addcovtable{{$tableVar}}' style='margin-top: 10px;'></div>
                    <span class='addcovsavenote savenote'></span>
                    <p style='text-align: center;'><button name='addcovsave{{$tableVar}}' class="save2 datasavebutton" style='width:400px'>儲存</button></p>
                    <p style='margin-top:5px;'><button name='clearaddcovtable' class="save2">清空新增表單</button></p>
                </div>
            </div>

{{--          @if($selectPlot==(count($plots)-1))
        
        <div style='margin: 30px 0 0 30px;'>
        <button class='finish finishbutton' onclick="finish({{$entry}})">輸入完成</button>
        <span class='finishnote savenote'></span>
        </div>
        @endif --}}

    @endif
</div>
