<x-tree-entry.shell
    title="2026 年 GEO-TREES"
    :entry="$entry"
    :user="$user"
    :note-url="route('admin.fushan.geo-tree-survey.note')">

    @if ($recordTablesReady)
        <x-slot:selector>
            <div style="display:flex; flex-direction:column; align-items:flex-start;">
                <x-tree-entry.coordinate-selector
                    :qx-options="$qxOptions"
                    :qy-options="$qyOptions"
                    :qx="$qx"
                    :qy="$qy"
                    :previous-action="$previousAction"
                    :next-action="$nextAction"
                    :message="$entrynote" />

                @if ($qx === null || $qy === null)
                    <p style="margin:8px 0 0; font-weight:400;">
                        @if ($suggestedStartingPoint)
                            尚有資料未輸入，建議從樣方
                            ({{ $suggestedStartingPoint['qx'] }}, {{ $suggestedStartingPoint['qy'] }})、
                            小樣區 ({{ $suggestedStartingPoint['sqx'] }}, {{ $suggestedStartingPoint['sqy'] }}) 開始。
                        @else
                            本次需要輸入的資料皆已有日期。
                        @endif
                    </p>
                    <p style="margin:8px 0 0; color:#666; font-weight:400;">
                        不需輸入的樣區：
                        @forelse ($excludedQuadrats as $quadrat)
                            ({{ $quadrat['qx'] }}, {{ $quadrat['qy'] }}){{ !$loop->last ? '、' : '' }}
                        @empty
                            無
                        @endforelse
                    </p>
                @endif
            </div>

            @if ($qx !== null && $qy !== null)
                <x-tree-entry.subquadrat-selector
                    :selected-x="$sqx"
                    :selected-y="$sqy" />
            @endif
        </x-slot:selector>

        @if ($qx !== null && $qy !== null)
            <x-tree-entry.panel title="每木調查資料">
                <x-slot:reminders>
                    <ul>
                        <li><b>輸入資料後需按 <button class="datasavebutton" style="width:auto;" disabled>儲存</button>，才能確實將資料儲存。</b></li>
                        <li>日期空白的資料視為尚未輸入，儲存時會跳過；日期有值的資料必須通過全部檢查才會一起儲存。</li>
                        <li>開啟特殊修改前會先儲存主表；若主表檢查未通過，請先修正標示的欄位。</li>
                        <li>日期格式：YYYY-MM-DD。每筆資料皆需輸入日期，日期為空白者視同未輸入。</li>
                        <li>status 不為空值時，dbh 需為 0，且 code 不得有值；status 為空值時，dbh 不得為 0。</li>
                        <li>dbh 必須大於或等於上次調查，或勾選縮水。</li>
                        <li>dbh 顯示 M 代表該 stem 已列入死亡率調查；顯示 -- 代表前次 dbh 小於 9.5。灰色資料列不需輸入。</li>
                        <li>code、POM、note 與特殊修改規則將沿用每木調查。</li>
                    </ul>
                </x-slot:reminders>

                <x-tree-entry.subquadrat-navigation
                    :x="$sqx"
                    :y="$sqy"
                    :previous-action="$previousSubquadratAction"
                    :next-action="$nextSubquadratAction" />

                <x-tree-entry.handsontable
                    id="geo-tree-entry-hot"
                    :save-enabled="true"
                    save-label="儲存" />

                <x-tree-entry.special-modification id="geo-tree-entry-hot-special" />
            </x-tree-entry.panel>
        @endif
    @else
        <x-tree-entry.panel title="尚未建立輸入工作表">
            <p>請先執行 GEO-TREES record1／record2 migration，再進入輸入頁。</p>
            <p>{{ $entrynote }}</p>
        </x-tree-entry.panel>
    @endif
</x-tree-entry.shell>
