# Handsontable 資料輸入介面規範

> 分類：資料管理系統／介面規範
> 適用範圍：新建的 Handsontable 資料輸入介面，以及日後進行重構的既有介面
本文件只規範 Handsontable 表格元件的技術實作，包括 instance、欄位設定、表格尺寸、draft、dirty、錯誤儲存格及 Livewire 生命週期。整個頁面的標題、卡片、note、工作流程、權限、儲存交易與完成調查，統一依 [資料輸入與修改介面規範](data-editing.md)。研究欄位、狀態意義與特殊驗證則記錄在各研究文件；共用表格不得寫入特定研究的 A／D／G、DBH、種子類別等規則。

既有「福山小苗」與「種子」輸入介面屬舊版架構，目前可繼續使用，不要求為了新增功能立即修改；其現況與後續調整原則列於本文末段。

## 一、版本與載入方式

- 專案內統一使用 Handsontable `12.3.3`。
- JavaScript 與 CSS 由 `public/js/handsontable/dist` 載入。
- 現有頁面使用 `non-commercial-and-evaluation` license key。
- 新功能不得另外載入其他 Handsontable 版本。
- 同一頁面不得重複載入 Handsontable 核心程式或樣式。

## 二、新介面的標準架構

新介面採下列分工：

```text
研究設定／Livewire Component
    欄位、選項、工作範圍、資料與導覽順序
                    ↓
共用 Handsontable JavaScript
    instance、分頁、draft、異動狀態、錯誤定位
                    ↓
Livewire 儲存方法
                    ↓
研究專屬 Save Service
    依流程規範處理正式驗證與資料寫入
```

### 2.1 研究層

研究專屬 Component、設定或 Service 負責：

- 定義欄位、標題、型別、選項與唯讀狀態。
- 查詢本次允許輸入的資料範圍。
- 提供 Plot／樣區或其他工作單位的導覽順序。
- 定義研究專屬的必填、數值範圍與欄位關聯。
- 判斷正式資料如何新增或更新。
- 記錄操作者、輸入時間及研究需要的批次資訊。

### 2.2 共用表格層

共用元件只處理所有研究一致的介面行為：

- 建立、取得及銷毀 Handsontable instance。
- 保存跨頁的完整 draft。
- 分頁與切換工作單位。
- 顯示異動、驗證及儲存狀態。
- 將後端錯誤定位到資料頁、資料列及儲存格。
- 儲存失敗時保留使用者輸入。
- 防止重複送出。

共用程式不得以研究名稱、`plotType`、`tableType` 或頁面全域 `cellfunction()` 判斷業務流程。

### 2.3 後端整合邊界

共用表格負責送出完整 draft 並接收結構化結果，不負責決定權限、transaction、完成條件或正式資料寫入方式。這些流程依 [資料輸入與修改介面規範](data-editing.md)及各研究文件實作。前端驗證只協助輸入，不能作為資料正確性的唯一依據。

## 三、共用 Blade 元件

共用資料輸入元件目前位於：

- `resources/views/components/data-entry/handsontable.blade.php`
- `public/js/data-entry-grid.js`

元件以現有 GEO-TREES 的 tree-entry Handsontable 為主要參考，至少提供：

- 表格上方與下方的操作回饋。
- 使用 `wire:ignore` 的 Handsontable container。
- 儲存按鈕及儲存中的停用狀態。
- 第一筆錯誤的提示與定位操作。
- 預設完整展開目前工作範圍的資料列。
- 只有研究資料量確實過大時，才選配資料總筆數、頁碼、每頁筆數及上下頁功能。

研究頁面可在共用元件外增加日期、調查批次、Plot 導覽或說明文字，但不可複製整份共用表格程式再個別修改。

### 3.1 表格顯示

輸入與修改頁面的標題、樣站導覽、note、內容卡片及按鈕格式統一依 [資料輸入與修改介面規範](data-editing.md)；本節只定義 Handsontable 本身的顯示方式。

Handsontable 顯示規格：

- 一般資料列高度為 35px，與既有福山小苗輸入表相同。
- Date 欄使用 YYYY-MM-DD，預設欄寬至少 145px，避免日期文字及下拉圖示互相擠壓。
- 每個樣站資料量可以完整展開時，表格高度使用「表頭高度＋資料列數 × 35px」計算。
- 完整展開時啟用 renderAllRows，並將 viewportRowRenderingOffset 設為該工作範圍的資料列數。
- 完整展開的表格不得出現 Handsontable 內部水平或垂直捲軸；頁面本身負責一般瀏覽捲動。
- 若單一工作範圍資料量過大，確實需要分頁或固定高度，必須在研究文件說明原因、每頁筆數與 draft 保存方式，不得任意限制表格高度。
- 唯讀欄沿用系統灰底、灰字及斜體樣式；可編輯欄維持白底。

## 四、JavaScript 行為規範

### 4.1 Instance 管理

- 每個 container id 同時間只允許一個 Handsontable instance。
- 重新載入或離開元件前必須 destroy 舊 instance。
- 不得把多個研究頁面的狀態放在未命名或可能互相覆蓋的全域變數。
- Livewire 重新 render 時不得清除尚未儲存的 draft。

### 4.2 Draft 與分頁

- 前端保存本次工作範圍的完整 draft，不只保存目前頁面。
- 換頁、切換 Plot 或重新排序前，先把目前表格內容同步回 draft。
- 切回原頁時，必須恢復尚未儲存的內容。
- 離開含未儲存異動的工作範圍前，必須先儲存或取得使用者確認。
- 儲存失敗不得重新向資料庫載入並覆蓋使用者輸入。

### 4.3 異動與儲存狀態

- 使用者修改過的欄位應有一致且不影響閱讀的標記。
- 儲存時停用儲存按鈕，避免重複送出。
- 儲存完成後明確顯示成功、無異動或失敗。
- 儲存成功才以後端回傳的正式資料重新建立 draft。

### 4.4 錯誤呈現

- 前端驗證可立即標示欄位，但不得移除不合法的原輸入值。
- 後端錯誤必須能對應至資料列與欄位。
- 收到錯誤後，自動切換到第一個錯誤所在的頁面及儲存格。
- 同時保留其他錯誤標記，讓使用者可以逐筆修正。
- 不只顯示「儲存失敗」；必須顯示可理解的錯誤原因。

## 五、欄位設定規範

每個研究項目必須以白名單明確列出前端欄位，不得把資料庫整列直接送入瀏覽器。欄位設定可包含：

- `data`、`header`、`width`。
- `readOnly`。
- `type`：text、numeric、date、dropdown、autocomplete、checkbox。
- `source`、`strict`、`allowInvalid`。
- `dateFormat: YYYY-MM-DD`、`correctFormat: true`。
- hidden 欄位或研究專屬 renderer。

其他規則：

- 內部主鍵如需送到前端，必須隱藏且唯讀。
- Plot、個體編號及其他識別欄即使在前端唯讀，後端仍須核對。
- 日期使用 date editor 與 `YYYY-MM-DD`，後端再驗證日期是否真實存在。
- dropdown 不接受選項外的值。
- autocomplete 是否採 strict，須由研究規則明訂。
- 可允許從 Excel 貼上，但貼上資料仍須通過所有前後端驗證。
- 相依欄位的清除或自動帶入可以在前端協助處理，後端仍須驗證最後組合。

## 六、後端回傳介面

權限、驗證、transaction、更新範圍與稽核規則不在本文重複定義，依 [資料輸入與修改介面規範](data-editing.md)處理。Handsontable 元件只要求後端回傳足以更新 draft、顯示結果及定位錯誤的結構。

完整回傳統一使用下列結構；舊介面可在前端 adapter 將既有欄位轉成此格式：

```json
{
  "ok": false,
  "status": "partial",
  "message": "部分儲存完成：本次新增或更新 3 筆，跳過 5 筆尚未輸入資料，錯誤 1 筆。錯誤資料已保留，請修正後再儲存。",
  "counts": {
    "saved": 3,
    "skipped": 5,
    "failed": 1
  },
  "errors": [
    {
      "row": 4,
      "recordKey": "stable-record-key",
      "field": "status",
      "code": "required",
      "message": "Status 不得空白。"
    }
  ]
}
```

`status` 只使用：

- `success`：所有已輸入資料皆儲存成功。
- `partial`：部分成功、部分錯誤。
- `error`：沒有任何資料成功，且存在錯誤。
- `noop`：沒有異動或沒有可儲存資料。

若錯誤資料跨越分頁，`row` 必須代表完整 draft 的資料列位置，而不是目前畫面的相對列號；若資料可能排序，應一併保留穩定的 record key 供前端核對。

結果至少提供狀態、訊息、成功／跳過／錯誤筆數，以及需要同步至 draft 的最新資料。`partial` 時只以後端正式資料更新成功列，錯誤列繼續使用原 draft；沒有異動時也回傳 `noop`，不以空回應表示成功。

## 七、表格內的分頁與資料範圍

- 工作單位與頁面導覽順序由後端傳入，Handsontable 不自行依顯示文字排序。
- 切換表格頁次或資料範圍前，先把目前內容同步至 draft。
- 分頁只切換顯示範圍，不得使未顯示列從送出資料中消失。
- 固定名單的 record key 必須穩定，重新排序後仍能對應原資料。
- 批次邊界與完成條件屬頁面流程及研究規則，不由 Handsontable 自行決定。

## 八、新介面實作檢查表

新增 Handsontable 介面時，至少確認：

- [ ] 使用專案既有 Handsontable 版本。
- [ ] 欄位採白名單設定。
- [ ] 識別欄唯讀，且後端重新核對。
- [ ] instance 重建前會 destroy。
- [ ] 分頁與切換工作單位不會遺失 draft。
- [ ] 未儲存異動有離開提示。
- [ ] 前端錯誤不會清除原輸入。
- [ ] 正確列可逐筆成功，錯誤列仍保留原 draft。
- [ ] 空白／未啟動列會跳過，不當成錯誤。
- [ ] 回傳包含 status、message、counts 與結構化 errors。
- [ ] 錯誤包含 row、field、code、message。
- [ ] 可定位第一個錯誤儲存格。
- [ ] 儲存期間防止重複送出。
- [ ] 成功、無異動與失敗都有清楚回饋。
- [ ] 一般列高為 35px，Date 欄寬至少 145px。
- [ ] 可完整展開時沒有 Handsontable 內部水平或垂直捲軸。
- [ ] 輸入或修改頁面已符合資料輸入與修改介面規範。
- [ ] 研究專屬規則已寫入該研究文件。

## 九、既有介面現況與後續調整

### 9.1 福山小苗（舊版）

主要位置：

- `public/js/seedling.js`
- `public/js/create-handsontable.js`
- `app/Http/Livewire/Fushan/SeedlingShowentry.php`
- `app/Http/Controllers/Fushan/SeedlingSaveController.php`

現況：

- 使用舊版共用 `createHandsontable()`、jQuery plugin 及多個頁面全域 callback／變數。
- 依 Trap 載入既有苗、環境、新苗、撿到環及特殊修改等不同表格。
- 具備日期、狀態選單、唯讀欄、異動標示、樣站導覽及雙輸入工作資料流程。
- 既有操作流程目前可繼續使用，不需為小苗實驗新功能同步改寫。

待日後重構時：

- 改採本文的 instance、draft 與結構化錯誤規範；transaction 依資料輸入與修改介面規範處理。
- 移除共用函式內依 `plotType`、`tableType` 及全域 `cellfunction()` 的分支。
- 先驗證完整儲存範圍，避免前段資料已寫入後才遇到後列錯誤。
- 重構必須另行測試既有雙輸入及特殊修改流程，不可直接以新小苗實驗流程取代。

### 9.2 種子（舊版）

主要位置：

- `public/js/seeds.js`
- `public/js/create-handsontable.js`
- `app/Http/Livewire/Fushan/SeedsShowentry.php`
- `app/Http/Controllers/Fushan/SeedsSaveController.php`

現況：

- 先建立調查日期及 `record1` 工作資料，再分別處理工作資料與正式資料。
- 已有資料與新增資料使用不同表格。
- 支援新增列、物種 autocomplete 及研究專屬 validator。
- 同樣依賴舊版 `createHandsontable()` 與頁面全域狀態。
- 既有操作流程目前可繼續使用，不需為新介面同步修改。

待日後重構時：

- 保留先建立調查批次、阻擋未完成批次及工作資料分層的業務需求。
- 將表格 instance、draft、分頁、錯誤定位與儲存狀態改用本文共用規範。
- 清楚區分「工作資料已儲存」與「正式資料已建立」，避免只靠不同按鈕讓使用者判斷階段。
- 正式改造前須盤點新增資料列與物種 autocomplete 的既有使用方式。

### 9.3 GEO-TREES（新規範的主要技術基礎）

GEO-TREES 已具備欄位白名單、instance destroy、跨頁 draft、scope 與唯讀欄核對、逐筆 transaction、結構化錯誤及儲存格定位，是新共用元件的主要參考。主要位置：

- `public/js/tree-entry-grid.js`
- `resources/views/components/tree-entry/handsontable.blade.php`
- `app/Http/Livewire/Fushan/GeoTreeSurveyShowentry.php`
- `app/Services/Fushan/GeoTreeEntrySaveService.php`
- `config/tree-entry.php`

抽取共用元件時不得直接加入小苗實驗專屬欄位或狀態規則。

### 9.4 死亡率調查（互動方式參考）

死亡率調查的逐筆成功、完整前端 draft、相依欄位處理、客製 renderer、備註編輯器及錯誤列標示可供參考；但其 JavaScript 位於大型 Blade，回傳欄位仍需透過 adapter 對齊本文件格式，因此不得直接作為新介面的共用實作。主要位置：

- `resources/views/livewire/fushan/mortality-showentry.blade.php`
- `app/Http/Livewire/Fushan/MortalityShowentry.php`
