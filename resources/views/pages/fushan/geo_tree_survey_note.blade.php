@extends('layouts.geo-tree-survey')

@section('pagejs')
    <script>
        $('.list4').addClass('now');
        $('.list4 hr').css('color', '#91A21C');
        $('.list41').addClass('now');
    </script>
@endsection

@section('rightbox')
    <div>
        <h2>GEO-TREES 資料輸入注意事項</h2>

        <div class="note">
            <ul>
                <li><b>請依照紙本紀錄逐筆輸入，第一次與第二次輸入應由不同人員獨立完成。</b></li>
                <li><b>輸入後必須按 <button class="datasavebutton" style="width:auto;">儲存</button>，資料才會寫入該次的輸入資料表。</b></li>
                <li>可使用 Tab、Enter 及方向鍵在 Handsontable 欄位間移動。</li>
                <li>GEO-TREES 不另設「輸入完成」檢查；一般儲存可分次進行，漏輸資料由二次比對檢查。</li>
            </ul>

            <div class="flex text_outbox" style="flex-direction:column;">
                <div class="text_box_note_out">
                    <div class="text_box text_box_note">
                        <h2>選擇樣方與小樣區</h2>
                        <hr>
                        <ol>
                            <li>先選擇 20×20 樣方的 qx、qy，再按「送出」。進入樣方後預設顯示小樣區 (1, 1)。</li>
                            <li>可使用右側快速方格或「上一個樣區／下一個樣區」切換 5×5 小樣區。</li>
                            <li>樣方選單只列出輸入資料表中存在且需要調查的樣方；不需輸出紀錄紙的樣方也不需輸入。</li>
                            <li>小樣區顯示「共有 0 筆資料」代表該小樣區沒有背景資料，可直接切換至下一區。</li>
                        </ol>
                    </div>

                    <div class="text_box text_box_note">
                        <h2>儲存方式</h2>
                        <hr>
                        <ol>
                            <li>Date 空白或為 0000-00-00 的資料視為尚未輸入，按儲存時會跳過，不驗證也不寫入。</li>
                            <li>Date 有值代表該筆已開始輸入，必須通過全部欄位檢查。</li>
                            <li>同一次送出的已輸入資料必須全部通過檢查才會一起儲存；任一筆有錯時皆不寫入。</li>
                            <li>檢查失敗時，尚未儲存的內容會保留在畫面，並標示錯誤欄位。</li>
                            <li>可以輸入部分資料後先行儲存，再繼續完成同一小樣區。</li>
                        </ol>
                    </div>
                </div>

                <div class="text_box_note_out">
                    <div class="text_box text_box_note">
                        <h2>主表欄位</h2>
                        <hr>
                        <ol>
                            <li><b>Date</b>：必填，格式為 YYYY-MM-DD，且必須是實際存在的日期。</li>
                            <li><b>20x、20y、5x、5y、tag、b、csp</b>：背景資料，主表不可直接修改；資料有誤時使用特殊修改。</li>
                            <li><b>status</b>：合法值為 0、-1、-2、-3，同一格只能有一個值。
                                <ul>
                                    <li><b>0</b>：全株死亡。</li>
                                    <li><b>-1</b>：全株失蹤。</li>
                                    <li><b>-2</b>：全株 DBH &lt; 1 cm，植株仍存活但已離開本調查取樣範圍。</li>
                                    <li><b>-3</b>：該枝幹死亡。</li>
                                    <li><b>空白</b>：枝幹存活，需輸入 DBH。</li>
                                </ul>
                                status 有值時 DBH 必須為 0，code 必須留白；status 空白時 DBH 不得為 0。
                            </li>
                            <li><b>code</b>：合法代碼為 C、I、P、R，儲存時統一轉為大寫。代碼可共存，但須依字母順序排列、中間不留空格，且不得重複。
                                <ul>
                                    <li><b>C</b>（Change）：本次改變 POM。</li>
                                    <li><b>I</b>（Irregular）：不正常 POM。</li>
                                    <li><b>P</b>（Prostrate）：枝幹倒伏。</li>
                                    <li><b>R</b>（Ramet）：無性拓殖分株，只能記錄於分支（b 不得為 0）。</li>
                                </ul>
                            </li>
                            <li><b>dbh</b>：必須是數值。存活枝幹的 DBH 必須 ≥ 1，且應輸入本次實際測量值。</li>
                            <li><b>POM</b>：DBH 測量高度，必須是數值。若本次改變 POM，code 必須包含 C，POM 必須與前次不同，並在 note 說明原因。</li>
                            <li><b>note</b>：可空白。新增內容接在需保留的原 note 後，以中文句號「。」分隔；中文使用全形標點，英文與阿拉伯數字使用半形。</li>
                            <li><b>縮水</b>：本次 DBH 小於前次調查時勾選。本次 DBH 未縮小時不可勾選；使用 C 表示 POM 改變時也不可同時勾選。</li>
                        </ol>
                    </div>
                </div>

                <div class="text_box_note_out">
                    <div class="text_box text_box_note">
                        <h2>M 與 -- 資料</h2>
                        <hr>
                        <ol>
                            <li><b>M</b>：該 stem 已列入死亡率調查，本次不在 GEO-TREES 主表輸入。</li>
                            <li><b>--</b>：該 stem 前次 DBH &lt; 9.5，本次不需輸入。</li>
                            <li>M 與 -- 的整列皆為灰色且不可輸入，特殊修改按鈕也會停用。</li>
                            <li>畫面標記不會寫入 DBH；死亡率調查完成後，再另行匯入死亡率調查資料。</li>
                        </ol>
                    </div>

                    <div class="text_box text_box_note">
                        <h2>特殊修改 <i class="fa-regular fa-note-sticky"></i></h2>
                        <hr>
                        <ol>
                            <li>用於修正背景資料的 20x、20y、5x、5y、tag、b、csp、原 POM，或填寫其他說明。</li>
                            <li>只需填寫需要修改的欄位，未修改的欄位保持空白。</li>
                            <li>點選特殊修改圖示時，系統會先儲存主表中已輸入的資料；主表檢查成功後才開啟特殊修改。</li>
                            <li>修改 tag 或 b 後若形成重複 stemid，特殊修改不會儲存。</li>
                            <li>csp 使用 <code>plant_catalog.site_species</code> 中 <code>site = fushan</code> 的名錄，可輸入文字搜尋並從選單選擇。</li>
                            <li>原始 POM 資料錯誤時，應在特殊修改的「原POM」填寫正確值；不要以主表的 code C 取代背景資料修正。</li>
                            <li>特殊修改通過檢查後，仍須按「儲存特殊修改」才會保存。</li>
                        </ol>
                    </div>
                </div>

                <div class="text_box_note_out">
                    <div class="text_box text_box_note">
                        <h2>第一次與第二次輸入</h2>
                        <hr>
                        <ol>
                            <li>第一次輸入寫入 record1，第二次輸入寫入 record2，兩次資料彼此獨立。</li>
                            <li>第二次輸入時請重新依紙本輸入，不應參考第一次輸入的內容。</li>
                            <li>完成兩次輸入後，後續使用「資料比對」檢查 record1 與 record2；資料比對功能尚待製作。</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
