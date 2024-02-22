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
<div>
<h2>每木調查資料輸入注意事項</h2>

<div class='note'>

    <ul>
        <li><b>輸入資料後需按 <button class='datasavebutton' style='width:auto'>儲存</button> 鈕才能確實將資料儲存。</b></li>
        <li><b>請確實依照紙本資料輸入，以減少兩次輸入的不一致。</b></li>
        <li><b>可利用「Tab」鍵和「上下左右」鍵在各輸入欄位間移動。</b></li>
        <li><b>每一 20×20 樣方輸入完成後，請按 <button class='finishbutton'>輸入完成</button> 來檢查資料是否輸入正確及完整。</b>若有錯誤，請一一更正。</li>
    </ul>
<div class='flex text_outbox' style="flex-direction: column;">
    <div class='text_box_note_out'>
    <div class='text_box text_box_note'>
        <h2>調查資料輸入</h2>
        <hr>
        <ol>
        <li>必要欄位皆需有值才會儲存，未符合資料會暫存於畫面中。</li>
        <li><b>Date</b> - 每一筆皆需填入調查日期。<b>未輸入日期(0000-00-00)則視同未輸入，不予儲存。</b></li>
        <li><b>Status</b> - 枝幹存在狀態代碼。<span class='line'>代碼間不可共存</span>。
            <ul>
                <li><b>0</b>：<span class='line'>全株</span>死亡。<span class='line'>同編號之所有枝幹</span>均為 0，code 欄留白，dbh 欄均為 0。</li>
                <li><b>-1</b>：<span class='line'>全株</span>失蹤。<span class='line'>同編號之所有枝幹</span>均為 -1，code 欄留白，dbh 欄均為 0。</li>
                <li><b>-2</b>：<span class='line'>全株</span>枝幹 dbh < 1 cm，但植株仍存活。代表整個植株脫離了樣區研究的取樣範圍。<span class='line'>同編號之所有枝幹</span>均為 -2，code 欄留白，dbh 欄均為 0。</li>
                <li><b>-3</b>：僅<span class='line'>該枝幹</span> dbh < 1 cm，但植株仍存活。<span class='line'>該枝幹</span> 記錄 -3，code 欄留白，dbh 欄為 0。</li>
                <li><b>留白</b>：正常情形，枝幹存活，也可以找到 POM 測量 dbh。該枝幹的 dbh 一定 ≥ 1 cm。</li>
            </ul>
        </li>
        <li><b>code</b> - 特殊狀態代碼。<span class='line'>代碼間可共存</span>，多碼時<span class='line'>照字母排列</span>，<span class='line'>中間不留空格</span>。
            <ul>
                <li><b>C</b>(Change)：更改 POM 。要在 note 欄說明更換 POM的原因，並記錄新 POM 的長度。</li>
                <li><b>I</b>(Irregular)：不正常 POM 。</li>
                <li><b>P</b>(Prostrate)：枝幹倒伏 。</li>
                <li><b>R</b>(Ramet)：無性拓殖分株。已與主幹分離，獨立存活的分枝。<span class='line'>只能記錄在分枝</span>。若無性拓殖分株上有多個分枝時，R 只記錄在「著地」的那一個主要分枝，並要在 note 欄記錄「包含分枝#」。</li>
            </ul>
        </li>
        <li><b>dbh/h高</b> - 填入測量值至<span class='line'>小數點後一位</span>。
            <ul>
                <li>若無測量(即有Status需要紀錄)，則填入<b>0</b>。</li>
                <li>一般喬木/灌木填入dbh，樹蕨則填入h高。</li>
                <li>dbh需 ≥ 上次調查之dbh，若有縮小，則勾選「縮水」。</li>
                <li>若此次調查植株縮水至 dbh < 1 cm，原dbh欄位仍填寫1，並另在 dbh(<1)欄位中填寫此次調查數值。</li>
                <li>若發現此次調查與上次調查結果有不正常的變化，如縮水很多或是增長過多，請查閱上次紙本以檢查是否有輸入錯誤。</li>
            </ul>
        </li>
        <li><b>POM</b> - dbh 測量高度，預設值為原 pom。
            <ul>
                <li>若有更改，即 code 欄填入C，並更新 pom 長度。</li>
                <li>若調查發現現場調查之 pom 與原 pom 不同者(並非於這次修改 pom )，<span class='line'>請於「特殊修改<i class='fa-regular fa-note-sticky'></i>」欄位說明</span>，而不在 pom 欄位做修改。</li>
                <li>請在輸入時注意，若原始 note 有記錄 pom 位置，而與輸入資料中的 pom 不合，請於「特殊修改<i class='fa-regular fa-note-sticky'></i>」欄位說明。並將 note 中的 pom 備註刪除。</li>
            </ul>
        <li><b>note</b> - 註記。
            <ul>
                <li>若有縮水則勾選「縮水」，若需查舊資料則勾選「查舊」，此兩項不需在 note 欄輸入。</li>
                <li>pom 若有更新，則在 pom 欄位輸入，不需在 note 欄輸入。若原始 note 欄位中有 pom 備註，請將其刪除。</li>
                <li>「主幹牌移至分支#」統一寫為「TAB=#」。</li>
                <li>請檢視原始 note 是否仍需完全保留。再輸入此次新增之 note (列於原始 note 之後)。</li>
                <li>統一使用<b>「中文」標點符號</b>。<b>「半形」英文符號</b>。<b>「半形」阿拉伯數字</b>。</li>
                <li>新舊 note 以句號「。」相隔。多個分支以「、」相隔，如：1、2、3...。不同類型的 note 之間以「。」相隔。數字與單位間包含一個空格，如：1 cm。數字和<、=、>之間不需空格。</li>
                <li>特例：座標寫法為「(1, 1)」。括號用半形「()」。</li>
                <li>【常用note 縮寫】
                    <ol>
                        <li>縮水－「S」</li>
                        <li>凹凸不平：凹凸－「I」；凸－「I+」；凹－「I-」</li>
                        <li>長不定根－「AR」</li>
                        <li>頭尾接地生根－「GR」</li>
                        <li>全株接地生根－「GR+」</li>
                        <li>根拔－「U」</li>
                        <li>DBH<1cm之R分株：
                            <ul>
                                <li>note欄寫「(方位x.x m, 方位x.x m)r」</li>
                                <li>方位寫中英皆可</li>
                                <li>現場蘭花牌建議寫法「xxxxxx <1cm R」</li>
                            </ul>
                        </li>

                    </ol>


                </li>
            </ul>
        </li>
        <li><b>縮水</b> - 本次調查所得之 dbh 若小於上次調查之 dbh，則勾選「縮水」。</li>
        <li><b>查舊</b> - 本次調查之狀態與前次調查不符，需調閱資料確定者，請勾選「查舊」。</li>

        </li>
        <li><b>特殊修改<i class='fa-regular fa-note-sticky'></i></b> - 需更新原始資料的修改。包含：<span class='line'>樣區，號碼，種類，原始 pom。</span>。
            <ul>
            <li>將欲修改的欄位，分別填入格子內。僅需填寫需修改的欄位。</li>
            <li>如原為分支而此次要改為單獨編號之個體，除在特殊修改號碼欄位填寫新號碼外，亦需將填寫b欄位為0。</li>
            <li>若此次調查植株縮水至 dbh < 1 cm，原dbh欄位仍填寫1，並另在 dbh(<1)欄位中填寫此次調查數值。</li>
            <li>若調查發現現場調查之 pom 與原 pom 不同者(並非於這次修改 pom)，<span class='line'>請將數值填入「特殊修改<i class='fa-regular fa-note-sticky'></i>」中的 原始 POM 欄位中</span>，而不在 POM 欄位做修改。</li>
            </ul>
        </li>

        <li><span class='line'>若有輸入的資料不符驗證規定，系統將會提示錯誤，請重新輸入。</span></li>   
         
        </ol>
    </div>
    <div class='text_box text_box_note'>
    <h2>新增樹與漏資料樹輸入</h2>
    <hr>
    <ol>
        <li><span class='line'>Date、樣區編號、tag、b、csp、dbh/h高 為<b>必要欄位</b>，缺少任一個值則將不予儲存。</span></li>
        <li><b>Date</b> - 每一筆皆需填入調查日期。</li>
        <li><b>樣區編號</b> - 填入小區的編號。限定數字為 1-4。不同小區的資料可同時輸入。</li>
            <ul>
                <li>新增分支的樣區編號需與主幹相同。</li>
                <li>如分支為R且跨樣區，仍須新增於主幹所在小區。並在note欄位註記「R在（3,1）」，地圖上則畫記在（3,1)。</li>
            </ul>
        <li><b>tag</b> - 每棵樹的編號。</li>
        <li><b>b</b> - 分支號。</li>
        <li><b>csp</b> - 樹種中文名稱。
            <ul>
                <li>限定為樣區樹種名錄的110種。<span class='line'>若有新增種類，請洽管理員或填寫輸入錯誤表單。</span></li>
                <li>設有「自動完成」功能，只要輸入第一個字，即會出現同字首種類之選單，按向下鍵或用滑鼠即可選擇。</li>
                <li>在格子內點滑鼠兩下或是按 Enter 鍵即會出現名錄，依據種種於樣區的數量做排序。</li>
            </ul>
        </li>
        <li><b>code</b> - 特殊狀態代碼。<span class='line'>代碼間可共存</span>，多碼時照字母排列，<span class='line'>中間不留空格</span>。
            <ul>
                <li><b>I</b>(Irregular)：不正常 POM 。</li>
                <li><b>P</b>(Prostrate)：枝幹倒伏 。</li>
                <li><b>R</b>(Ramet)：無性拓殖分株。已與主幹分離，獨立存活的分枝。只能記錄在分枝。若無性拓殖分株上有多個分枝時，R 只記錄在「著地」的那一個主要分枝，並要在 note 欄記錄「包含分枝#」。</li>
                <li>不得有C。</li>
            </ul>
        </li>
        <li><b>dbh/h高</b> - 填入測量值至小數點後一位。
            <ul>
                <li>一般喬木/灌木填入 dbh，樹蕨則填入 h高。</li>
                <li>dbh需 ≥ 1。</li>
            </ul>
        </li>
        <li><b>POM/h低</b> - dbh 測量高度，預設值為1.3。
            <ul>
                <li>一般喬木/灌木填入 POM，<span class='line'>樹蕨則填入 h低</span>。樹蕨的 h低 即為 dbh 測量高度。</li>
            </ul>
        </li>
        <li><b>note</b> - 註記。
            <ul>
                <li>若為漏資料則勾選「漏資料」，不需在note欄輸入。</li>
                <li>統一使用<b>「中文」標點符號</b>。<b>「半形」英文符號</b>。<b>「半形」阿拉伯數字</b>。</li>
                <li>新舊 note 以句號「。」相隔。多個分支以「、」相隔，如：1、2、3...。不同類型的 note 之間以「。」相隔。數字與單位間包含一個空格，如：1 cm。</li>
                <li>特例：座標寫法為「(1, 1)」。括號用半形「()」。</li>
            </ul>
        </li>
        <li>未通過檢查以致無法儲存的資料將保留在輸入表單中，而點選 <button>輸入完成</button> 鈕後即會刪除。</li>
        <li>輸入表單中的空行並不影響資料儲存。</li>
        <li><b>新樹輸入補充說明</b> -
            <ul>
                <li>在按儲存後，系統會將各個新樹分配到所屬的 5×5 小區內，並可以進行<b>修改</b>及<b>刪除</b>。</li>
            </ul>
        </li>
        <li><b>漏資料樹輸入補充說明</b> -
            <ul>
                <li>在按儲存後，系統會將各個樹分配到所屬的 5×5 小區內，視為舊樹，無法修改及刪除。</li>
                <li><span class='line'>若樣區編號、tag、b、csp等欄位與原始資料不同，則不予儲存，請確認編號，或洽管理員或填寫輸入錯誤表單。</span></li>
                <li>若為原始資料中沒有的樹，則不予儲存，請確認是否為漏資料，或洽管理員或填寫輸入錯誤表單。</li>
                <li>如需更動資料或刪除，請洽管理員或填寫輸入錯誤表單。</li>
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
        <li>上一次該樣木調查的 POM 並不是在 1.3，但原始資料顯示在 1.3，然而這次調查有修改 POM 且剛好新 POM=1.3。<br>
            <ul>
                <li>請資料管理者修改原始 POM 的數值，再輸入此次資料。</li>
            </ul>
        </li>
        <li>有一個紀錄紙上沒有的資料，本次調查發現這棵樹還活著，只是現在的status是-2。
            <ul>
                <li>請先依照規則新增這筆資料進去，dbh先填1，勾選漏資料，儲存，然後系統會把資料叫出來，再將資料修改成status=-2，dbh=0。</li>
            </ul>
        </li>
        <li>
            若調查後的 dbh < 1 cm，請在表格內填寫 1，再至「特殊修改<i class='fa-regular fa-note-sticky'></i>」的 dbh(<1)欄位填寫正確之調查資料。
        </li>   
        <li>遇到舊樣木復活，但無法確認樹種。
            <ul>
                <li>請查閱舊資料電子檔，或是請資料管理者進後端確認樹種。</li>
            </ul>
        </li>
        <li>如遇到被認為死亡的樹有新增枝幹，請新增主幹並勾選漏資料，主幹若死亡，status = -3。</li>

        </ol>
    </div>
    <div class='text_box text_box_note' >
        <ul>
        <li><b>每一 20×20 樣方輸入完成後，請按 <button class='finishbutton'>輸入完成</button> 來檢查資料是否輸入正確及完整。</b>若有錯誤，請一一更正。
            <ol>
            <li>輸入完成後將檢查 - 
                <ol>
                    <li>每一筆資料是否都有日期，亦即皆有輸入。</li>
                    <li>每棵樹的 status 需相同(-3除外)。</li>
                    <li>每棵樹的 csp 需相同。</li>
                </ol>
            </li>   
            </ol>
        </li>
        <li>只要有更新輸入資料，皆需按 <button>輸入完成</button> 已紀錄輸入完成。</li>
        <li><b>兩次資料輸入皆完成後，請選擇「資料比對」來檢查資料，若有不相符的資料請一一改正。最後確定沒問題後，請通知資料管理員。</b></li>
        </ul>
    </div>
    </div>
</div>
</div>
</div>
@endsection