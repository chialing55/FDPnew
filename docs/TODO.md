# 暫緩工作待辦事項

> 本文件記錄目前暫緩、日後需要接續的工作。現正進行中的 ForestGEO／GEO-TREES 工作不列於此處。

## 每木

- [ ] 重整每木輸入介面，且在重整完成前維持既有輸入流程正常運作。
- [ ] 將登入與操作者識別統一改用 Laravel `users`。
- [ ] 移除每木功能對 `fs_base.login`、`login2` 的依賴。
- [ ] 將每木物種選單與驗證改用 `plant_catalog.site_species`、`taiwan_checklist` 及 `species_research_links`。
- [ ] 移除每木功能對 `fs_base.tree_splist`、`fs_base.spinfo` 的依賴。
- [ ] 盤點 `tree_splist` 是否仍包含每木專用的非分類設定，並將需要保留的設定移至適當資料庫。
- [ ] 確認第一次輸入、第二次輸入、新增樹、修改、資料比對、地圖及 PDF 功能均正常。
- [ ] 完成資料比對與備份後，再決定是否停用或刪除舊資料表。

詳細說明：[每木輸入介面重整待辦](tree/entry-refactor.md)

## 種子

- [ ] 在正式環境完成 `fruiting_ht` 欄位搬移 migration。
- [ ] 確認 `fs_seeds.splist.fruiting_ht` 非空筆數及 A／B 分布符合來源資料。
- [ ] 確認未對應的 6 個物種不需要加入目前的種子輸入名錄。
- [ ] 驗證種子輸入、資料檢查、研究統計及下載功能正常。
- [ ] 搜尋並確認正式程式已無 `FsBaseSeedsSpinfo` 或 `fs_base.seeds_spinfo` 的執行路徑。
- [ ] 完成正式環境備份與驗證後，再評估刪除 `fs_base.seeds_spinfo`。

詳細說明：[`fs_base.seeds_spinfo` 淘汰待辦](seeds/spinfo-retirement.md)

## 維護方式

- 本文件只保留暫緩但仍需處理的工作。
- 詳細背景與技術設計放在各工作項目的 note，本文件只列可執行的摘要與連結。
- 工作恢復時，逐項更新核取方塊；完成且不再需要追蹤的項目可從本文件移除。
- 目前進行中的工作若日後暫緩，再將尚未完成的項目加入本文件。
