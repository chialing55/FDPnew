@extends('layouts/seeds') 
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
<h2>種子雨輸入注意事項</h2>
<div class='note'>
    <ul style='font-weight: 800;'>
    <li>輸入資料後需按<button class='datasavebutton' style='width:auto'>儲存</button>才能確實將資料儲存。</li>
    <li>可利用「Tab」鍵和「上下左右」鍵在各輸入欄位間移動。</li>
    
    <li>輸入種子雨收集日期及調查人員後，即可進入資料輸入畫面。</li>
    <li>輸入資料畫面分為「空白頁面」及「已輸入資料」頁面，可點選按鈕切換。
        <ul>
            <li>從「空白頁面」輸入資料並儲存後，即會在「已輸入資料」頁面看到輸入結果。</li>
            <li>「已輸入資料」頁面可更新或刪除資料。</li>
            <li>無論正確與否，皆會儲存，但未通過輸入檢查者，會在欄位上顯示錯誤資訊，請依提示修改資料至正確。</li>
            <li>此次資料若未輸入完成，可在下次繼續輸入。</li>
        </ul>
    </li>

    <li>輸入完成後，請按<button class='finishbutton'>輸入完成</button>來檢查資料是否輸入正確及完整。待通過檢查後，會將資料匯入大表，並清空輸入表單，即可進行下一周之資料數入。</li>    
    </ul>
<div class='flex text_outbox' style="flex-direction: column;">

    <div class='text_box_note_out'>    
    <div class='text_box text_box_note'>    
    <h2>資料輸入欄位說明</h2>

        <ol>
            <li><b>Trap</b> - Trap欄位會自動在左側補0，故只需輸入數字即可。</li>
            <li><b>種類</b> - 輸入物種中文名。
                <ul>
                    <li>為避免錯字，鎖定只能輸入名單內的種類，若出現新增種類，請洽管理員更新物種名單。</li>
                    <li>不確定種類，一律輸入「<b>UNKUNK</b>」，並將疑似種類名稱寫在 note。</li>
                </ul>
            </li>
            <li><b>類別</b> - <br>
                <b>1：成熟果實。</b>
                    <ul>
                        <li>若為大種子種類，需記錄種子數。<span class='line'>種子數不可小於果實數</span>。</li>
                        <li>若為大種子種類，需記錄種子活性，<span class='line'>活性數不可大於種子數</span>。若為「<b>九芎、凹葉越橘、五節芒、UNKCOM1、UNKCOM2、UNKCOM3</b>」，活性數記 <b>NA</b>。</li>
                        <li><span class='line'>若為小種子種類，種子數及活性數皆記為 <b>NA</b>。</span></li>
                        <li>碎片3數量可為 0 或空白(系統會自動補0)。</li>
                        <li>性別欄位需為空白。</li>
                    </ul> 
                <b>2：成熟種子。應皆為大種子種類。</b>
                    <ul>
                        <li>需記錄種子數。<span class='line'>種子數需與數量欄位相等</span>。</li>
                        <li>需記錄種子活性，<span class='line'>活性數不可大於種子數</span>。若為「<b>九芎、凹葉越橘、五節芒、UNKCOM1、UNKCOM2、UNKCOM3</b>」，活性數記 <b>NA</b>。</li>
                        <li>碎片3數量可為 0 或空白(系統會自動補0)。</li>
                        <li>性別欄位需為空白。</li>
                    </ul>
                <b>3：capsule。可能為果蒂、殼杯等。</b>
                    <ul>
                        <li>可由破碎的 capsule 組裝後獲得總數量。</li>
                        <li>種子數可為 0 或空白(系統會自動補0)。</li>
                        <li>種子活性可為 0 或空白(系統會自動補0)。</li>
                        <li><span class='line'>需記錄破碎的 3 的數量，不應大於總數量。</span></li>
                        <li>性別欄位需為空白。</li>
                    </ul>
                <b>4：碎片。除去capsule外的任何不完整的部分。</b>
                    <ul>
                        <li><span class='line'>不論多少，數量皆為 1。</span></li>
                        <li>種子數可為 0 或空白(系統會自動補0)。</li>
                        <li>種子活性可為 0 或空白(系統會自動補0)。</li>
                        <li>碎片3數量可為 0 或空白(系統會自動補0)。</li>
                        <li>性別欄位需為空白。</li>
                    </ul>
                <b>5：未成熟果實。</b>
                    <ul>
                        <li>種子數可為 0 或空白(系統會自動補0)。</li>
                        <li>種子活性可為 0 或空白(系統會自動補0)。</li>
                        <li>碎片3數量可為 0 或空白(系統會自動補0)。</li>
                        <li>性別欄位需為空白。</li>
                    </ul>
                <b>6：花。花苞不計。</b>
                    <ul>
                        <li><span class='line'>不論多少，數量皆為 1。</span></li>
                        <li>種子數可為 0 或空白(系統會自動補0)。</li>
                        <li>種子活性可為 0 或空白(系統會自動補0)。</li>
                        <li>碎片3數量可為 0 或空白(系統會自動補0)。</li>
                        <li><span class='line'>若種類為「長葉木薑子」，需記錄花朵性別(F/M)。</span>其餘種類則性別欄位需為空白。</li>
                    </ul>

            </li>
            <li><b>數量</b> - 若類別為 4 或 6，不論多少，數量皆為 1。</li> 
            <li><b>種子數</b> - 類別為 1 或 2 時，才需記錄種子數(小種子種類記 NA)，其餘類別皆記為 0 或空白(系統會自動補0)。</li>
            <li><b>活性</b> - 類別為 1 或 2 時，才需記錄種子活性(小種子種類或特殊種類記 NA)，其餘類別皆記為 0 或空白(系統會自動補0)。</li>
            <li><b>碎片3數量</b> - 類別為 3 時，才需記錄。其餘類別皆記為 0 或空白(系統會自動補0)。</li>
            <li><b>性別</b> - <span class='line'>種類為長葉木薑子，類別為 6 時，需記錄性別 F/M</span>。其餘類別或種類皆保留空白。</li>
            <li><b>鑑定者</b> - 可由選單選取亦可自行輸入。</li>
        </ol>
        <h6>特殊情況說明</h6>
        <ol>
            <li><span class='line'>栲屬</span>或<span class='line'>薹屬</span>的花，可不需分類至種。</li>
        </ol>
    </div>


</div>    

    

</div>
</div>
</div>
@endsection