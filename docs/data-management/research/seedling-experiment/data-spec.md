# 小苗實驗資料說明

> 分類：資料管理系統／研究工作項目／小苗實驗

> 日常輸入流程見[小苗實驗資料輸入](data-entry.md)；相關文件頁的共通結構見[相關文件頁面規範](../../interface-specs/related-documents.md)；記錄紙格式另見[小苗實驗記錄紙](record-paper.md)。

本文件是小苗實驗資料結構、歷史資料整理、正式匯入與後續日常輸入功能的唯一規格。特定日期、樣區或個體經人工確認的資料修正不重複寫在本文件，分別記錄於：

- [第一期人工修正紀錄](phase1-manual-corrections.md)
- [第二期人工修正紀錄](phase2-manual-corrections.md)

## 一、共用資料分層

系統將資料分成三層：

1. **raw 原始資料**：逐列保存上傳檔案內容及來源檔名、工作表與列號，不套用清理規則。
2. **cleaned 修改後資料**：由 raw 自動產生，套用共用整理規則及人工修正規則，是建立正式資料的唯一來源。
3. **正式資料**：由實驗、樣區、個體及每月調查紀錄組成，供日常輸入、查詢與後續匯出使用。

raw 不因後續判定而改寫。重新上傳時，系統必須能由 raw 重建 cleaned，再由 cleaned 建立正式資料。

## 二、共用正式資料表

### 1. `experiments`

- `id`
- `code`
- `name`
- `description` nullable
- `start_date` nullable
- `end_date` nullable
- `is_active`
- timestamps

### 2. `plots`

- `id`
- `experiment_id`
- `plot_code`
- `density` nullable，使用 unsigned integer
- `note` nullable
- timestamps

`experiment_id + plot_code` 必須唯一。

### 3. `individuals`

- `id`
- `plot_id`
- `individual_no`
- `survey_order` nullable
- `species_id` nullable
- `species_name` nullable
- `start_date`
- `end_date` nullable
- `death_date` nullable
- `note` nullable
- timestamps

規則：

- `plot_id + individual_no` 必須唯一。
- `start_date` 是個體第一次正式出現在調查紀錄中的日期。
- `end_date = NULL` 表示仍需追蹤；有值表示已停止追蹤。
- 個體表不保存原始檔來源欄位，也不使用 `end_reason`。
- 補植苗使用新編號建立新個體，不建立補植關聯資料表。
- 實驗只有香楠與黃杞，正式資料保存 `species_name`；目前不連接物種名錄，`species_id` 保持 NULL。

### 4. `records`

- `id`
- `individual_id`
- `survey_date`
- `survey_period`，固定格式 `YYYY-MM`
- `status`
- `length` nullable
- `cotyledon_count` nullable
- `leaf_count` nullable
- `note` nullable
- `is_backfilled` boolean，預設 `false`
- timestamps

必要限制：

- `survey_period` 由 `survey_date` 的年月自動產生，上傳檔不需要提供此欄位。
- `individual_id + survey_date` 必須唯一。
- `individual_id + survey_period` 必須唯一，確保同一株每月最多一筆正式紀錄。
- 正式 records 不保存來源檔名、工作表名稱或原始列號。

## 三、共用調查與狀態規則

調查原則為每月一次。日常輸入時選擇調查期 `YYYY-MM` 及實際調查日期，只列出 `end_date IS NULL` 的個體。

正式資料使用 A、D、G：

| Status | 定義 | 後續是否追蹤 | 正式量測值 |
| --- | --- | --- | --- |
| A | 存活 | 是 | 實際量測值；補登時為 -1 |
| D | 判斷死亡，但仍看到個體或屍體 | 是 | 適用欄位為 -4 |
| G | 個體與屍體均已消失 | 否 | 適用欄位為 -4 |

正常流程為 `A → D → G`，也允許 `A → G`：

- 第一次 D 設定 `death_date`，但不設定 `end_date`，下期仍需調查。
- 不因連續多次 D 自動結束追蹤。
- D→G 時保留第一次 D 的 `death_date`，以 G 的日期設定 `end_date`。
- A→G 且中間沒有 D 時，G 的日期同時是 `death_date` 與 `end_date`。
- 第一次有效 G 是最後一筆正式調查紀錄；來源資料中之後重複抄錄的 G 不匯入正式 records。
- 一般情況不提供額外的人工結束操作；第一期 D＋補植的歷史例外依第一期規則處理。

缺少某月紀錄代表資料缺漏，不得自動推定為 D、G 或死亡。

## 四、共用恢復追蹤與補登規則

後續重新確認個體為 A 時，必須使用「恢復追蹤」流程，不可直接在 G 或 D 後新增一般 A：

1. 找出最後一筆有實際量測的 A。
2. 到本次重新找到的 A 之前，既有 D／G 改為補登 A。
3. 停止追蹤期間缺少的調查期新增補登 A。
4. 補登紀錄不沿用或推測量測值。
5. 清除個體 `end_date` 與先前暫定的 `death_date`。
6. 本次重新找到並實際量測的 A 維持一般紀錄。

補登 A：

```ini
status = A
length = -1
leaf_count = -1
cotyledon_count = -1（第二期）
is_backfilled = true
```

本次實際量測的 A：

```ini
status = A
量測欄位 = 實際值
is_backfilled = false
```

## 五、共用量測識別值

- 一般 A：保存實際量測值，`is_backfilled = false`。
- 補登 A：適用量測欄位全部為 -1，`is_backfilled = true`。
- D／G：適用量測欄位全部為 -4，`is_backfilled = false`。
- 已完成輸入的適用量測欄位不可為 NULL；NULL 表示資料尚未輸入或不完整。
- 匯出時原樣保留正式資料中的 -1、-4 與實際量測值。

## 六、Raw 產生 cleaned 的共用整理規則

以下規則只套用於 cleaned，raw 不得修改：

1. 長度有數值且不為 -4 時，Status 統一為 A。
2. 長度為 -4，但 Status 為 A 或空白時，沿用同株前一筆 D／G；前一筆不是 D／G 時設為 D。
3. 長度為 -4 時，葉片數統一為 -4；第二期 D／G 的子葉數也統一為 -4。
4. 第一次 G 後只有單次 D 時，將該 D 改為 G。
5. 第一次 G 後有連續多次 D 時，保留 D 區段，並將該區段之前過早出現的 G 改為 D。
6. D 後恢復實測 A 時，恢復前連續 D 改為補登 A，適用量測欄位改為 -1。
7. 已人工確認的 G 後恢復個體，依恢復追蹤規則將誤判期間改為補登 A。

特定調查日期、Plot、個體編號、量測值、漏記或人工判定，必須寫入對應期別的人工修正紀錄，並同步實作於 normalizer：

- [第一期人工修正紀錄](phase1-manual-corrections.md)
- [第二期人工修正紀錄](phase2-manual-corrections.md)

## 七、共用正式匯入流程

第一期與第二期都使用同一操作方式：

1. 管理員上傳原始資料。
2. 系統逐列保存至 raw。
3. 系統重建 cleaned，套用共用及人工修正規則。
4. 系統依 `survey_date` 由舊到新逐批建立正式 plots、individuals 與 records。
5. 每個調查日期在獨立 database transaction 內完成。
6. 若該日期資料不合或寫入失敗，整批回滾並停止後續日期；raw 與 cleaned 保留。
7. 頁面分別回報 raw、cleaned、修正數及正式匯入筆數。

正式匯入前至少驗證：

- Plot、個體編號、物種、Status 及適用量測欄位不可缺漏。
- Status 只能是 A、D、G。
- A 必須是完整實測值或完整的補登 -1 組合，不接受部分 -1。
- D／G 的適用量測欄位必須全部為 -4。
- 同一個體的物種不得在不同調查期改變。
- 同一調查期不得有重複個體。
- 已設定 `end_date` 的個體不得再出現非 G 紀錄。

正式匯入服務採續傳模式，維護時可使用：

```bash
php artisan seedling-experiment:import-phase1
php artisan seedling-experiment:import-phase2
```

## 八、共用正式資料完整性

匯入或日常輸入完成後必須確認：

- 沒有孤立 record。
- 沒有同株同日或同株同月重複紀錄。
- 第一次有效 G 後沒有正式 record。
- `survey_period` 與 `survey_date` 的年月一致。
- 補登 A 的適用量測欄位皆為 -1 且 `is_backfilled = true`。
- D／G 的適用量測欄位皆為 -4 且 `is_backfilled = false`。
- 仍在追蹤的個體若缺少某調查期，視為缺漏資料，不視為死亡。

# 第一期專屬規則

## 九、第一期來源與欄位

第一期 raw／cleaned 使用：

- Date
- Plot
- No.
- Species
- 長度
- 葉片數
- Status
- 備註

第一期沒有子葉數及調查順序：

- `plots.density` 固定為 1。
- `individuals.survey_order` 保持 NULL。
- 正式 records 的 `cotyledon_count` 保持 NULL。

歷史資料條件：

- 27 個調查期。
- 每期 480 個不重複 Plot＋編號。
- 48 個樣區。
- 584 個歷史個體。
- cleaned 共 12,960 筆。

## 十、第一期正式匯入規則

- A 匯入長度及葉片數實際值。
- D／G 的長度與葉片數必須為 -4。
- 第一次 G 設定 `end_date`，後續重複 G 不匯入。
- 第一期間沒有恢復追蹤案例，正式資料 `is_backfilled = true` 為 0 筆。
- 補植的新編號建立新個體；補植備註中的新苗量測不另建當期正式 record，以新編號下一次正式出現為起點。

### D＋補植結束追蹤例外

第一期有 104 筆舊苗紀錄明確包含補植備註：

- 40 筆補植當期為 G，依一般 G 規則設定 `end_date`。
- 64 筆補植當期為 D，且補植後舊編號不再出現；Status 保留 D，但以該次 `survey_date` 設定 `end_date`。

此例外不可擴張成所有 D 自動結束追蹤。

第一期目前正式資料：

- 48 個樣區。
- 584 株個體。
- 9,609 筆 records。
- 0 筆補登紀錄。
- 實驗期間 `2023-11-09` 至 `2026-03-09`，已結束。

第一期特定日期、個體及補植編號的人工調整詳見[第一期人工修正紀錄](phase1-manual-corrections.md)。

# 第二期專屬規則

## 十一、第二期來源與欄位

第二期 raw／cleaned 使用：

- 調查順序排序
- Date
- Plot
- Density
- No.
- Species
- 長度
- 子葉數
- 葉片數
- Status
- 備註

規則：

- `survey_order` 必填且大於 0，同一個體各期必須一致。
- `density` 必須是大於 0 的整數，同一 Plot 各期必須一致。
- A 的長度、子葉數、葉片數必須全部為實測值，或全部為 -1。
- D／G 的長度、子葉數、葉片數必須全部為 -4。
- `A + -1/-1/-1` 自動設定 `is_backfilled = true`。
- `GM005-DC / E308 / 2025-04-16 / D` 是經人工確認允許的首次非 A 例外；其他個體首次出現不是 A 時停止匯入。

第二期調查順序已完成一次性整理，暫存表及專用上傳／下載功能已移除。raw 上傳格式必須直接包含已確認的調查順序，raw 產生 cleaned 時原樣傳遞；人工補建且沒有 `raw_id` 的 cleaned 紀錄沿用同株其他紀錄的順序。

## 十二、第二期正式匯入規則

- 建立 Plot 時使用 cleaned 的 density。
- 建立個體時保存 cleaned 的 survey_order。
- 第一次 G 設定 `end_date`，後續重複 G 不匯入。
- 第二期仍在進行，維持 `experiments.is_active = true`，實驗 `end_date` 保持 NULL。
- 第二期目前沒有補植備註或補植關係，不套用第一期 D＋補植例外。

第二期目前正式資料：

- 18 個調查期。
- 36 個樣區。
- 440 株個體。
- 5,725 筆 records。
- 24 筆補登紀錄。
- 資料期間 `2025-04-16` 至 `2026-09-20`，仍在進行。

第二期特定日期、Plot、編號、漏記、復活及量測值的人工調整詳見[第二期人工修正紀錄](phase2-manual-corrections.md)。

目前確認的單株紙本漏記只有 `PE001-DC / M819-1 / 2025-08-29`；系統已補建 `-1/-1/-1/A` 並設定 `is_backfilled = true`。第一、二期 cleaned 依各 Plot 的實際調查日期檢查後，沒有其他個體中間缺期。

# 溫室實驗專屬規則

## 十三、溫室資料上傳與正式匯入

- 本機初次整理分兩步：先上傳「其中一次、有調查順序」的參考檔，再上傳不含順序的完整歷史資料。
- 系統以「處理代碼＋編號」識別個體，將參考檔的調查順序套入該株所有歷史紀錄；遮蔭處理由處理代碼最後一碼推導。
- 完整資料全部配對成功後，才建立 cleaned 與正式資料；任何個體找不到順序、同一順序對到不同個體或同株順序不一致時，整批停止。
- 整理後可下載欄位為調查順序、遮蔭處理、日期、物種、處理、編號、高度、葉片、狀態及附註的完整 CSV。
- 主機部署時只需上傳上述「含調查順序的完整 CSV」，不必再次上傳參考檔。
- 日期以 CSV 的日期欄為準，不依檔名推算調查期。
- 處理代碼存為 `plots.plot_code`，編號存為 `individuals.individual_no`；兩者共同識別個體。
- 遮蔭處理保留於 raw／cleaned，並驗證必須等於處理代碼最後一碼；正式資料可由 Plot 代碼推導。
- 子葉數不適用，正式 records 的 `cotyledon_count` 保存 NULL。
- 完整歷史資料上傳後依序完成順序合併、`greenhouse_raw`、`greenhouse_cleaned`、`plots`、`individuals` 及 `records`。
- raw 保存來源文字及檔案列號；cleaned 去除代碼前後空白、轉換日期與數值型別，再驗證後寫入正式表。
- 若正式匯入驗證失敗，停止正式匯入並保留 raw／cleaned，供確認及修正。
- A 必須有非負的高度及葉片數；D／G 的高度及葉片數必須都是 -4。
- 第一次調查可保留 D；D 設定 `death_date` 但不設定 `end_date`，後續仍需追蹤。

## 十四、Migration 與部署

- 小苗實驗的 migration 檔案統一放在 Laravel 的 `database/migrations`。
- migration 類別內以 `fs_seedling_exp` connection 操作小苗實驗資料庫；Laravel 的 migration 執行紀錄仍統一保存在主資料庫的 `migrations` 表。
- 本機與主機部署均使用一般的 `php artisan migrate`，不可使用 `php artisan migrate --database=fs_seedling_exp`。後者會在小苗實驗資料庫另建 `migrations` 追蹤表，造成 Laravel 主追蹤表誤判 migration 尚未執行。
- 資料匯入與人工整理不是 migration；由上傳處理流程執行，migration 只負責資料表結構及必要的一次性結構資料調整。

## 十五、相關文件

- 工作選擇頁進入「福山 小苗實驗」時，預設開啟「相關文件」。
- 「相關文件」是小苗實驗的第一個主頁籤。
- 小苗實驗記錄紙與一般小苗調查格式不同，本階段先完成規格盤點，尚未建立下載功能。
- 已確認記錄紙輸出為 Excel，欄位與批次關係詳見[小苗實驗記錄紙](record-paper.md)。

通用的相關文件頁結構見[相關文件頁面規範](../../interface-specs/related-documents.md)。
