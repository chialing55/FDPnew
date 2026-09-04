<?php

return [
    'profiles' => [
        // 所有每木輸入共用：儲存流程、批次驗證、交易及錯誤後保留畫面資料。
        'base' => [
            'validateAllBeforeWrite' => true,
            'useTransaction' => true,
            'retainInputOnFailure' => true,
            'validation' => [
                'requireDateAndDbh' => true,
                'statusRequiresZeroDbh' => true,
                'emptyStatusDisallowsZeroDbh' => true,
                'statusDisallowsCode' => true,
                'uppercaseCode' => true,
                'allowMultipleCodes' => true,
                'sortMultipleCodes' => true,
                'disallowCodeWhitespace' => true,
                'disallowDuplicateCodes' => true,
                'minimumDbh' => 1,
                'validateDbhShrink' => true,
                'shrinkCanUseCode' => 'C',
                'disallowShrinkWhenNotSmaller' => true,
                'rootBranchOnlyCode' => 'R',
                'changePomCode' => 'C',
                'changePomRequiresNote' => true,
                'newRecordsDisallowCodes' => ['C'],
            ],
        ],
        // 福山每木與 GEO-TREES 共用；樹蕨規則不放在這一層。
        'fushan_tree_family' => [
            'validation' => [
                'allowedCodes' => ['C', 'I', 'P', 'R'],
                'allowedStatuses' => ['0', '-1', '-2', '-3'],
            ],
        ],
    ],

    /*
     * 每個調查項目自行定義輸入表格欄位。只有列在這裡的資料庫欄位
     * 才會傳給 Handsontable，避免自動顯示內部管理欄位。
     */
    'surveys' => [
        'fushan_geo_trees' => [
            'usesProfiles' => ['base', 'fushan_tree_family'],
            'validation' => [
                'previousData' => 'census5_part',
                'treeFernHeightInDbhColumn' => false,
                'skipLockedRows' => true,
            ],
            'specialModification' => [
                'title' => '特殊修改',
                'note' => '*只需填寫需修改的資料',
                'columns' => [
                    ['data' => 'qx', 'header' => '20x', 'width' => 45, 'type' => 'numeric'],
                    ['data' => 'qy', 'header' => '20y', 'width' => 45, 'type' => 'numeric'],
                    ['data' => 'sqx', 'header' => '5x', 'width' => 45, 'type' => 'numeric'],
                    ['data' => 'sqy', 'header' => '5y', 'width' => 45, 'type' => 'numeric'],
                    ['data' => 'tag', 'header' => 'tag', 'width' => 80],
                    ['data' => 'b', 'header' => 'b', 'width' => 40, 'type' => 'numeric'],
                    ['data' => 'csp', 'header' => 'csp', 'width' => 120, 'type' => 'autocomplete', 'strict' => true, 'visibleRows' => 10, 'optionSource' => 'species'],
                    ['data' => 'pom', 'header' => '原POM', 'width' => 60, 'type' => 'numeric'],
                    ['data' => 'other', 'header' => '其他', 'width' => 120],
                    ['data' => 'stemid', 'header' => 'stemid', 'width' => 120, 'readOnly' => true, 'hidden' => true],
                ],
            ],
            'rowLocks' => [
                [
                    'type' => 'active_mortality',
                    'displayColumn' => 'dbh',
                    'display' => 'M',
                ],
                [
                    'type' => 'previous_dbh_below',
                    'column' => 'dbh',
                    'threshold' => 9.5,
                    'displayColumn' => 'dbh',
                    'display' => '--',
                ],
            ],
            // 只比對實際由調查員輸入的欄位；背景欄位來自同一份 census5_part。
            'compareColumns' => [
                'date' => '日期',
                'status' => 'status',
                'code' => 'code',
                'dbh' => 'DBH',
                'pom' => 'POM',
                'note' => 'note',
                'confirm' => '縮水',
                'alternote' => '特殊修改',
            ],
            // 只比對實際由調查員輸入的欄位；背景欄位來自同一份 census5_part。
            'compareColumns' => [
                'date' => '日期',
                'status' => 'status',
                'code' => 'code',
                'dbh' => 'DBH',
                'pom' => 'POM',
                'note' => 'note',
                'confirm' => '縮水',
                'alternote' => '特殊修改',
            ],
            'columns' => [
                ['data' => 'date', 'header' => 'Date', 'width' => 120, 'type' => 'date', 'dateFormat' => 'YYYY-MM-DD', 'allowInvalid' => false, 'emptyValues' => ['0000-00-00']],
                ['data' => 'qx', 'header' => '20x', 'width' => 25, 'readOnly' => true],
                ['data' => 'qy', 'header' => '20y', 'width' => 25, 'readOnly' => true],
                ['data' => 'sqx', 'header' => '5x', 'width' => 25, 'readOnly' => true],
                ['data' => 'sqy', 'header' => '5y', 'width' => 25, 'readOnly' => true],
                ['data' => 'tag', 'header' => 'tag', 'width' => 80, 'readOnly' => true],
                ['data' => 'branch', 'header' => 'b', 'width' => 40, 'readOnly' => true],
                ['data' => 'csp', 'header' => 'csp', 'width' => 120, 'readOnly' => true],
                ['data' => 'status', 'header' => 'status', 'width' => 50, 'type' => 'dropdown', 'source' => ['', '0', '-1', '-2', '-3'], 'allowInvalid' => false, 'emptyValues' => ['-9']],
                ['data' => 'code', 'header' => 'code', 'width' => 50],
                ['data' => 'dbh', 'header' => 'dbh', 'width' => 60, 'type' => 'numeric', 'allowInvalid' => false, 'emptyWhenZero' => true],
                ['data' => 'pom', 'header' => 'POM', 'width' => 50, 'type' => 'numeric', 'allowInvalid' => false],
                ['data' => 'note', 'header' => 'note', 'width' => 160],
                ['data' => 'confirm', 'header' => '縮水', 'width' => 50, 'type' => 'checkbox', 'checkedTemplate' => '1', 'uncheckedTemplate' => ''],
                ['data' => 'stemid', 'header' => '', 'width' => 50, 'readOnly' => true, 'renderer' => 'special-modification'],
            ],
        ],
    ],
];
