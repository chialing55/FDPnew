# 公版每木輸入介面

## 目標

公版提供森林樣區每木雙次輸入的共同介面與操作流程，但不綁定特定資料庫、Eloquent Model、樣區編碼或額外調查項目。

第一個 consumer 為 ForestGEO／GEO-TREES。既有福山每木及壽山每木在各自重整前維持現況，之後再逐步套用公版。

## 共用範圍

- 第一次、第二次資料輸入。
- 輸入標題、輸入者、輸入日期及注意事項連結。
- 樣區選擇及上一頁、下一頁。
- 小樣區選擇。
- 每木 Handsontable。
- 新增樹與漏資料樹。
- 特殊修改。
- 儲存結果及輸入完成狀態。

## 三層結構

```text
基礎公版
└── 福山系每木公版
    ├── 福山每木
    └── GEO-TREES
```

### 基礎公版

負責 Handsontable、分頁、異動標示、批次驗證流程、transaction、錯誤呈現及輸入失敗時保留使用者尚未儲存的資料。正式欄位與驗證規則統一記錄於[每木資料輸入與驗證規格](validation-profiles.md)。

### 福山系每木公版

福山每木與 GEO-TREES 共用的 profile。這一層不包含福山每木的樹蕨轉換，也不包含 GEO-TREES 的鎖定列。

### 各調查版本

- 福山每木、GEO-TREES 與壽山分別提供自己的欄位、資料來源與例外設定。
- 各調查的正式差異以[每木資料輸入與驗證規格](validation-profiles.md)為準。

## 儲存失敗時保留輸入

儲存採「整批驗證、全部通過後才寫入」：

1. 前端送出目前頁面的資料，但不先清除 Handsontable。
2. 後端完成所有列的正規化及驗證，不在驗證途中更新資料庫。
3. 任一列失敗時回傳列索引、欄位及訊息；前端標示錯誤格並保留整頁輸入。
4. 全部通過後才在 transaction 內一次更新。
5. 寫入成功後才由後端資料重新整理表格。

## 模組自行提供

- `record1`、`record2` Model。
- 背景資料 Model。
- 樣區選擇模式及可選範圍。
- 小樣區的行列數與順序。
- Handsontable 欄位設定。
- 物種名錄來源。
- 輸入檢查規則及例外條件。
- 是否顯示環境、地被、掃描檔或進度等額外區塊。
- 儲存與刪除路由。

## 樣區選擇模式

### 座標樣區

適用於福山每木、GEO-TREES 及壽山 1.05 ha。模組提供 `qxOptions`、`qyOptions`、目前選取值及 Livewire action。

### 命名樣區

適用於壽山 10m 森林觀測樣區。模組提供樣區代碼清單、目前選取值及 Livewire action。

## Blade 元件

```text
resources/views/components/tree-entry/
├── shell.blade.php
├── coordinate-selector.blade.php
├── named-plot-selector.blade.php
├── subquadrat-selector.blade.php
└── panel.blade.php
```

- `shell`：輸入頁共同外框與 metadata。
- `coordinate-selector`：`qx-qy` 樣區選擇。
- `named-plot-selector`：A1、B-F-01 等命名樣區選擇。
- `subquadrat-selector`：可配置行列數的小樣區格。
- `panel`：每木、環境、地被及新增資料等可插拔區塊。

## 設計原則

- Blade 不直接查詢資料庫。
- 元件不寫死網站路徑，網址由命名路由產生後傳入。
- 元件不判斷調查規則，驗證由共用 service 與模組設定處理。
- 額外調查項目使用 slot 或獨立 panel 擴充，不在核心元件加入站點判斷。
- 不以 `site === ...` 或 `plotType === ...` 在公版內堆疊分支。

各層驗證規則與調查例外詳見[每木輸入驗證規格](validation-profiles.md)。
