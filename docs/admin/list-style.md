# 後台編輯列表樣式

後台列表採用一致的「整列可編輯」設計：滑過資料列時顯示底色，點擊資料列可進入編輯；最右欄保留明確的鉛筆圖示「編輯」動作。

## Filament Table Resource

優先使用 Filament 內建 Table action，而非自行建立連結：

```php
use Filament\Tables\Enums\ActionsPosition;

->actions([
    Tables\Actions\EditAction::make()
        ->label('編輯')
        ->icon('heroicon-o-pencil-square')
        ->color('success'),
], ActionsPosition::AfterColumns)
```

`ActionsPosition::AfterColumns` 會把動作固定在最右側。公開狀態使用 `IconColumn::make('is_active')->boolean()`，避免以「公開／隱藏」文字重複佔用欄位。

## 嵌入在頁面分頁的自訂列表

張楊家豪個人網站後台的 News、People、Publications 使用共用 CSS class：

- `fi-changyang-list-table-wrap`
- `fi-changyang-list-table`
- `fi-changyang-list-table__row`
- `fi-changyang-list-table__edit`

公開狀態使用 `heroicon-o-check-circle`（綠色），隱藏使用 `heroicon-o-x-circle`（灰色）。資料列需要保留拖曳把手、按鈕與連結的原有行為；整列點擊事件應排除 `a`、`button`、`input`，再導向編輯 URL。

列表若不需要整體拖曳排序，應在查詢層使用 `paginate()`，而不是把全部資料交給 Blade。需要整體排序的 News 與 People 則必須維持完整資料集，直到改為支援跨頁排序的專用 Livewire 元件。

## 張楊家豪頁面入口

從單篇 News、People、Gallery、Publications 返回對應頁面分頁時，統一使用 `App\Filament\ChangYang\Support\ChangYangPageLink::tab($slug)`。不要在 Resource 內重複組裝 Page 查詢與 `tab` query string。
