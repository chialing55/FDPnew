@extends('layouts/tree') 
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

<h2>每木調查資料輸入注意事項</h2>

<div class='note'>

    <ul>
        <li><b>輸入資料後需按「儲存」鈕才能確實將資料儲存。</b></li>
        <li><b>可利用「Tab」鍵和上下左右鑑在各輸入欄位間移動。</b></li>
    </ul>
<div class='flex text_outbox' style="flex-direction: column;">
    <div class='text_box_note_out'>
    <div class='text_box text_box_note'>
        <h2>調查資料輸入</h2>
        <hr>
        <ol>
        <li><b>Date</b> - 每一筆皆需填入調查日期。<b>未輸入日期則視同未輸入，不予儲存。</b></li>
        <li><b>Status</b> - 枝幹存在狀態代碼。<span class='line'>代碼間不可共存</span>。
            <ul>
                <li><b>0</b>: <span class='line'>全株</span>死亡。<span class='line'>所有枝幹</span>的均為 0，code 欄留白，dbh 欄均為 0。</li>
                <li><b>-1</b>: <span class='line'>全株</span>失蹤。<span class='line'>所有枝幹</span>的均為 -1，code 欄留白，dbh 欄均為 0。</li>
                <li><b>-2</b>: <span class='line'>全株</span>枝幹 dbh < 1 cm，但植株仍存活。代表整個植株脫離了樣區研究的取樣範圍。<span class='line'>所有枝幹</span>均為 -2，code 欄留白，dbh 欄均為 0。</li>
                <li><b>-3</b>: 僅<span class='line'>該枝幹</span> dbh < 1 cm，但植株仍存活。<span class='line'>該枝幹</span> 記錄 -3，code 欄留白，dbh 欄為 0。</li>
                <li><b>留白</b>: 正常情形，枝幹存活，也可以找到 POM 測量 dbh。該枝幹的 dbh 一定 ≥ 1 cm。</li>
            </ul>
        </li>
        <li><b>code</b> - 特殊狀態代碼。<span class='line'>代碼間可共存</span>，多碼時照字母排列，<span class='line'>中間不留空格</span>。
            <ul>
                <li><b>C</b>: 更改 POM (Change)。要在 note 欄說明更換 POM的原因，並記錄新 POM 的長度。</li>
                <li><b>I</b>: 不正常 POM (Irregular)。</li>
                <li><b>P</b>: 枝幹倒伏 (Prostrate)。</li>
                <li><b>R</b>: 無性拓殖分株。已與主幹分離，獨立存活的分枝(Ramet)。只能記錄在分枝。若無性拓殖分株上有多個分枝時，R 只記錄在「著地」的那一個主要分枝，並要在 note 欄記錄「包含分枝#」。</li>
            </ul>
        </li>
        <li><b>dbh/h高</b> - 填入測量值至小數點第一位。
            <ul>
                <li>若無測量(即有Status需要紀錄)，則填入<b>0</b>。</li>
                <li>一般喬木/灌木填入dbh，樹蕨則填入h高。</li>
                <li>dbh需 ≥ 上次調查之dbh，若有縮小，則勾選「縮水」。</li>
            </ul>
        </li>
        <li><b>POM</b> - dbh測量高度，預設值為原 pom。
            <ul>
                <li>若有更改，即 code欄填入C，則需填入新的 pom 長度。</li>
                <li>若調查發現現場調查之pom與原pom不同者(並非於這次修改pom)，<span class='line'>請於「特殊修改」欄位說明</span>，而不在pom欄位做修改。</li>
                <li>請在輸入時注意，若原始note有記錄pom位置，而與輸入資料中的pom不合，請於「特殊修改」欄位說明。(因為當初是手動更新，恐有遺漏。)</li>
            </ul>
        <li><b>note</b> - 註記。
            <ul>
                <li>若有縮水則勾選「縮水」，若需查舊資料則勾選「查舊」，此兩項不需在note欄輸入。</li>
                <li>pom若有更新，則在pom欄位輸入，不需在note欄輸入。</li>
                <li>「主幹牌移至分支#」統一寫為「TAB=#」。</li>
                <li>統一使用<b>「中文」標點符號</b>。<b>「半形」英文符號</b>。<b>「半形」阿拉伯數字</b>，並於數字後留一格空白。</li>
                <li>先輸入原始note，加句號，再輸入手寫note。</li>
            </ul>
        </li>
        <li><b>縮水</b> - 本次調查所得之 dbh 若小於上次調查之 dbh，則勾選「縮水」。</li>
        <li><b>查舊</b> - 本次調查之狀態與前次調查不符，需調閱資料確定者，請勾選「查舊」。</li>

        </li>
        <li><b>特殊修改</b> - 需更新原始資料的修改。包含: <span class='line'>更改樣區，更改號碼，更改種類，更改原始pom。</span>。
            <ul>
            <li>將欲修改的欄位，分別填入格子內。</li>
            <li>若調查發現現場調查之pom與原pom不同者(並非於這次修改pom)，<span class='line'>請將數值填入「特殊修改」中的 POM 欄位中</span>，而不在 POM 欄位做修改。</li>
            </ul>
        </li>

        <li><span class='line'>若有輸入的資料不符驗證規定，系統將會提示錯誤，請重新輸入。若無更正，則無法移動到下一區。</span></li>   
         
        </ol>
    </div>
    <div class='text_box text_box_note'>
    <h2>新增樹與漏資料樹輸入</h2>
    <hr>
    <ol>
        <li><span class='line'>Date、樣區編號、tag、b、csp、dbh/h高為<b>必要欄位</b>，缺少任一個值則將不予儲存。</span></li>
        <li><b>Date</b> - 每一筆皆需填入調查日期。</li>
        <li><b>樣區編號</b> - 填入小區的編號。限定數字為1-4。不同小區的資料可同時輸入。</li>
        <li><b>tag</b> - 每棵樹的編號。</li>
        <li><b>b</b> - 分支號。</li>
        <li><b>csp</b> - 樹種中文名稱。
            <ul>
                <li>限定為樣區樹種名錄的110種。<span class='line'>若有新增種類，請洽管理員。</span></li>
                <li>設有「自動完成」功能，只要輸入第一個字，即會出現同自首物種之選單，按向下鍵或用滑鼠即可選擇。</li>
                <li>在格子內點滑鼠兩下或是案向下鍵及會出現樣區數量前10多的種類可選擇。</li>
            </ul>
        </li>
        <li><b>code</b> - 特殊狀態代碼。<span class='line'>代碼間可共存</span>，多碼時照字母排列，<span class='line'>中間不留空格</span>。
            <ul>
                <li><b>I</b>: 不正常 POM (Irregular)。</li>
                <li><b>P</b>: 枝幹倒伏 (Prostrate)。</li>
                <li><b>R</b>: 無性拓殖分株。已與主幹分離，獨立存活的分枝(Ramet)。只能記錄在分枝。若無性拓殖分株上有多個分枝時，R 只記錄在「著地」的那一個主要分枝，並要在 note 欄記錄「包含分枝#」。</li>
                <li>不得有C。</li>
            </ul>
        </li>
        <li><b>dbh/h高</b> - 填入測量值至小數點第一位。
            <ul>
                <li>一般喬木/灌木填入dbh，樹蕨則填入h高。</li>
                <li>dbh需 ≥ 1。</li>
            </ul>
        </li>
        <li><b>pom/h低</b> - dbh測量高度，預設值為1.3。
            <ul>
                <li>一般喬木/灌木填入dbh，<span class='line'>樹蕨則填入h低</span>。樹蕨的h低即為dbh測量高度。</li>
            </ul>
        </li>
        <li><b>note</b> - 註記。
            <ul>
                <li>若為漏資料則勾選「漏資料」，若需查舊資料則勾選「查舊」，此兩項不需在note欄輸入。</li>
                <li>統一使用<b>「中文」標點符號</b>。<b>「半形」英文符號</b>。<b>「半形」阿拉伯數字</b>，並於數字後留一格空白。</li>
            </ul>
        </li>
        <li>未通過檢查以致無法儲存的資料將保留在輸入表單中，而點選「輸入完成」鈕後即會刪除。</li>
        <li>輸入表單中的空行並不影響資料儲存。</li>
        <li><b>新樹輸入補充說明</b> -
            <ul>
                <li>在按儲存後，系統會將各個新樹分配到不同的5×5小區內，並為可以<b>修改</b>及<b>刪除</b>的狀況。</li>
            </ul>
        </li>
        <li><b>漏資料樹輸入補充說明</b> -
            <ul>
                <li>在按儲存後，系統會將各個樹分配到不同的5×5小區內，視為舊樹，無法修改及刪除。。</li>
                <li><span class='line'>若樣區編號、tag、b、csp等欄位與原始資料不同，則會在特殊修改欄位中註記，請確認是否需要更新。</span>若確定要更新以上欄位，則請在特殊修改欄位中輸入更新的內容。若確定不用更新，則可刪除註記。</li>
                <li>若為原始資料中沒有的樹，則會新增，並在特殊修改欄位中註記「查無此樹」。</li>
                <li>如需更動資料或刪除，請洽資料管理員。</li>
            </ul>       
        </li>
    </oi>
    </div>
    </div>
    <div class='text_box_note_out'>
    <div class='text_box text_box_note' >
    <h2>特殊輸入說明</h2>
    <hr>
        <ol>
        <li>上一次該樣木調查的POM並不是在1.3，但原始資料顯示在1.3，然而這次調查有修改POM且剛好新POM=1.3。<br>
            <ul>
                <li>先不要在code欄輸入"C"，而是讓它空白，然後POM處也不要修改，但是在"查舊"那欄打勾。</li>
                <li>如果資料輸入者非調查者或紀錄者，請先詢問調查者與紀錄者是否有印象原始POM在哪裡，有的話就在紀錄紙上標示。沒有的話就請調查者與紀錄者回樣區確認原始POM的位置並記錄在紀錄紙上。</li>
                <li>接著，請資料管理者修改原始POM的數值，之後再進系統把code欄填入"C"，並把此次的POM位置輸入即可。</li>
            </ul>
        </li>
        <li>有一個紀錄紙上沒有的資料，本次調查發現這棵樹還活著，只是現在的status是-2。
            <ul>
                <li>請先依照規則新增這筆資料進去，dbh先填1，勾選漏資料，儲存，然後系統會把資料叫出來，再將資料修改成status=-2，dbh=0。</li>
            </ul>
        </li>   
        <li>遇到舊樣木復活，但不知道樹種名。
            <ul>
                <li>樹種名先輸入unknown，在特殊修改欄位會出現 [本次資料的 csp 與原始資料不合，請檢查。]，再將特殊修改欄位的內容刪除。</li>
            </ul>
        </li>
        </ol>
    </div>
    <div class='text_box text_box_note' >
        <ul>
        <li><b>每一20×20樣方輸入完成後，請按「輸入完成」按鈕來檢查資料是否輸入正確及完整。</b>若無法通過檢查，請將錯誤一一更正，否則無法進入下一個樣方。
            <ol>
            <li>輸入完成後將檢查 - 
                <ol>
                    <li>每一筆資料是否都有日期，亦及皆有輸入。</li>
                    <li>每棵樹的status需相同(-3除外)。</li>
                    <li>每棵樹的csp需相同。</li>
                </ol>
            </li>   
            </ol>
        </li>
        <li><b>兩次資料輸入皆完成後，請選擇「資料比對」來檢查資料，若有不相符的資料請一一改正。最後確定沒問題後，請通知資料管理員。</b></li>
        </ul>
    </div>
    </div>
</div>
</div>

@endsection