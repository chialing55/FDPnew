# 工作項目介面規範

> 分類：資料管理系統／介面規範

新增研究工作項目時，應先參考既有的福山小苗、死亡率調查與種子介面，維持後台視覺與操作方式一致。

## 頁面結構

- 工作項目的 layout 應延伸 `layouts/app2`，並沿用 `includes.header2`、頁首工作列與既有 footer。
- 主內容外層使用 `.content`，確保內容區撐滿剩餘視窗高度，footer 位於頁面底部。
- 頁面內容區使用 `.flex.text_outbox`；內容卡片一律使用 `.text_box`。
- 不要替 `.text_box` 另加陰影、不同圓角、特殊背景或另一套邊框。若只需控制排版，可增加不改變外觀的功能 class。

基本範例：

```blade
<div class="flex text_outbox" style="flex-direction: column; align-items: center;">
    <div class="text_box">
        <h2>頁面標題</h2>
        <hr>
        <p>頁面內容</p>
    </div>
</div>
```

## 標題與訊息

- 卡片標題使用既有的 `<h2>`，標題下方接 `<hr>`；不要自行建立不同字級、字型或顏色的標題元件。
- 成功與錯誤訊息分別使用 `.app-feedback-note--success`、`.app-feedback-note--error`，並保留 `.app-feedback-note` 基礎 class。
- 按鈕、輸入欄位與表格優先沿用全站既有樣式；只有功能確實需要時才增加局部排版 CSS。

## 導覽列

- 第一層使用 `.headerlist`、`.list`；第二層使用 `.listinner`、`.innerlist`。
- 第二層最後一個項目保留 `<hr>`，使 bar 高度與既有小苗、死亡率、種子介面一致。
- 管理員限定項目必須同時做到「介面不顯示」及路由 `admin` middleware 防護。

## 新增前檢查

1. 桌面版內容是否滿版並由 `.content` 撐高。
2. footer 是否在內容不足一頁時仍位於視窗底部。
3. 所有內容卡片是否使用原生 `.text_box`，且沒有自訂陰影。
4. 標題是否使用 `<h2><hr>` 的既有格式。
5. 第二層工作列的高度、hover 與目前項目狀態是否和既有模組一致。
6. 權限是否同時套用到工作卡片、頁面路由及管理員限定操作。
