@extends('layouts/seedling') 
@section('pagejs')
<script>
  // var element = document.getElementById("#list1");
  // element.classList.add("now");
// $('.list41').addClass('now');
// $('.list4').addClass('listhover');
// $('.list4').unbind();
// $('.list4inner').unbind();
// $('.list4inner').css('display', 'inline-flex');
// $('.list41 hr').css('color', '#91A21C');

$(function() {
  $('.list4').addClass('now');
  $('.list4 hr').css('color', '#91A21C');
})




</script>

@endsection
@section('rightbox')

<h2>小苗輸入注意事項</h2>
<div class='note'>
    <ul style='font-weight: 800;'>
    <li>輸入資料後需按<button class='datasavebutton' style='width:auto'>儲存</button>才能確實將資料儲存。</li>
    <li>可利用「Tab」鍵和「上下左右」鍵在各輸入欄位間移動。</li>
    <li>全樣區輸入完成後，請按<button class='finishbutton'>輸入完成</button>來檢查資料是否輸入正確及完整。</li>
    <li>兩次資料輸入皆完成後，請選擇<a href="{{asset('/fushan/seedling/compare')}}" target="_blank">資料比對</a>來檢查資料，若有不相符的資料請一一改正。最後確定沒問題後，請通知資料管理員。</li>
    </ul>
<div class='flex text_outbox' style="flex-direction: column;">
    <div class='text_box_note_out'>
    <div class='text_box text_box_note'>    

        <h2>樣站環境資料輸入</h2>
            <ol>
                <li><b>Date</b> - 每一筆皆需填入調查日期。未輸入日期(0000-00-00)則視同未輸入，不予儲存。</li>
                <li><b>覆蓋度</b> - 輸入調查值。</li>
                <li><b>樣區上方光度</b> - <b>U</b>: 多層樹冠，<b>I</b>: 一層樹冠，<b>G</b>: 沒有樹冠。 </li>
                
            </ol>
    </div>
<div class='text_box text_box_note'>
    <h2>撿到環輸入</h2>
        <ol>
        <li>不同樣區的資料亦可在同一表格內輸入。所有資料將一起呈現。可修改或是刪除。</li>
        <li><span class='line'>若有任一欄位為空白 (Note除外)，系統將不予儲存，請重新輸入。</span></li>
        </ol>
    </div>
</div>
    <div class='text_box_note_out'>    
    <div class='text_box text_box_note'>    
    <h2>小苗調查資料輸入</h2>
        <ol>
            <li><b>Date</b> - 每一筆皆需填入調查日期。</li>
            <li><b>長度</b> - 填入測量值至小數點第一位。長度大於 200 公分者填入 999。<br>
                <span >若無測量則填入代碼: <br>
                <b>-1</b>: 沒有測量。<br>
                <b>-2</b>: DBH >= 1。<br>
                <b>-4</b>: 見環不見苗或死亡。<br>
                <b>-6</b>: 消失。<br>
                <b>-7</b>: 主幹或分枝死亡但個體存活</span>。<br>
                <span class='line'>長度欄位<b>不可為 0</b></span>。</li>
            <li><b>子葉數</b> - 填入子葉數。子葉數最多兩片，若無子葉者填 0。<span class='line'>若無測量，需填入代碼，且代碼需與長度欄的代碼相同</span>。</li>
            <li><b>真葉數</b> - 填入真葉數。真葉數大於 50 片者填入 99。<span class='line'>若無測量，需填入代碼，且代碼需與長度欄的代碼相同</span>。</li>
            <li><b>Note</b> - 依記錄修改或新增。<span class='line'>句子內用中文逗號「，」，句子間的聯結為中文句號「。」，<b>不留任何空格</b>。<br><b>部分暫記在Note欄的內容不需輸入</b>，如：</span>
                <ul>
                    <li>需填寫在「特殊修改<i class='fa-regular fa-note-sticky'></i>」內。如: 改物種、改為xxxx的分枝、修正座標、修正樣方、改舊資料。</li>
                    <li>主幹死亡分枝換位置，請直接修改主幹的座標。</li>
                    {{-- <li>跟植株狀態及下次調查無關者，例如：確認/confirm/con、復活、舊苗/漏做/漏資料(新舊選O)、和xxxx同物種、查舊資料(如果兩次輸入的人都沒辦法查詢要另外記錄確認)。</li> --}}
                    <li>撿到環/撿到牌/回收，另填寫在撿到環表單中。</li>
                </ul>
            </li>
            <li><b>狀態</b> - 記錄存活狀態，以<b>個體</b>為單位，分枝狀態需與主幹狀態相同。<span class='line'>預設為 <b>A</b> (存活)</span>。其他代碼: <b>G</b>: 見環不見苗，<b>D</b>: 死亡，<b>N</b>: 消失。
                <ul>
                    <li>同一個體(萌糵與主幹)需為同一狀態。即當萌糵或主幹死亡，長度等單位記為 -7，狀態欄仍為 A。</li>
                    <li>當找不到萌糵苗時，若該個體還存活，則在萌糵苗的長度等欄位記錄 -7，不記 -6。且狀態欄仍為 A。</li>
                    <li>當主幹或萌糵的 DBH > 1 時，長度等欄位記錄 -2，狀態欄仍為 A (分支狀態需與主幹同)。</li>
                   {{-- 取消狀態欄的L代碼，因主幹需與分支同狀態，改以-2為DBH>1的標準(2023/10/16) --}}
                </ul>
            </li>
            <li><b>新增</b> - 記錄小苗為新增苗或舊苗或是大樹。R: 新增；O: 舊苗；T: 大樹。輸入表單鎖定不得修改。
            </li>
            <li><b>萌櫱</b> - 記錄小苗為種子苗或是萌糵分支。輸入表單鎖定不得修改。
                <ul>
                    <li>若為萌櫱苗，編號為: <span class='line'>主幹小苗編號.分枝編號</span>。</li>

                </ul>
            </li>
        <li><b>特殊修改</b> - 需更新原始資料的修改。包含: <span class='line'>更改樣站，更改種類，換號碼，重新找到原本消失的小苗，舊記錄輸入錯誤</span>。:
            <ol>
            <li>將欲修改的欄位，分別填入格子內。</li>
            <li><span class='line'>當原本消失的小苗被重新找到時，狀態欄可直接更改為 A 或是 D、G，程式會自動在特殊修改中註記「狀態:N→A」「狀態:N→G」「狀態:N→D」，以提醒管理者更改之前調查的原始資料。</span>若小苗並非還活著，通常會是 G (見環不見苗) 的情形居多，請注意。</li>
            
            </ol>
         
        </li>
        <li><b>特殊情況處理</b> :<br>
            <ol>
                <li><b>該小苗更改植物種類，但有新增萌糵苗，造成種類名稱不同</b>：新增萌糵苗的種類先輸入原有種類，並在主幹的note欄位備注「有新增萌糵苗需改種類名稱」。
                </li>
                <li><b>原本的子葉數為0 (0+5)，但此次調查判斷應有子葉 (2+4)</b>：在特殊修改中的原葉片數欄位填入 2+3，這次的子葉數寫在 note「子葉數應為2」(原子葉數為0，無法輸入大於原子葉數的資料)。因這種情形不多見，故不另開設欄位。</li>
            </ol>
        
        </li>
          <li><span class='line'>若有輸入的資料不符驗證規定，系統將拒絕儲存，請重新輸入。</span></li>   
         
        </ol>
    </div>

    <div class='text_box text_box_note'>
    <h2>新增小苗輸入</h2>
    <hr>
        <ol>
            <li><b>Date</b> - 每一筆皆需填入調查日期。</li>
            <li><b>Plot</b> - 每一筆皆需填入小苗所在的小苗區。</li>
            <li><b>Tag</b> - 需注意小苗編號不得重複。若號碼重複，系統將會拒絕儲存資料。
                <ul>
                    <li>若為新增萌櫱苗，編號為: <span class='line'>主幹小苗編號.分枝編號</span>。如 1201.2。</li>
                    {{-- <li>如有多棵小苗 (以 3棵為例) 需用同一個號碼，小苗編號方式為 A001，A001<b>.1</b>，A001<b>.2</b>(以此類推)。<span class='line'>並在 A001 的 Note欄位加註「3棵」，以提供下次調查時辨識。</span></li> --}}
                </ul>
            </li>
            <li><b>種類</b> - 有選單可選擇植物中文名，排序依據為小苗數量。輸第一個字後會出現同樣字首的種類供選擇。可以輸入選單以外的種類。</li>
            <li><b>長度</b> - 填入測量值至小數點第一位。</li>
            <li><b>子葉數</b> - 填入子葉數。子葉數最多兩片，若無子葉者填 0。</li>
            <li><b>真葉數</b> - 填入真葉數。真葉數大於 50 片者填入 99。</li>
            <li><b>Note</b> - 依記錄修改或新增。<span class='line'>句子內用中文逗號「，」，句子間的聯結為中文句號「。」，<b>不留任何空格</b>。</span></li>
            <li><b>新增</b> - 預設為 <b>R</b> (Recruit 新增苗)。
                <ul>
                    <li>若判定為<span class='line'>漏作的小苗，新增狀態請選擇 <b>O</b> (Old seedling 舊苗)</span>，並在 <span class='line'>Note欄註記「漏作」</span>。</li>
                    <li>若為因<span class='line'>有新增萌櫱苗而加入調查範圍的樹(DBH >= 1)，則新增狀態為 <b>T</b> (Tree 樹)，長度、子葉數及真葉數欄位記為 <b>-2</b></span>。</li>
                </ul>
                
            </li>
            <li><b>萌櫱</b> - 預設為 <b>False</b>，若為新增的萌櫱苗，請選為 <b>True</b>。</li>
            <li><span class='line'>若有任一欄位為空白 (Note除外) 或輸入的資料不符驗證規定，系統將拒絕儲存，請重新輸入。</span></li>
            <li>儲存後的資料將會進入小苗調查資料內，依 Plot 和 Tag 排序，可更改輸入資料或刪除資料。</li>
            <li><b>如果為已死小苗的新增萌櫱(資料中無主幹資料)，請通知資料管理員。</b></li>
        </ol>

    </div>

</div>    

    

</div>
</div>
@endsection