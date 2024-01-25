<div>
    <h2>檢視 / 更新資料</h2>
    <div class='text_box'>
        選擇要檢視 / 更新的 census： <select name="census" class="fs100"  wire:model='selectCensus' wire:change="searchCensus($event.target.value)">
             <option value=""> 
                
            </option>
            @for ($i=0; $i<count($censuslist);$i++)
 
            <option value="{{$censuslist[$i]}}">{{$censuslist[$i]}}</option>

             @endfor
        
            </select>
    </div>

    @if($censusdata!=[])
    <div class='text_box entrytableout'>
        <h6>第 {{$selectCensus}} 次 調查資料檢視 / 更新</h6>
       
        <div id='simplenote' class='text_box'>
            <ul>
            <li><b>輸入資料後需按 <button class='datasavebutton' style='width: auto;'>儲存</button> ，才能確實將資料儲存。</b></li>
            <li>Trap欄位會自動在左側補0。</li>
            <li>若出現新增種類，請洽管理員更新物種名單。</li>
            <li>不確定種類，一律輸入「<b>UNKUNK</b>」，並將疑似種類名稱寫在 note。</li>
            <li>若為小種子植物的果實或種子，無法計算種子數量，種子數及活性欄位皆填NA。</li>
            <li>不需記錄種子數、活性、碎片3數量時，可填入 0 或保留空白(系統會自動補 0)。</li>
            <li><span class='line'>長葉木薑子</span>的花，需記錄性別 F / M。</li>
            <li>若不符合規則，會在檢查欄位顯示錯誤之處。</li>
            <li><b>更新資料即為更新大表，請小心謹慎。</b></li>
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

                <div id='seedstable{{$selectCensus}}' class='fs100' >
                    <span class='seedssavenote savenote'></span>
                    <p style='margin-top:5px; text-align: center'><button name='datasave2{{$selectCensus}}' class='datasavebutton' style='width:550px'>儲存</button></p>

                </div>
            </div>
            <div id='seedstableout_empty' class='seedstable fs100'>
                <div class='pages'>
                    <button name='show_seedstable'>檢視輸入資料</button>
                </div>
                <div id='seedstable_empty{{$selectCensus}}' class='fs100' >
                     
                    <p style='margin-top:5px; text-align: center'><button name='newdatasave2{{$selectCensus}}' class='datasavebutton' style='width:550px'>儲存</button></p>

                </div>
            </div>
        </div>
    </div>
    @endif
</div>
