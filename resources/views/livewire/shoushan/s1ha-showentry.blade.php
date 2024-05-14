<div>
<div class="loading-container" wire:loading.class="visible">
    <div class="loading-spinner"></div>
</div>
    <h2> 2023 年 壽山 1.05 ha 永久樣區 第 {{$entry}} 次資料輸入</h2>
    <div style='margin-top:10px'>
         <p>請先詳閱 <a href="{{--{{asset('/shoushan/plot/10m_note')}}--}}"><b>資料輸入注意事項</b></a></p>  
        <p> 輸入者 {{$user}}，輸入日期 {{date("Y-m-d")}}</p>
    </div>


    <div style='font-weight: 800; margin-bottom: 20px; display: inline-flex;' >
        <span style='margin-right: 20px;'>選擇要輸入的樣方</span>

    <form wire:submit.prevent='submitForm'>
        <select name="qx" class="fs100 entryqx" wire:model.defer='qx' style='height:25px;'>
            <option value=""></option>
        @for ($i=-4; $i<11;$i++)
                @php 
                        echo "<option value=".$i.">".$i."</option>";
                @endphp
         @endfor
        </select>-<select name="qy" class="fs100" wire:model.defer='qy' style='height:25px;'>
            <option value=""></option>
        @for ($i=13; $i<20;$i++)
        <option value="{{$i}}">{{$i}} 
        </option>
         @endfor
        </select>
        <button type="submit" style='margin-left: 20px;'>送出</button>

    </form>


        @if (!$record)
        <span style='padding-left:20px'>{{$entrynote}}</span>
        @else
@php
   if ($qy==13){$prevshow="prevhidden"; $nextshow='prevshow';} 
   else if ($qy==19){$prevshow='prevshow'; $nextshow='prevhidden';}
   else {$prevshow='prevshow'; $nextshow='prevshow';}
@endphp
            <span class='{{$prevshow}}'><a class='a_' wire:click.once="searchSite({{$qx}}, {{$qy-1}}, 1, 1)">上一個樣方</a></span>
            <span class='{{$nextshow}}'><a class='a_' wire:click.once="searchSite({{$qx}}, {{$qy+1}}, 1, 1)">下一個樣方</a></span>
        @endif

    </div>

    @if($record && $record!='')
@php
// print_r($record[1][1]);
$plot2list=array("11","12","22","21");
$nowplotkey=array_keys($plot2list, $sqx.$sqy);
$nowplot=$plot2list[$nowplotkey[0]];
$searchSiteVar=$qx.",".$qy;
$tableVar=$qx.$qy.$sqx.$sqy;
$alterOtherNote="";
@endphp
        <div class='text_box'>
           
            <h6>樣區環境資料</h6>
            <hr>
            <div id='simplenote' class='text_box2'>
                <ul>
                <li><b>輸入資料後需按 <button class='datasavebutton' style='width: auto;'>儲存</button> ，才能確實將資料儲存。</b></li>
                <li>各欄位皆須輸入數字。</li>
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
                    
                </ul>
            </div>
     
            
@include('includes.str-tree-entrytable')

@if($record!='無')

@include('includes.str-main-entrytable')

@endif
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



{{-- 
         @if($qx==10 && $qy==19)
        
        <div style='margin: 30px 0 0 30px;'>
        <button class='finish finishbutton' onclick="finish({{$entry}})">輸入完成</button>
        <span class='finishnote savenote'></span>
        </div>
        @endif --}}

    @endif
</div>
