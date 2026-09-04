# 每木資料輸入與驗證規格

## 1. 文件定位

本文件是公版每木資料輸入、驗證與儲存的正式規格。欄位規則只在此處定義；介面文件及各調查 note 僅引用本文件，不另行複製相同規則。

規格分為四層：

```text
所有每木基礎公版
├── 福山系公版
│   ├── 福山每木
│   └── GEO-TREES
└── 壽山系公版
    ├── 壽山 1 ha
    └── 壽山 10 m
```

所有調查必須套用基礎公版。下層 profile 只能增加欄位、合法值、資料來源或明確例外，不得複製基礎規則另行實作。

## 2. 適用資料

公版處理三種輸入：

- 主表：既有 stem 的本次調查資料。
- 特殊修改：修正位置、編號、物種或前次 POM 等基本資料。
- 新增樹／漏資料：新增本次發現的 stem，或補回工作表未列出的既有 stem。

本文件中的「前次資料」由調查 profile 指定。主表不得相信瀏覽器傳入的前次值，驗證時必須由後端重新查詢。

## 3. 主表欄位規則

### 3.1 date

- 必填。
- 格式為 `YYYY-MM-DD`，且必須是實際存在的日期。
- 空字串、`null` 與 `0000-00-00` 均視為未輸入。
- 每筆資料各自保存調查日期，不以頁面日期代替。

### 3.2 樣方與小樣區

欄位依調查類型使用 `qx、qy、sqx、sqy` 或命名樣區 `plot、sqx、sqy`。

- 主表中全部為唯讀欄位。
- 儲存時必須與資料庫中的 stem 位置及目前開啟的輸入範圍一致。
- `sqx`、`sqy` 的合法範圍由調查 profile 定義。
- 選單只顯示該次輸入資料表實際存在的樣方，數字型代碼以數值排序。

### 3.3 tag

- 主表唯讀。
- 與 branch 組成 stemid；實際格式由調查 profile 定義。
- 儲存時以資料庫 stemid 查找原資料，不以使用者可修改欄位重新指定更新目標。

### 3.4 branch／b

- 主表欄位名稱顯示為 `b`，資料鍵使用 `branch`。
- 主表唯讀。
- `0` 代表主幹；非 `0` 代表分支。
- code `R` 只能用於非 `0` 的分支。

### 3.5 csp

- 主表唯讀。
- 顯示名稱由各調查指定的植物名錄提供。
- 物種修正必須使用特殊修改，不能由主表直接覆寫。

### 3.6 status

- 合法值由調查 profile 提供，空白代表仍需量測的活株。
- status 有值時，DBH 必須為 `0`。
- status 空白時，DBH 不得為 `0`。
- status 有值時 code 必須為空白；若調查有明確例外，例外代碼必須列在該調查 profile。
- status 與 DBH、code 的檢查以正規化後的值執行。

### 3.7 code

- 可空白。
- 儲存前統一轉成大寫。
- 可同時輸入多個代碼，但必須依字母順序排列。
- 代碼之間不得包含空格、逗號或其他分隔符號。
- 同一代碼不得重複。
- 合法代碼集合由調查 profile 定義。
- `R` 只能出現在分支。
- `C` 代表本次更改 POM；相關條件統一依 3.9 節處理。

### 3.8 dbh 或 dbh/h高

- 一般樹木欄名為 `dbh`；福山每木因樹蕨需求顯示為 `dbh/h高`。
- 必須是數值，不接受文字、無限值或非數值內容。
- 需要量測的普通樹木必填且數值不得小於 `1`；status 所造成的 `0` 值要求統一依 3.6 節處理。
- 必須與 profile 指定的前次 DBH 比較；所有適用 stem 都要比較，不限主幹。
- 本次值小於前次值時，必須勾選縮水或使用 code `C`。
- 本次值大於或等於前次值時，不得勾選縮水。
- 使用 `C` 說明改變 POM 時，不得再勾選縮水。
- 鎖定列及樹蕨的比較方式依各自 profile 處理。

### 3.9 pom

- 必須是數值。
- 一般資料若 POM 與前次不同，code 必須包含 `C`。
- code 包含 `C` 時，POM 必須與前次不同。
- code 包含 `C` 時 note 必填，內容需說明改變測量點的原因或位置。
- 使用 `C` 時不得同時勾選縮水。
- 新增樹不得使用 `C`。
- 前次資料本身錯誤而需要修正時，應使用特殊修改，不得以主表的 `C` 取代資料更正。

### 3.10 note

- 可空白。
- 其他 code 是否要求說明，可由調查 profile 增加規則。
- note 的標點與格式屬輸入規範，不應改變其他欄位的驗證結果。

### 3.11 confirm／縮水

- 使用 checkbox；勾選值為 `1`，未勾選為空字串。
- 是否允許勾選統一依 3.8 的 DBH 比較及 3.9 的 C／POM 規則判斷。

### 3.12 特殊修改按鈕

- 只用於開啟該 stem 的特殊修改表格，不是資料欄位。
- 按鈕本身及畫面產生的 HTML、renderer、metadata 不得寫入資料庫。
- 調查 profile 標示為鎖定的資料列，特殊修改按鈕也必須停用。

## 4. 基礎跨欄位規則

後端必須以同一筆正規化資料依序檢查：

1. 日期及 DBH 是否完整。
2. status、DBH 與 code 是否一致。
3. DBH 是否符合最小值。
4. code 格式及合法代碼集合。
5. R 與 branch 是否一致。
6. C、POM 與 note 是否一致。
7. DBH、前次 DBH、C 與縮水 checkbox 是否一致。

一筆資料可能同時有多個錯誤。後端應回傳所有可確定的欄位錯誤，不應只回傳第一個錯誤後停止整頁驗證。

## 5. 福山系公版

福山每木與 GEO-TREES 在基礎公版上增加：

- 主表合法 code 為 `C、I、P、R`。
- status 合法值為 `0、-1、-2、-3`。
- status 有值時沒有 code 例外。
- 樣方使用 `qx、qy、sqx、sqy`。
- stemid 基本格式為 `tag.branch`。

樹蕨及 GEO-TREES 鎖定列不屬於此層，分別由各調查 profile 定義。

## 6. 福山每木專屬規則

- 主表欄名使用 `dbh/h高`。
- `G` 開頭 tag 為樹蕨，該欄輸入值代表樹高。
- 樹蕨儲存時將輸入值寫入 `h2`，並將 `dbh` 寫為 `0`。
- 樹蕨以前次 `h2` 作為縮小比較基準，不套用普通樹木 DBH 最小值。
- 非樹蕨套用基礎公版的普通 DBH 規則。
- 前次資料表由該次調查設定；既有第五次輸入以 `census4` 為基準。

## 7. GEO-TREES 專屬規則

- 主表欄名使用 `dbh`，不使用樹蕨或 `h2` 轉換。
- 前次 DBH 與 POM 來自 `fs_geo_tree_survey.census5_part`。
- `fs_mortality.tree_individuals.is_active = 1` 的 stem 顯示 `M`。
- 非死亡率 stem 且前次 DBH 小於 `9.5` 時顯示 `--`。
- 同時符合兩種條件時優先顯示 `M`。
- `M` 與 `--` 只是畫面標記，不是 DBH 值。
- `M` 與 `--` 整列唯讀，特殊修改按鈕亦停用。
- 主表儲存時完全忽略鎖定列，不寫入空值、`M`、`--` 或畫面鎖定 metadata。
- 死亡率調查完成後另行匯入結果，不由 GEO-TREES 主表輸入。
- GEO-TREES 不另設「輸入完成」狀態；record1／record2 任一方缺少日期，由二次比對列為差異。

## 8. 壽山系公版

壽山 1 ha 與 10 m 套用完整基礎公版，並增加：

- 主表另有 `ill` 與 `leave`。
- `ill` 必須是 0–5 的數值。
- `leave` 必須是 0–100 的數值。
- 合法 code 為 `C、I、P、R、F`。
- `F` 只適用於雀榕或榕樹的氣生根，且只能用於分支。
- status 有值時原則上 code 為空白；壽山現有需求允許 `F` 例外。
- status 合法值包含 `-4`。
- 榕屬氣生根分支可因 `F` 而與主幹位於不同小樣區。
- status 有值時 ill 與 leave 的確切歸零條件，需在壽山改版前確認後寫入 profile。

壽山 1 ha 使用 `qx、qy、sqx、sqy` 及 `tag.branch`。壽山 10 m 使用命名 `plot`，stemid 格式為 `plot-tag.branch`，新增 tag 補成三碼。兩者各自指定前次資料來源。

既有程式的差異與待修正問題另見[壽山每木輸入驗證現況](shoushan-validation.md)。

## 9. 特殊修改規則

- 特殊修改使用獨立 Handsontable，欄位由調查 profile 定義。
- GEO-TREES 的 csp 自動完成與後端驗證使用 `plant_catalog.site_species`，並固定限制 `site = fushan`。
- 只保存不是 `null` 且不是空字串的欄位；數值 `0` 必須保留。
- 隱藏 stemid 只用於辨識目標，不得保存進 alternote JSON。
- 修改 tag 或 branch 後若形成重號，相關修改不得保存。
- 位置、編號、物種與 POM 須通過各欄位的型態及範圍檢查。
- GEO-TREES 的 `M`、`--` 列不可開啟或保存特殊修改。
- 全部驗證通過後，才在 transaction 中將特殊修改保存為 JSON。

## 10. 新增樹與漏資料規則

- 日期空白的預備列不處理，並保留在畫面。
- 有日期的列必須完成所有必填欄位與型態驗證。
- 檢查 stemid 是否重號。
- 新增樹不得使用 code `C`。
- 分支必須有主幹；跨小樣區例外由調查 profile 定義。
- 勾選漏資料時必須能找到既有 stem，且不可改變受保護的基本位置及物種欄位。
- 新增成功後才清空該列；失敗列及其他尚未保存列均保留。

## 11. 驗證與儲存流程

1. 前端保留 Handsontable 目前資料並送出目前小樣區的全部內容。
2. 後端確認 entry、stemid、樣方範圍與可寫入欄位。
3. 後端重新取得前次資料及鎖定狀態。
4. 一般儲存將日期空白或 `0000-00-00` 的列視為尚未輸入，跳過驗證及寫入；日期有值的列則執行全部欄位驗證。
5. 後端正規化並驗證所有已開始輸入的列。
6. 驗證失敗時不寫入資料庫，回傳列索引、欄位、錯誤代碼及訊息。
7. 前端保留所有輸入並標示錯誤格。
8. 全部通過後，以登入帳號作為操作者，在 transaction 中一次寫入。
9. transaction 成功後才重新載入資料；失敗則維持原表格內容。
10. 點特殊修改時先執行同一個一般儲存流程；成功才開啟特殊修改，失敗則留在主表修正。
11. 「輸入完成」屬於另一個嚴格檢查流程，日期空白列必須視為未完成；尚未提供此功能的調查不顯示該按鈕。

## 12. 設定與實作位置

- Profile 設定：`config/tree-entry.php`
- Profile 合併：`app/Support/TreeEntry/TreeEntryProfileResolver.php`
- 公版 validator：`app/Support/TreeEntry/TreeEntryValidator.php`
- 驗證結果：`app/Support/TreeEntry/TreeEntryValidationResult.php`
- GEO-TREES 主表儲存：`app/Services/Fushan/GeoTreeEntrySaveService.php`
- GEO-TREES 特殊修改儲存：`app/Services/Fushan/GeoTreeSpecialModificationService.php`
- 公版 validator 測試：`tests/Unit/TreeEntryValidatorTest.php`
- 公版介面：`resources/views/components/tree-entry/`
- 公版 Handsontable：`public/js/tree-entry-grid.js`
- 公版介面架構：[公版每木輸入介面](shared-interface.md)
- 壽山舊程式盤點：[壽山每木輸入驗證現況](shoushan-validation.md)

調查規則異動時，應先修改本文件與 profile，再修改 validator 及測試。
