# 每木輸入介面重整待辦

> 狀態：待處理。此文件只記錄後續重整範圍，目前不要移除舊表或改變既有每木輸入流程。

## 目標

每木輸入介面重整時，移除對 `fs_base.login`、`fs_base.tree_splist` 與 `fs_base.spinfo` 的依賴。帳號統一使用 Laravel 使用者架構，植物名錄統一改用 `plant_catalog`。

## 人員資料

目前每木部分功能仍讀取 `fs_base.login`：

- `app/Http/Controllers/LoginController.php`
- `app/Http/Livewire/Fushan/TreeShowentryprogress.php`
- `app/Models/FsBaseLogin.php`

重整方向：

- 登入與操作者識別統一使用 Laravel `users`。
- 寫入者統一使用 `ResolvesActorAccount` 所提供的帳號。
- 輸入進度的人員名稱改由 `users` 或日後定義的調查人員資料表取得。
- 確認沒有其他程式使用後，才評估停用 `fs_base.login`、`login2`。

## 物種名錄與驗證

目前 `TreeController` 仍以 `FsBaseSpinfo` 及舊欄位 `spinfo.tree = 1` 產生物種選單；每木輸入、驗證、地圖、資料修改及 PDF 另有多處讀取 `fs_base.tree_splist`：

- `app/Http/Controllers/Fushan/TreeController.php`
- `app/Jobs/FsTreeDataCheck.php`
- `app/Jobs/FsTreeRecruitCheck.php`
- `app/Http/Controllers/Fushan/TreeSaveController.php`
- `app/Http/Controllers/Fushan/TreePDFController.php`
- `app/Http/Livewire/Fushan/TreeAdddata.php`
- `app/Http/Livewire/Fushan/TreeDataviewer.php`
- `app/Http/Livewire/Fushan/TreeMap.php`
- `app/Http/Livewire/Fushan/TreeShowentry.php`
- `app/Http/Livewire/Fushan/TreeUpdatetable.php`
- `app/Http/Livewire/Fushan/TreeUpdatebackdata.php`
- `app/Models/FsBaseTreeSplist.php`

重整方向：

- 每木不得再直接查詢 `fs_base.spinfo`、使用 `FsBaseSpinfo`，或依賴舊欄位 `spinfo.tree`。
- 福山樣區物種與中文名使用 `plant_catalog.site_species`，查詢時固定加入 `site = fushan`。
- 福山內部物種鍵使用 `plant_catalog.site_species.spcode`；`csp` 只作中文名顯示及舊資料輸入相容用途。
- 學名、完整學名、屬、科、中文科名及生長型一律透過 `site_species.code → taiwan_checklist.spcode`，由 `plant_catalog.taiwan_checklist` 取得，不得從舊表複製或回退讀取。
- 是否屬於每木調查使用 `plant_catalog.species_research_links`，條件必須同時包含 `site = fushan` 與 `research_code = tree`。
- 程式應優先使用 `App\Models\PlantCatalog\SiteSpecies` 的 `fushan()`、`withChecklistTaxonomy()` 與 `checklist()`，避免各功能自行重寫名錄連接規則。
- 每木新增與修改驗證不得再以 `tree_splist` 作為唯一合法名錄。
- 每木合法物種選單及 `spcode/csp` 驗證，應由上述 `site_species + species_research_links` 組合產生。
- `tree_splist` 若仍有每木專用的非分類設定，須先盤點並移往每木所屬資料庫；不可把這些設定塞入 `site_species` 或 `taiwan_checklist`。
- 未知物種統一使用既定未知代碼規則；`UNKUNK` 與其他 `UNK*` 不得寫入 `species_research_links` 的正式研究物種連結。

目標查詢關係：

```text
plant_catalog.site_species
  site = fushan
  ├─ code   → plant_catalog.taiwan_checklist.spcode（學名與分類資料）
  └─ site + spcode → plant_catalog.species_research_links
                     research_code = tree（每木調查物種範圍）
```

## code 排序與重複驗證

福山既有後端會檢查 code 的允許字元，但未完整檢查字母排序及重複代碼，目前仍依賴人工遵守輸入規範。

- [ ] 在既有樹與新增樹的後端驗證補齊 code 字母排序及禁止重複的檢查，涵蓋第一次與第二次輸入及相關修改流程。
- [ ] 優先沿用公版 `TreeEntryValidator` 的 `sortMultipleCodes`、`disallowDuplicateCodes` 規則，避免另寫一套。
- [ ] 驗證失敗時提示具體原因，讓輸入人員修正；不可只依賴前端或人工檢查。
- [ ] 驗收案例：`IP`、`IPR` 通過；`PI`（順序錯誤）、`II`（重複代碼）不通過；並確認空白 code 及既有合法代碼規則不受影響，新增樹仍不得使用 C。
- [ ] 完成後更新[管理員閱讀版指南](../tree-entry/administrator-guide.md)中仍須人工檢查的說明。

## 無法測量的特殊值 999

福山輸入注意事項使用 `999` 表示無法測量或因特殊因素未測量，不是實測胸徑。重整時需明確區分特殊值與一般測量值，避免被當成正常數值驗證或分析。

- [ ] 確認 `999` 的適用欄位與情境（DBH、樹蕨 h高是否皆適用），以及是否必須在 note 說明未測量原因。
- [ ] 明訂本次或前次為 `999` 時的驗證方式，避免直接以數值比較判定縮水或異常成長，並確認與 status、POM、C 的交互規則。
- [ ] 盤點資料比對、匯出及分析如何辨識 `999`；匯出須能保留未測量意義，胸徑統計與生長量計算應排除特殊值，不得視為實測值或改成 0。
- [ ] 驗收本次為 `999`、前次為 `999`、前後次皆為 `999` 及一般實測值等情境。
- [ ] 確認規則並完成實作後，更新[管理員閱讀版指南](../tree-entry/administrator-guide.md)的無法測量說明。

## 同株關係、日期與輸入格式

福山既有完成檢查的同株關係檢查主要針對同株多筆資料，不能據此認定所有孤立分支都已被找出。系統檢查也不能完全取代人工複核；日期應使用真實的 `YYYY-MM-DD` 日期，小數位與 note 格式應統一。

- [ ] 補齊孤立分支檢查：即使同株僅有一筆分支資料，也須確認對應主幹是否存在及是否列入本次調查；區分缺少主幹與主幹需補回的情境，提示處理方式。
- [ ] 驗收同株僅一筆非 0 分支、主幹存在但未列入本次調查、正常主分支及 R 分株例外，避免只在同株有多筆資料時執行關係檢查。
- [ ] 補齊後端日期格式與實際日期驗證，涵蓋既有樹、新增樹及相關修改流程；明確區分一般儲存的未填日期列與「輸入完成」時的漏輸檢查。
- [ ] 日期驗收包含合法日期、閏年日期、不存在日期（如 `2026-02-30`）、非 `YYYY-MM-DD` 格式、空白及 `0000-00-00`。
- [ ] 明訂各測量欄位的小數位規範及超出位數的處理方式，確認福山 dbh／h高小數點後一位的要求如何落實於輸入、顯示與匯出；特殊值 `999` 另依其規則處理。
- [ ] 統一 note 的標點、新舊註記分隔、分支列舉、數字與單位間距、座標及 `TAB=#` 寫法；明訂哪些提供提示、哪些可安全正規化，避免自動改寫調查意義。
- [ ] 保留人工回查紙本、異常值與 note 內容的複核流程，並於完成後更新[管理員閱讀版指南](../tree-entry/administrator-guide.md)。

## 完成條件

- 每木第一次輸入、第二次輸入、新增樹、修改、比對、地圖及 PDF 均正常。
- 每木操作者與進度頁不再查詢 `fs_base.login`。
- 每木物種選單及驗證不再查詢 `fs_base.tree_splist`。
- 每木所有畫面、驗證、輸出與修改流程不再查詢 `fs_base.spinfo` 或 `fs_base.species_research_links`。
- 每木需要的學名與分類欄位均可追溯至 `plant_catalog.taiwan_checklist`。
- 全專案搜尋確認沒有執行路徑使用 `FsBaseLogin`、`FsBaseTreeSplist` 或 `FsBaseSpinfo`。
- 完成資料比對與備份後，才另行決定是否刪除舊資料表。
