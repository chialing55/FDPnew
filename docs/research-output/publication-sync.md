# 老師文獻同步與確認

## 使用流程

- 每月 1 日 03:00（Asia/Taipei）從張楊家豪老師的 Zotero My Publications 取得公開文獻。
- 資料直接建立在正式 `publications`；老師網站 `/changyang/publications` 會立即列出這些已公開且標記為老師的文獻。
- 只有首次從 Zotero 新增、且沒有比對到既有資料的文獻，會標記「樣區歸屬：待確認」。後台「研究成果／學術產出」的導覽數字與篩選可找到它們。
- 既有文獻在初次轉換時全部設為老師文獻與「已確認」；不會重設既有樣區關聯或改為待確認。
- 已存在的正式文獻以 DOI 比對（忽略大小寫、DOI URL 前綴）；其次以正規化標題與年份比對。兩筆都有不同 DOI 時不以標題自動合併。
- 找到單筆既有文獻時只補上老師標記與 Zotero ID，保留中文欄位、PDF、公開狀態、樣區確認狀態及既有關聯。
- 若同時匹配多筆，該筆同步會略過並寫入日誌，避免自動合併。
- 首次同步會取得所有公開文獻（包含舊作與預印本），不是只取得本月新增。預印本保留類型，與不同 DOI 的正式版本分別審核。
- 已取得的來源項目不會因定期同步而覆蓋人工修改，也不會因 Zotero 移除資料而刪除網站文獻。Zotero 未提供作者名單時須在審核表單補齊。

## 同步來源

主要來源是老師公開的 Zotero My Publications：
https://www.zotero.org/c.h.changyang

這個區域與老師的完整私人文獻庫分開；目前 API 可讀取 50 筆頂層文獻，PDF 附件不會匯入。Zotero 官方 API 對公開資料提供唯讀存取，也支援版本與分頁，適合做定期同步。

Google Scholar 個人頁保留為人工核對連結：
https://scholar.google.com/citations?user=DBdkpXwAAAAJ&hl=en

Scholar 程式存取回傳 403，因此不作為自動同步來源。Zotero 中標記為 My Publications 的資料是老師自行維護的清單，匯入時預設為老師文獻。

設定在 `config/publication_sync.php`。`ZOTERO_USER_ID` 可覆寫 Zotero 使用者 ID；目前的公開庫不需要 API key。請勿將私人 API key 放入版本控制。

## 部署與初次同步

先備份並套用新增的遷移（不需要重跑舊資料匯入）：

```sh
docker compose -f docker-compose.prod.yml exec app php artisan migrate --force
docker compose -f docker-compose.prod.yml exec --user www-data app php artisan changyang:sync-publications
docker compose -f docker-compose.prod.yml up -d scheduler
docker compose -f docker-compose.prod.yml exec app php artisan schedule:list
```

以上命令須在新版 app image 建置／啟動後執行。正式 compose 新增 scheduler 服務，與 app 共用 storage（排程鎖與日誌）；每次部署須一併重建 scheduler 容器，確保使用新版 image。不要另開第二個 host cron 重複執行相同排程。

開發環境的 `docker-compose.yml` 也包含 scheduler，沿用目前 app 的 `fdpnew-app` image 並掛載專案程式碼。套用遷移後執行 `docker compose up -d --no-deps scheduler`；之後一般的 `docker compose up -d` 會一併啟動。開發用 Docker 關閉期間不會執行排程；正式每月同步應由持續運作的正式環境負責。

非 Docker 環境須配置每分鐘執行 `php artisan schedule:run` 的 cron，或常駐 `php artisan schedule:work`。只修改 PHP 排程程式碼不會自行啟動排程程序。

查看 `storage/logs/publication-sync.log` 與 Laravel 日誌。同步失敗會回傳非零 exit code，已取得資料仍保留，可重跑命令補齊。HTTP 請求有逾時及有限重試；重跑不會重複建立文獻。同步命令有互斥鎖避免同時執行。

審核不會直接取得論文 PDF，也不推斷樣區歸屬。
