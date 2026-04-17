@extends('layouts/mortality')

@php
    $sourceRows = $censuses
        ->map(function ($item) {
            return [
                'id' => $item->id,
                'census' => $item->census,
                'survey_year' => $item->survey_year,
                'has_dbh' => $item->has_dbh ? 'Yes' : 'No',
                'dbh_census' => $item->dbh_census,
                'data_batch' => $item->data_batch,
            ];
        })
        ->values();
@endphp

@section('pagejs')
    <script>
        $('.list4').addClass('now');
        $('.list4 hr').css('color', '#91A21C');

        document.addEventListener('DOMContentLoaded', function() {
            const container = document.getElementById('mortality-census-table');
            const saveButton = document.getElementById('mortality-census-save');
            const feedback = document.getElementById('mortality-census-feedback');
            const sourceRows = @json($sourceRows);

            if (!container || typeof Handsontable === 'undefined') {
                return;
            }

            const hot = new Handsontable(container, {
                data: sourceRows,
                licenseKey: 'non-commercial-and-evaluation',
                rowHeaders: true,
                colHeaders: ['id', 'census', '調查年度', '量測DBH', '每木調查census', '資料批次'],
                className: 'htCenter htMiddle',
                columns: [{
                        data: 'id',
                        type: 'numeric',
                        readOnly: true
                    },
                    {
                        data: 'census',
                        type: 'numeric'
                    },
                    {
                        data: 'survey_year',
                        type: 'numeric'
                    },
                    {
                        data: 'has_dbh',
                        type: 'dropdown',
                        source: ['Yes', 'No'],
                        strict: true,
                        allowInvalid: false
                    },
                    {
                        data: 'dbh_census',
                        type: 'numeric'
                    },
                    {
                        data: 'data_batch',
                        type: 'text'
                    }
                ],
                stretchH: 'all',
                width: '100%',
                height: 'auto',
                minSpareRows: 1,
                manualColumnResize: true,
                contextMenu: ['copy', 'cut'],
                filters: true,
                dropdownMenu: true,
                hiddenColumns: {
                    columns: [0],
                    indicators: false,
                },
            });

            async function saveCensuses() {
                if (!saveButton) return;

                saveButton.disabled = true;
                saveButton.style.background = '#94a3b8';

                const rows = hot.getSourceData().map((row) => ({
                    id: row.id ?? '',
                    census: row.census ?? '',
                    survey_year: row.survey_year ?? '',
                    has_dbh: row.has_dbh ?? 'No',
                    dbh_census: row.dbh_census ?? '',
                    data_batch: row.data_batch ?? '',
                }));

                try {
                    const response = await fetch(@json(route('admin.fushan.mortality.census.save')), {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify({
                            rows
                        }),
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        throw new Error(data.message || '儲存失敗。');
                    }

                    hot.loadData(data.rows || []);

                    if (feedback) {
                        feedback.textContent = data.message || '已儲存調查年度資料。';
                        feedback.style.display = 'block';
                        feedback.style.color = '#14532d';
                        feedback.style.borderColor = 'rgba(34,197,94,.35)';
                        feedback.style.background = 'rgba(34,197,94,.12)';
                    }
                } catch (error) {
                    if (feedback) {
                        feedback.textContent = error.message || '儲存失敗。';
                        feedback.style.display = 'block';
                        feedback.style.color = '#991b1b';
                        feedback.style.borderColor = 'rgba(220,38,38,.35)';
                        feedback.style.background = 'rgba(220,38,38,.08)';
                    }
                } finally {
                    saveButton.disabled = false;
                    saveButton.style.background = '#3f5f5b';
                }
            }

            if (saveButton) {
                saveButton.addEventListener('click', saveCensuses);
            }
        });
    </script>
@endsection

@section('rightbox')
    <div class='text_box' style="width:min(960px, 92vw); text-align:left;">
        <h2>調查年度</h2>
        <hr>

        <p style="margin:12px 0 18px; color:#475569;">
            這裡可以直接檢視並編輯 `censuses`。可新增列，修改後按儲存；空白列不會寫入。
        </p>

        <div id="mortality-census-feedback"
            style="display:none; margin:14px 0; padding:10px 12px; border:1px solid transparent; border-radius:6px;">
        </div>

        <div style="margin:0 0 14px; display:flex; justify-content:flex-end;">
            <button id="mortality-census-save" type="button"
                style="padding:10px 18px; border:0; border-radius:6px; background:#3f5f5b; color:#fff; cursor:pointer;">
                儲存調查年度
            </button>
        </div>

        <div style="overflow-x:auto; background:rgba(255,255,255,.82); padding:12px; border:1px solid rgba(0,0,0,.08); border-radius:8px;">
            <div id="mortality-census-table"></div>
        </div>
    </div>
@endsection
