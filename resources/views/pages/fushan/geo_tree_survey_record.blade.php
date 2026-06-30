<!DOCTYPE html>
<html lang="zh-TW">

<head>
    <meta charset="UTF-8">
    <title>{{ $title }}－qx {{ $selectedQx }}</title>

    <style>
        @page {
            margin: 22px 30px 22px 30px;
        }

        html,
        table,
        td,
        .chinese {
            font-family: msjh;
            font-size: 12px;
            font-weight: normal;
        }

        body {
            margin: 0;
            font-family: msjh;
            font-size: 12px;
            font-weight: normal;
        }

        .record_pdf {
            position: relative;
            height: 285mm;
            page-break-after: always;
            box-sizing: border-box;
            padding: 0;
            margin: 0;
        }

        .record_pdf:last-child {
            page-break-after: auto;
        }

        .page-header {
            display: table;
            width: 100%;
            margin-bottom: 8px;
            line-height: 14px;
            font-family: msjh;
            font-size: 12px;
        }

        .page-header>div {
            display: table-cell;
            vertical-align: top;
        }

        .header-title {
            width: 34%;
        }

        .header-count {
            font-size: 10px;
            white-space: nowrap;
        }

        .header-input {
            width: 42%;
        }

        .header-date {
            width: 24%;
            text-align: right;
            vertical-align: bottom;
        }

        .page-footer {
            display: table;
            position: absolute;
            right: 0;
            bottom: 25;
            left: 0;
            width: 100%;
            font-family: msjh;
            font-size: 12px;
        }

        .page-footer-left {
            display: table-cell;
            width: 75%;
            vertical-align: bottom;
        }

        .page-footer-right {
            display: table-cell;
            width: 25%;
            text-align: right;
            vertical-align: bottom;
            font-size: 2em;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead td {
            background: #c9c9c9;
            font-size: 10px;
            padding: 2px 4px;
            height: 18px;
            border-bottom: none;
        }

        td {
            vertical-align: middle;
            text-align: left;
            text-overflow: ellipsis;
        }

        .data-row td {
            height: 19px;
            font-size: 12px;
            padding: 3px 4px;
            border-bottom: 1px solid #dedede;
        }

        .branch-cell {
            color: #444;
        }

        .write-cell {
            border-bottom: 1px solid #000 !important;
            border-left: 1px solid #dedede;
        }

        .dbh-mark-cell {
            text-align: center;
        }

        .disabled-row td {
            background: #e6e6e6;
            color: #666666;
        }

        .disabled-row .subquadrat-cell {
            background: #ffffff;
            color: #000000;
        }

        .note-cell {
            font-size: 10px !important;
            text-align: right;
            border-left: 1px solid #dedede;
            border-right: 1px solid #dedede;
        }

        .condition-cell {
            font-size: 10px !important;
            text-align: right;
            border-left: 1px solid #dedede;
        }

        .subquadrat-cell {
            vertical-align: top;
            border-bottom: 2px solid #000 !important;
        }

        tr.sq-end td {
            border-bottom: 2px solid #000 !important;
        }
    </style>
</head>

<body>

    @php
        /*
         * 每頁最多列數。
         * footer 固定在頁面底部，因此列數維持保守設定，
         * 避免資料表與 footer 重疊。
         */
        $maxRowsPerPage = 33;

        $pages = [];

        foreach ($quadrats as $quadrat) {
            $quadratTotalCount = 0;
            $quadratSurveyCount = 0;

            foreach ($quadrat['subquadrats'] as $quadratRows) {
                foreach ($quadratRows as $quadratRow) {
                    $quadratTotalCount++;

                    if (($quadratRow['survey_dbh_mark'] ?? '') === '') {
                        $quadratSurveyCount++;
                    }
                }
            }

            $currentPage = [
                'qx' => $quadrat['qx'],
                'qy' => $quadrat['qy'],
                'total_count' => $quadratTotalCount,
                'survey_count' => $quadratSurveyCount,
                'groups' => [],
                'row_count' => 0,
            ];

            foreach ($subquadratOrder as $subquadrat) {
                $sqx = $subquadrat[0];
                $sqy = $subquadrat[1];

                $rows = $quadrat['subquadrats'][$subquadrat] ?? [];
                $rowCount = count($rows);

                if ($rowCount === 0) {
                    continue;
                }

                if ($currentPage['row_count'] > 0 && $currentPage['row_count'] + $rowCount > $maxRowsPerPage) {
                    $pages[] = $currentPage;

                    $currentPage = [
                        'qx' => $quadrat['qx'],
                        'qy' => $quadrat['qy'],
                        'total_count' => $quadratTotalCount,
                        'survey_count' => $quadratSurveyCount,
                        'groups' => [],
                        'row_count' => 0,
                    ];
                }

                $currentPage['groups'][] = [
                    'sqx' => $sqx,
                    'sqy' => $sqy,
                    'rows' => $rows,
                    'row_count' => $rowCount,
                ];

                $currentPage['row_count'] += $rowCount;
            }

            if ($currentPage['row_count'] > 0) {
                $pages[] = $currentPage;
            }
        }

        $quadratPageTotals = [];

        foreach ($pages as $page) {
            $quadratKey = $page['qx'] . ':' . $page['qy'];
            $quadratPageTotals[$quadratKey] = ($quadratPageTotals[$quadratKey] ?? 0) + 1;
        }

        $quadratPageNumbers = [];

        foreach ($pages as &$page) {
            $quadratKey = $page['qx'] . ':' . $page['qy'];
            $quadratPageNumbers[$quadratKey] = ($quadratPageNumbers[$quadratKey] ?? 0) + 1;
            $page['quadrat_page'] = $quadratPageNumbers[$quadratKey];
            $page['quadrat_total_pages'] = $quadratPageTotals[$quadratKey];
        }

        unset($page);
    @endphp

    @foreach ($pages as $pageIndex => $page)
        <div class="record_pdf">

            <div class="page-header">
                <div class="header-title">
                    {{ $title }}<br>
                    <span class="header-count">
                        共 {{ $page['survey_count'] }}／{{ $page['total_count'] }} 筆
                    </span>
                </div>

                <div class="header-input">
                    資料輸入1________________輸入日期1________________<br>
                    資料輸入2________________輸入日期2________________
                </div>

                <div class="header-date">
                    {{ $page['quadrat_page'] }}／{{ $page['quadrat_total_pages'] }}<br>
                    調查日期 _______月_______日
                </div>
            </div>

            <table border="0" cellpadding="0" cellspacing="0" class="chinese">
                <thead>
                    <tr>
                        <td width="5%">plot</td>
                        <td width="8%">tag</td>
                        <td width="4%">b</td>
                        <td width="15%">csp</td>
                        <td width="11%">前次 dbh</td>
                        <td width="7%">status</td>
                        <td width="7%">code</td>
                        <td width="10%">dbh</td>
                        <td>note</td>
                        <td width="6%">狀況</td>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($page['groups'] as $groupRows)
                        @php
                            $sqx = $groupRows['sqx'];
                            $sqy = $groupRows['sqy'];
                            $rows = $groupRows['rows'];
                            $rowCount = $groupRows['row_count'];
                        @endphp

                        @foreach ($rows as $index => $row)
                            @php
                                $isBranch = (int) $row['branch'] !== 0;
                                $isSqEnd = $index === $rowCount - 1;
                                $cellClass = $isBranch ? 'branch-cell' : '';
                            @endphp

                            <tr
                                class="data-row {{ $isSqEnd ? 'sq-end' : '' }} {{ $row['survey_dbh_mark'] !== '' ? 'disabled-row' : '' }}">
                                @if ($index === 0)
                                    <td rowspan="{{ $rowCount }}" width="5%" class="subquadrat-cell">
                                        ({{ $sqx }}, {{ $sqy }})
                                    </td>
                                @endif

                                <td width="8%" class="{{ $cellClass }}">
                                    {{ $row['tag'] }}
                                </td>

                                <td width="4%" class="{{ $cellClass }}">
                                    {{ $row['branch'] }}
                                </td>

                                <td width="15%" class="{{ $cellClass }}">
                                    {{ $row['csp'] }}
                                </td>

                                <td width="11%" class="{{ $cellClass }}">
                                    {{ $row['dbh'] }}
                                </td>

                                <td width="7%" class="write-cell {{ $cellClass }}">
                                    {{ $row['status'] }}
                                </td>

                                <td width="7%" class="write-cell {{ $cellClass }}">
                                    {{ $row['code'] }}
                                </td>

                                <td width="10%" class="write-cell dbh-mark-cell">
                                    {{ $row['survey_dbh_mark'] }}
                                </td>

                                <td class="note-cell {{ $cellClass }}">
                                    {{ $row['note'] }}
                                </td>

                                <td width="6%" class="write-cell condition-cell"></td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>

            <div class="page-footer">
                <div class="page-footer-left">
                    調查者__________________________________________記錄者_____________________檢查者_____________________
                </div>

                <div class="page-footer-right">
                    ({{ $page['qx'] }}, {{ $page['qy'] }})
                </div>
            </div>

        </div>
    @endforeach

</body>

</html>
