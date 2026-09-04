# ForestGEO `census5_part` 產生方式

## 用途

`fs_geo_tree_survey.census5_part` 是福山 ForestGEO／GEO-TREES 調查的背景資料表。目前用於產生調查紀錄紙，後續也會作為 `record1`、`record2` 輸入工作表的來源及資料檢查時的前次調查資料。

此表不是由網站頁面自動產生。目前的產生方式是由管理人員手動執行：

[`database/scripts/import_geo_tree_survey_census5_part.sql`](../../database/scripts/import_geo_tree_survey_census5_part.sql)

## 執行前提

執行 SQL 前須確認：

- `fs_geo_tree_survey.census5_part` 已存在；目前這支 SQL 只匯入資料，不會建立資料表。
- `census5_part` 的欄位須能接收 SQL 中列出的 23 個欄位。
- `fs_tree.base`、`fs_tree.census5` 及 `fs_base.tree_splist` 可以由同一個資料庫帳號讀取。
- 目標表應為空表，或已事先確認重複執行不會造成主鍵衝突。這支 SQL 不會先清空目標表，也沒有使用 `INSERT IGNORE` 或 `ON DUPLICATE KEY UPDATE`。
- 正式環境執行前應先備份既有 `census5_part`。

## 資料來源

匯入時使用三張既有資料表：

| 資料表 | 用途 |
| --- | --- |
| `fs_tree.census5` | 第五次每木調查的 stem 與調查資料 |
| `fs_tree.base` | 樹號、物種代碼及樣區位置 |
| `fs_base.tree_splist` | 依 `spcode` 取得中文種名 `csp` |  => 之後需修改，已經植物名錄資料改到 `plant.catalog.site_species`

三張表的連接方式如下：

```text
fs_tree.census5.stemid
        ↓
選出符合條件的 stemid
        ↓
fs_tree.census5.tag = fs_tree.base.tag
        ↓
fs_tree.base.spcode = fs_base.tree_splist.spcode
```

上述連接皆為 `INNER JOIN`。若 `base` 或 `tree_splist` 找不到對應資料，該筆 stem 不會被寫入 `census5_part`，因此匯入後須核對筆數。

## 樣區篩選

SQL 以 `base.plotx`、`base.ploty` 計算 5 × 5 的大樣區編號：

```sql
FLOOR(plotx / 100) * 5 + CEIL(ploty / 100)
```

目前只納入以下編號：

```text
4、7、8、12、16、17、20、21、22、25
```

若未來 ForestGEO 的調查範圍改變，應修改 SQL 中兩處相同的 `IN (...)` 清單，並確認紀錄紙及輸入介面的範圍也一致。

## Stem 篩選規則

第一部分會選入同時符合下列條件的 stem：

- `base` 未被刪除，亦即 `deleted_at` 是 `NULL` 或空字串。
- `tag` 不是以 `G` 開頭。
- 位於指定的大樣區。
- `census5.dbh >= 9.5`。

第二部分會補入主幹：只要同一株樹有任一分支的 `dbh >= 9.5`，就另外選入該株 `branch = 0` 的主幹。主幹本身的 DBH 不一定大於或等於 9.5。

兩部分使用 `UNION` 合併，因此重複的 `stemid` 只會保留一筆。

## 寫入欄位

從 `census5` 複製：

```text
stemid、tag、branch、dbh、h1、h2、code、status、pom、note、date、
confirm、tofix、alternote
```

從 `base` 複製：

```text
spcode、qx、qy、sqx、sqy
```

從 `tree_splist` 複製：

```text
csp
```

匯入時固定初始化：

```text
updated_at = 空字串
updated_id = 空字串
show       = 0
```

`census5_part` 保存的是第五次每木調查背景資料；建立 ForestGEO 的 `record1`、`record2` 時，才依每木輸入工作表的規則重設本次輸入欄位。

## 執行方式

由具有上述資料庫權限的管理人員，在 MySQL 用戶端執行 SQL 檔。例如進入 MySQL 後：

```sql
SOURCE database/scripts/import_geo_tree_survey_census5_part.sql;
```

實際路徑應依執行主機上的專案位置調整。這項操作目前不應交由一般網站使用者執行。

## 匯入後檢查

SQL 檔最後會回傳以下統計：

| 欄位 | 檢查目的 |
| --- | --- |
| `total_rows` | 總筆數 |
| `distinct_tags` | 不重複植株數 |
| `distinct_stems` | 不重複 stem 數 |
| `nonblank_show` | `show` 不為 0 的筆數，初始化後預期為 0 |
| `nonblank_updated_id` | 已有輸入者的筆數，初始化後預期為 0 |
| `nonblank_updated_at` | 已有更新時間的筆數，初始化後預期為 0 |
| `g_tags` | `G` 開頭 tag 的筆數，依目前規則預期為 0 |

另外建議檢查：

- `total_rows` 應等於 `distinct_stems`。
- 各 `qx-qy` 的筆數是否符合預期。
- 是否因缺少 `tree_splist` 對應資料而漏掉 stem。
- 所有符合條件的 `dbh >= 9.5` stem 是否存在。
- 有合格分支的植株，其 `branch = 0` 主幹是否一併存在。

## 目前限制與後續方向

- 尚無建立 `census5_part` 結構的 migration。
- 尚無網站後台的產生或重建功能。
- 匯入流程尚未包在 transaction 中。
- 重複執行前須由管理人員自行處理既有資料。
- 中文種名目前仍依賴舊的 `fs_base.tree_splist`。

若日後改成網站後台操作，應加入權限限制、執行前預覽、transaction、既有資料備份、重複執行保護，以及匯入結果的筆數與缺漏報告。
