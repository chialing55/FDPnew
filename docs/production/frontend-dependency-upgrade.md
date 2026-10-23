# 前端開發依賴升級待辦

## 現況

2026-10-23 完成安全稽核後，正式環境使用的 npm 依賴沒有已知漏洞：

```bash
npm audit --omit=dev
```

完整執行 `npm audit` 時仍有 11 個警告（1 low、3 moderate、7 high）。這些警告來自 Vite、Tailwind CSS、BrowserSync、esbuild、chokidar、braces 等前端建置工具鏈，而不是正式 PHP 容器中的執行期套件。

正式 Docker 映像採多階段建置。Node 與 `node_modules` 只用來產生 CSS／JavaScript，最後的 PHP Runtime 不包含這些開發工具，因此目前不因這 11 個警告而阻擋部署。

## 暫緩原因

將完整 npm audit 強制降至 0，預計需要升級 Tailwind CSS 4、Vite 等大版本。這類升級可能改變 CSS 掃描、設定格式、外掛介面及建置結果，需配合前端畫面回歸測試，不適合在沒有前端調整需求時只為清除警告直接升級。

## 何時需要處理

符合下列任一情況時，應重新評估並執行升級：

- 下一次進行前端版面、Tailwind 樣式或 Vite 建置設定調整。
- 漏洞影響編譯產物、套件安裝流程或供應鏈安全。
- 開發伺服器需要對非本機或不受信任網路開放。
- CI／建置環境的安全政策要求完整 audit 為 0。
- 現有 Node、Vite 或 Tailwind 版本停止維護。

若只是後端功能修改，且仍在可信任環境中建置、未將 Vite 或 BrowserSync 開發伺服器暴露至公網，可以繼續使用目前鎖定版本。

## 升級工作

- [ ] 建立獨立升級分支並保留升級前的畫面截圖或測試基準。
- [ ] 查閱 Tailwind CSS 4、Vite 與 Laravel Vite Plugin 的升級說明。
- [ ] 更新 `package.json` 與 `package-lock.json`，不要直接以 `npm audit fix --force` 取代相容性評估。
- [ ] 執行 `npm audit`，確認剩餘警告及其是否只存在於無法替換的工具鏈。
- [ ] 執行正式前端建置。
- [ ] 檢查公開頁面、登入頁面及 Filament 後台是否跑版或互動失效。
- [ ] 重新建置正式 Docker 映像，確認最終映像沒有包含 Node 或 `node_modules`。

## 驗證指令

以下各指令應分開執行：

```bash
npm ci
```

```bash
npm audit
```

```bash
npm audit --omit=dev
```

```bash
npm run build:css
```

```bash
npm run build:admin-css
```

```bash
./node_modules/.bin/vite build
```

漏洞資料會持續更新；處理時應以當日 `npm audit` 結果為準，而不是以本文件記錄的 11 個警告作為固定基準。
