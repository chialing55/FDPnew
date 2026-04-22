{{-- 資料輸入主要的表格部分 /每木、小苗--}}
<div class='seedling-data-table-block' style='display: inline-flex; flex-direction: column; margin-top: 20px;'>
    <div class='pages' data-seedling-pages style='margin-bottom: 14px; display: flex; align-items: center; gap: 12px; flex-wrap: wrap;'>
        <div class='totalnum' data-seedling-totalnum></div>
        <div class='pagenote' data-seedling-pagenote></div>
        <div class='page-size' data-seedling-page-size style='display: inline-flex; align-items: center; gap: 6px;'>
            <span>每頁</span>
            <select data-seedling-page-size-select>
                <option value="20">20</option>
                <option value="40">40</option>
            </select>
            <span>筆</span>
        </div>
        <div class='prev' data-seedling-prev style='display: inline-block; min-width: 48px;'>上一頁</div>
        <div class='next' data-seedling-next style='display: inline-block; min-width: 48px;'>下一頁</div>
    </div>

    <span class='datasavenote savenote app-feedback-note'></span>
    <div id='datatable{{$tableVar}}' style='' class='fs100' ></div>
    <span class='datasavenote savenote app-feedback-note'></span>
    <p style='margin-top:5px; text-align: center;'><button name='datasave{{$tableVar}}' class='datasavebutton'>儲存</button></p>
</div>
<div class='alternotetalbeouter seedling-alternote-panel' data-seedling-alternote-site='{{$tableVar}}'>
    <div style='display:flex; align-items:center; gap:14px; padding-right:52px;'>
        <h6 class='alterh6' style='margin:0;'>特殊修改</h6>
        <span style='font-size: 80%; font-weight: 500;'>*只需填寫需修改的資料  {{$alterOtherNote}}</span>
    </div>

    <p >
        <span class='alterstemid'></span>
        <span class='altersavenote savenote app-feedback-note'></span>
    </p>
    <div class='seedling-alternote-form' style='display:none; margin-top: 10px;'>
        <div class='seedling-alternote-rows' style='display:flex; flex-direction:column; gap:10px;'></div>
        <button type='button' class='seedling-alternote-add datasavebutton' style='width:auto; margin-top:12px;'>增加一項</button>
    </div>
    <div id='alternotetable' style='margin-top: 5px;' class='fs100' ></div>

    <p style='margin-top:10px; text-align: right;'>
        <button type='button' name='alternotesave' class='datasavebutton' style='width: auto;' >儲存特殊修改</button>

        <button type='button' name='deletealternote' class='deletealternotebutton' onclick="deletealternoteButtonClick(this)">刪除此資料</button>
        <button type='button' class='close' onclick="closeSeedlingAlternoteModal(this)">關閉視窗</button>
    </p>
</div>
