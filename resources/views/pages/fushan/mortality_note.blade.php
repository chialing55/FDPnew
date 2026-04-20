@extends('layouts/mortality')

@section('pagejs')
    <script>
        $(function() {
            $('.list4').addClass('now');
            $('.list4 hr').css('color', '#91A21C');

            $('#mortality-note-comment-toggle').on('click', function() {
                const $panel = $('#mortality-note-comment-list');
                const isVisible = $panel.is(':visible');

                $panel.stop(true, true).slideToggle(160);
                $(this).text(isVisible ? '展開備註清單' : '收合備註清單');
            });
        });
    </script>
@endsection

@section('rightbox')
    @php
        $commentCategories = [
            'stem_condition' => '樹幹狀態 / stem_condition',
            'POM_issue' => 'POM 問題 / POM_issue',
            'structural_change' => '結構改變 / structural_change',
            'biotic_damage' => '生物危害 / biotic_damage',
            'disease' => '病害 / disease',
            'other' => '其他 / other',
        ];

        $commentOptionsByCategory = [
            'stem_condition' => [
                '基部中空 / HB',
                '中空 / H',
                '與其他枝幹合併 / merged with branch',
                '與主幹合併 / merged with main stem',
                '頂部著根 / trunk tip rooted',
                '爛根 / rotten root',
                '尖端枯萎 / tip end withered',
                '著根 / rooted',
                '板根 / buttress root',
                '被纏繞 / twisted',
                '變形 / deformation',
                '樹幹腐爛 / trunk rotten',
                '接地生根 / Grounded rooting',
                '倒伏 / prostrate',
            ],
            'POM_issue' => [
                '確認縮水 / C',
                '目測估計胸徑 / visually estimated DBH',
                '變更POM位置 / POM changed',
                'POM處破損 / POM brokend',
                'POM<1 / POM<1',
                'POM處腐爛導致測量值縮小 / rot POM',
                'DBH 縮水 / DBH shrink',
                'POM處死亡 / dead at POM',
                'POM處被藤蔓包圍 / POM sticked with climbers',
            ],
            'structural_change' => [
                '被倒樹壓到 / suppressed by a fallen tree',
                '被壓住 / suppressed',
                '基部斷折 / base broken',
                '樹幹倒伏並扎根 / trunk fallen and rooted',
                '斷頭 / broken tip',
            ],
            'biotic_damage' => [
                '被松鼠咬 / ST',
                '白蟻 / termite',
                '蛀蝕 / erosion',
                '有蜂 / beehive',
            ],
            'disease' => [
                '絹皮病 / white stem blight',
            ],
            'other' => [
                '牌子不見 / tag lose',
                'R / R',
                '存活長度增加 / Living length increased',
                'GR / GR',
            ],
        ];
    @endphp

    <div>
        <h2>死亡率調查輸入注意事項</h2>
        <div class='note'>
            <ul style='font-weight: 800;'>
                <li>輸入資料後需按<button class='datasavebutton' style='width:auto'>儲存</button>才能確實將資料儲存。</li>
                <li>可利用「Tab」鍵和「上下左右」鍵在各輸入欄位間移動。</li>
                <li>每個 map 輸入完成後，請再切換到下一個 map，確認沒有遺漏資料。</li>
                <li>兩次資料輸入皆完成後，請前往<a href="{{ route('admin.fushan.mortality.compare') }}"
                        style='color:#1d4ed8; text-decoration:none;'>資料比對</a>檢查兩次輸入是否一致。</li>
            </ul>

            <div class='flex text_outbox' style="flex-direction: column;">
                <div class='text_box_note_out'>
                    <div class='text_box text_box_note'>
                        <h2>基本操作</h2>
                        <ol>
                            <li>每次進入頁面後，先確認目前選擇的 map 是否正確，再開始輸入。</li>
                            <li><b>調查日期</b>與<b>調查人員</b>需先在表格上方儲存，才會寫入目前這個 map 的所有資料。</li>
                            <li><b>備註</b>請使用每列右側的「新增備註 / 編輯備註」按鈕填寫。</li>
                            <li><span class='line'>若有任一欄位不符驗證規則，該筆資料不會儲存。</span></li>
                            <li>每筆錯誤訊息中的多個規則，會以 <b>|</b> 分隔顯示。</li>
                        </ol>
                    </div>

                    <div class='text_box text_box_note'>
                        <h2>Status 與 Mode</h2>
                        <ol>
                            <li><b>Status</b> 只能填入 <b>OK</b>、<b>A</b>、<b>D</b>、<b>X</b>、<b>NF</b>。</li>
                            <li><b>Mode</b> 只能填 <b>S</b>、<b>B</b>、<b>U</b> 的組合，或填 <b>?</b>。</li>
                            <li><b>Mode</b> 不可重複字母。</li>
                            <li><b>Status = OK</b> 或 <b>NF</b> 時，不可填寫 <b>Mode</b>。</li>
                            <li><b>Status = A</b> 且 <b>Mode</b> 包含 <b>B</b> 時，必須填寫 <b>Living length</b>。若缺乏調查資料請填 <b>-1</b>。</li>
                            <li><b>Mode</b> 包含 <b>U</b> 時，必須填寫 <b>Leaning</b>。</li>
                        </ol>
                    </div>
                </div>

                <div class='text_box_note_out'>
                    <div class='text_box text_box_note'>
                        <h2>DBH、Branches、Illumination</h2>
                        <ol>
                            <li><b>DBH(new)</b> 必須為數字。</li>
                            <li><b>Status = OK</b> 時，必須填寫 <b>DBH(new)</b>。</li>
                            <li><b>Status = A</b> 或 <b>OK</b> 以外，不可填寫 <b>DBH(new)</b>。</li>
                            <li><b>DBH(new)</b> 應大於等於 <b>DBH(old)</b>。若小於，需在備註中加入 <b>確認縮水</b> 或 <b>DBH shrink</b> 的 option。</li>
                            <li><b>Branches</b> 只能填 0 到 100 的整數。</li>
                            <li><b>Status = A</b> 且有 <b>DBH(new)</b> 時，<b>Branches</b> 必須填 1 到 100，且不可空白。</li>
                            <li><b>Status = A</b> 且沒有 <b>DBH(new)</b> 時，<b>Branches</b> 可以留空；若填了非 0 的值，則必須補填 <b>DBH(new)</b>。</li>
                            <li><b>Illumination</b> 只能填 0 到 5。</li>
                            <li><b>Status = A</b> 或 <b>OK</b> 以外，不可填寫 <b>Illumination</b>。</li>
                            <li><b>Status = A</b> 且有 <b>DBH(new)</b> 時，<b>Illumination</b> 必須填 1 到 5。</li>
                            <li><b>Status = A</b> 且 <b>Branches = 0</b>、<b>DBH(new)</b> 空白時，若有填寫 <b>Illumination</b>，則只能填 0。</li>
                            <li><b>Status = A</b> 且 <b>DBH(new)</b> 空白時，若 <b>Illumination</b> 填了非 0 的值，則必須補填 <b>DBH(new)</b>。</li>
                            <li><b>Status = A</b> 且 <b>Living length &lt; 1.3</b> 時，即使有填寫 <b>Branches</b> 或 <b>Illumination</b>，也可不填 <b>DBH(new)</b>。</li>
                        </ol>
                    </div>

                    <div class='text_box text_box_note'>
                        <h2>其餘欄位規則</h2>
                        <ol>
                            <li><b>Living length</b> 必須為數字。</li>
                            <li><b>Leaning</b> 必須為 10 到 150。</li>
                            <li><b>Liana</b> 只能填 <b>L</b>、<b>S</b>、<b>LS</b> 或空白。</li>
                            <li><b>Fungi</b> 只能填 <b>1</b> 或空白。</li>
                            <li><b>Leaf damage</b> 只能填 <b>1</b> 或空白。</li>
                            <li><b>Wounded stem</b>、<b>Rotten</b> 只能在 <b>Status = A / OK / D</b> 時填寫。</li>
                            <li><b>Deformity</b>、<b>Leaves</b>、<b>Leaf damage</b> 只能在 <b>Status = A / OK</b> 時填寫。</li>
                            <li><b>Wounded stem</b>、<b>Deformity</b>、<b>Rotten</b> 只能填 1、2、3 或空白。</li>
                            <li><b>Leaves</b> 只能填 0 到 100 的整數。</li>
                        </ol>
                    </div>
                </div>

                <div class='text_box_note_out'>
                    <div class='text_box text_box_note'>
                        <h2>備註與特殊情況</h2>
                        <ol>
                            <li>若 <b>DBH(new) &lt; DBH(old)</b>，請在備註中加入 <b>確認縮水</b> 或 <b>DBH shrink</b>，否則系統不會儲存。</li>
                            <li>備註請用系統提供的 option；不要只輸入自由文字而未選對應 option。</li>
                            <li>若要補充說明，可在 option 後方的文字欄位加註。</li>
                            <li>若只修改備註，也需要再按一次<button class='datasavebutton' style='width:auto'>儲存</button>，才會將表格內容一併寫回。</li>
                        </ol>

                        <div style="margin-top:16px;">
                            <button id="mortality-note-comment-toggle" type="button"
                                style="padding:8px 14px; border:1px solid rgba(29,78,216,.25); border-radius:6px; background:#eff6ff; color:#1d4ed8; cursor:pointer;">
                                展開備註清單
                            </button>
                        </div>

                        <div id="mortality-note-comment-list"
                            style="display:none; margin-top:14px; padding:14px 16px; border:1px solid #dbeafe; border-radius:8px; background:#f8fbff;">
                            @foreach ($commentOptionsByCategory as $categoryKey => $items)
                                <div style="margin-bottom:12px;">
                                    <div style="font-weight:800; margin-bottom:6px;">
                                        {{ $commentCategories[$categoryKey] ?? $categoryKey }}
                                    </div>
                                    <ol style="margin-top:0;">
                                        @foreach ($items as $item)
                                            <li>{{ $item }}</li>
                                        @endforeach
                                    </ol>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class='text_box text_box_note'>
                        <h2>輸入完成後</h2>
                        <ol>
                            <li>每個 map 儲存後，請留意頁面上方是否出現錯誤訊息；有錯誤者需逐筆修正。</li>
                            <li>第一次與第二次輸入都完成後，請到<a href="{{ route('admin.fushan.mortality.compare') }}"
                                    style='color:#1d4ed8; text-decoration:none;'>資料比對</a>確認差異。</li>
                            <li>比對無誤後，再進行後續匯入與資料處理作業。</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
