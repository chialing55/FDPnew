# `fs_base.seeds_spinfo` 淘汰待辦

> 狀態：待正式環境搬移驗證後刪除。不要在驗證前直接刪表。

## 現況

- 程式中只有 `FsBaseSeedsSpinfo` Model 宣告此表，沒有執行路徑使用該 Model 或直接查詢 `seeds_spinfo`。
- 種子輸入、驗證與研究統計實際使用 `fs_seeds.splist`。
- `2026_08_27_000002_move_fruiting_ht_to_fs_seeds_splist` 已在 `fs_seeds.splist` 新增 `fruiting_ht`，並以 `sp` 搬移現有種子名錄物種的資料。

## 搬移盤點

- `fs_base.seeds_spinfo`：108 種，`sp` 無重複。
- 可對到 `fs_seeds.splist.sp`：102 種。
- 未對到的 6 種：`CINNAU`、`CYCLSE`、`ILEXFI`、`LORANT`、`SAURTR`、`SYMPWI`。
- 上述 6 種未出現在 `fs_seeds.fulldata`，不是目前種子輸入名錄，因此不為搬移欄位而新增至 `splist`。
- `fs_seeds.splist` 其他物種（主要為 `UNK*`）的 `fruiting_ht` 保持 `NULL`。

## 刪表前檢查

1. 正式環境 migration 成功完成。
2. `fs_seeds.splist` 的 `fruiting_ht` 非空筆數應為 102。
3. A/B 分布與來源的有效交集一致。
4. 種子輸入、資料驗證、研究統計與下載功能正常。
5. 全專案搜尋確認沒有 `FsBaseSeedsSpinfo` 或 `seeds_spinfo` 的執行路徑。
6. 備份 `fs_base.seeds_spinfo`。

完成以上檢查後，另建獨立 migration：

- 刪除 `fs_base.seeds_spinfo`。
- 刪除 `app/Models/FsBaseSeedsSpinfo.php`。

刪表不要併入搬移 migration，以保留部署後的資料驗證與回復空間。
