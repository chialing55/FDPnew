# 壽山每木輸入驗證現況

本文件記錄既有壽山 1 ha 與 10 m 每木輸入程式的驗證行為，供日後改用公版儲存流程時參考。福山系公版以福山每木規則為準；壽山規則應以獨立 profile 加入，不應直接改變福山或 GEO-TREES。

## 目前程式位置

- 主表後端檢查：`app/Jobs/SsPlotDataCheck.php`
- 新增樹後端檢查：`app/Jobs/SsPlotRecruitCheck.php`
- 儲存控制器：`app/Http/Controllers/Shoushan/PlotSaveController.php`
- 1 ha 前端欄位：`public/js/ss1ha.js`
- 10 m 前端欄位：`public/js/ss10m.js`
- 共用 Handsontable 儲存：`public/js/create-handsontable.js`

## 應改用所有每木基礎公版的規則

- 日期與 DBH 必填。
- status 有值時 DBH 必須為 0；status 空白時 DBH 不得為 0。
- status 有值時 code 原則上不得有值；既有壽山程式允許 code 包含 F 時通過。
- code 儲存前轉成大寫。
- 多個 code 可共存，但必須依字母順序排列、中間不留空格且不得重複。
- DBH 小於前次資料時，需勾選縮水或使用 C；沒有縮小時不應勾選縮水。
- DBH 最小值為 1。這兩項 DBH 規則屬於所有每木調查的基礎公版規則。
- R 只能出現在分支。
- C 用於 POM 改變，且需在 note 說明。
- 新增資料檢查重號、漏資料、分支是否有主幹，以及分支與主幹的位置關係。

上述日期／DBH、status／DBH、code 大寫、縮水、R、C／POM、新增資料不得使用 C、DBH 最小值及前次 DBH 比較，應直接使用基礎公版，不在壽山 validator 另寫一份。

## 壽山專屬規則

- code 允許 `CIPRF`；新增資料允許 `IPRF`。
- F 僅能用於雀榕或榕樹的氣生根，且只能出現在分支。
- status／code 的壽山 profile 需將 `F` 設為公版規則的例外。
- 主表另有 `ill` 與 `leave` 欄位；前端分別限制為 0–5 與 0–100。
- status 有值時，既有程式會檢查分支的 ill／leave；此處原條件使用兩欄同時不為 0 才失敗，重整時需確認是否應改成任一欄不為 0 即失敗。
- status 可包含 `-4`。
- 舊程式的 DBH 縮水只比較 `branch = 0`，這是不正確的既有行為；改用公版時所有適用 stem 都必須比較前次 DBH。
- 1 ha 使用 `qx-qy`；10 m 使用命名 `plot`。
- 10 m stemid 格式為 `plot-tag.branch`，tag 輸入會補成三碼。
- 榕屬分支可因 F 氣生根而位於不同小樣區。

## 特殊修改差異

- 1 ha 使用 `qx、qy、sqx、sqy、tag、b、csp、dbh、原POM、other`。
- 10 m 使用 `plot、sqx、sqy、tag、b、csp、dbh、原POM、other`。
- 現有 `array_filter($data)` 會把數值 0 一併移除；重整時應只移除 `null` 與空字串。

## 重整時應修正的流程問題

- 現在逐筆驗證後立即更新，後段資料失敗時可能形成部分儲存。應先驗證全部資料，再用 transaction 寫入。
- 現在部分 action 信任前端傳入的 `user`。新版必須由登入狀態取得操作者。
- Handsontable 的型態檢查僅作輸入提示，後端仍須完整驗證。
- 查詢原資料後多處直接使用陣列索引 0；應先處理找不到 stemid 的情況。
- C 的 POM 比較目前以固定 1.3 為基準，與福山讀取前次 POM 的方式不同；套用公版前需確認壽山的正確規則。
- 舊程式主要只排除 DBH 為 0，沒有完整執行 DBH 最小值 1；改用公版時必須套用共同的最小值規則。
- code 舊程式只逐字確認允許字元，未檢查重複代碼與字母順序；改用公版時直接套用共同格式規則。
