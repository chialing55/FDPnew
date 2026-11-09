# 小苗實驗資料檢視

> 分類：資料管理系統／研究工作項目／小苗實驗

本文件記錄福山小苗實驗資料檢視的資料來源、畫面欄位與查詢功能。共通的畫面格式另見[資料檢視介面規範](../../interface-specs/data-viewer.md)。

## 一、資料分類

資料檢視主頁以頁籤分成三個資料集：

| 頁籤 | experiment code |
| --- | --- |
| 地被實驗第一期 | `groundcover_phase1` |
| 地被實驗第二期 | `groundcover_phase2` |
| 溫室實驗 | `greenhouse` |

切換頁籤時會清除前一個資料集的篩選、頁碼與排序狀態，再依新的 experiment code 重新查詢。三個資料集不會混合計數或顯示。

## 二、資料來源

資料檢視只讀取正式資料表，不顯示 raw 或 cleaned 上傳處理表：

- `experiments`：實驗名稱與代碼。
- `plots`：樣區及密度。
- `individuals`：小苗編號與物種。
- `records`：各調查期的量測與狀態。

資料關聯為：

```text
experiments
└── plots
    └── individuals
        └── records
```

所有資料列、總筆數及篩選選項，都必須套用目前頁籤的 experiment code。

## 三、顯示欄位

| 畫面欄位 | 資料欄位 | 說明 |
| --- | --- | --- |
| 調查年月 | `records.survey_period` | `YYYY-MM` 格式 |
| 調查日期 | `records.survey_date` | 實際調查日期 |
| Plot／處理 | `plots.plot_code` | 地被實驗顯示為 Plot；溫室實驗顯示為「處理」 |
| Density | `plots.density` | 僅地被實驗第二期顯示 |
| No. | `individuals.individual_no` | 個體編號 |
| 種類 | `individuals.species_name` | 直接使用個體表物種名稱 |
| 長度 | `records.length` | `NULL` 顯示空白 |
| 子葉數 | `records.cotyledon_count` | 僅地被實驗第二期顯示 |
| 葉片數 | `records.leaf_count` | `NULL` 顯示空白 |
| Status | `records.status` | 顯示實際調查狀態 |
| 備註 | `records.note` | 保留原有文字 |

「補資料」屬內部資料處理標記，第一期、第二期及溫室實驗的資料檢視均不顯示。畫面不會為空白欄位補造 `0`、`-1` 或 `-4`。溫室實驗尚無正式資料時顯示空資料訊息；未來資料寫入相同正式表後，不需另做一套檢視頁面。

## 四、篩選功能

- 調查年月：完全相符。
- Plot／處理：完全相符；溫室實驗的篩選標的是處理代碼。
- No.：由輸入內容進行前綴查詢。
- 種類：固定使用 `all`、`黃杞`、`香楠` 下拉選單，選擇物種時採完全相符查詢。
- 長度：提供 `>`、`≧`、`=`、`≦`、`<` 數值比較。
- Status：完全相符。

每次執行篩選後回到第 1 頁；篩選結果的總筆數顯示在標題旁。

## 五、排序與分頁

可排序欄位：

- 調查日期
- Plot／處理
- No.
- 種類

排序只接受程式白名單中的欄位。第一次點擊採升冪，再次點擊同一欄位切換為降冪。若排序值相同，再依調查日期、Plot 及個體編號穩定排序。

每頁顯示 50 筆，提供上一頁、下一頁及指定頁碼。跨調查年月的第一列加上較粗的分隔線。

## 六、程式位置

- Livewire Component：`app/Http/Livewire/Fushan/SeedlingExperimentDataviewer.php`
- 畫面：`resources/views/livewire/fushan/seedling-experiment-dataviewer.blade.php`
- 工作區入口：`resources/views/pages/fushan/seedling-experiment/workspace.blade.php`
- 共用元件：`resources/views/livewire/partials/dataviewer-*.blade.php`

修改欄位、篩選規則或資料來源時，需同步更新本文件；修改跨工作項目共用的格式或元件時，才更新通用的資料檢視介面規範。
