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

## 完成條件

- 每木第一次輸入、第二次輸入、新增樹、修改、比對、地圖及 PDF 均正常。
- 每木操作者與進度頁不再查詢 `fs_base.login`。
- 每木物種選單及驗證不再查詢 `fs_base.tree_splist`。
- 每木所有畫面、驗證、輸出與修改流程不再查詢 `fs_base.spinfo` 或 `fs_base.species_research_links`。
- 每木需要的學名與分類欄位均可追溯至 `plant_catalog.taiwan_checklist`。
- 全專案搜尋確認沒有執行路徑使用 `FsBaseLogin`、`FsBaseTreeSplist` 或 `FsBaseSpinfo`。
- 完成資料比對與備份後，才另行決定是否刪除舊資料表。
