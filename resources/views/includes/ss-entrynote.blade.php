                    <li><b>輸入資料後需按 <button class='datasavebutton' style='width: auto;'>儲存</button> ，才能確實將資料儲存。</b>請確實依照紙本資料輸入，以減少兩次輸入的不一致。</li>
                    <li>日期格式： YYYY-MM-DD。每筆資料皆需輸入日期，<b>日期為 0000-00-00 者視同未輸入</b>。</li>
                    <li>status 為 0(全株死亡),-1(全株失蹤),-2(全株 dbh < 1 cm),-3(枝幹死亡),-4(在樣區外之主幹)，dbh 需為0，且 code 不得有值。status 為空值，則 dbh 不得為 0。</li>
                    <li>dbh 必須<b>大於或等於</b>上次調查，或勾選縮水。</li>
                    <li>code：C(更改pom)，I(量測點表面不平)，P(枝幹倒伏)，R(無行拓殖分株)，F(榕屬氣生根)。code R/F 只能出現在分支。<b>code 代碼間可共存</b>，多碼時照字母排列，<b>中間不留空格</b>。</li>
                    <li>note： TAB=#。統一使用<b>「中文」標點符號</b>。<b>「半形」英文符號</b>。<b>「半形」阿拉伯數字</b>，數字後留一格空白。先確認原始 note，加「。」，再輸入本次note。不同類型 note 間用「。」分隔。</li>
                    <li>plot，5x，5y，tag，b，csp 等欄位需要修改時，請至「特殊修改<i class='fa-regular fa-note-sticky'></i>」填寫。<b>只需填寫需修改的部分。</b></li>
                    <li>若調查後的 dbh < 1 cm，請在表格內填寫 1，再至「特殊修改<i class='fa-regular fa-note-sticky'></i>」的 dbh(<1) 欄位填寫正確之調查資料。</li>
                    <li>新樹資料可以修改或刪除。</li>
                    {{-- <li>最後一個樣區輸入完成後，請按<button class='datasavebutton' style='width: auto;'>輸入完成</button>以做檢查。若有更新資料，則需重新按<button class='datasavebutton' style='width: auto;'>輸入完成</button>，以再次檢查。</li> --}}