<?php

return [
    'columns' => [
        ['data' => 'batch_item_id', 'header' => '', 'hidden' => true, 'readOnly' => true],
        ['data' => 'date', 'header' => 'Date', 'width' => 145, 'type' => 'date', 'dateFormat' => 'YYYY-MM-DD', 'correctFormat' => true],
        ['data' => 'plot', 'header' => 'Treatment', 'width' => 105, 'readOnly' => true],
        ['data' => 'individual_no', 'header' => 'No.', 'width' => 105, 'readOnly' => true],
        ['data' => 'species', 'header' => 'Species', 'width' => 82, 'readOnly' => true],
        ['data' => 'length', 'header' => '長度', 'width' => 72, 'type' => 'numeric'],
        ['data' => 'leaf_count', 'header' => '葉片數', 'width' => 72, 'type' => 'numeric'],
        ['data' => 'status', 'header' => 'Status', 'width' => 72, 'type' => 'dropdown', 'source' => ['A', 'D', 'G'], 'strict' => true, 'allowInvalid' => false],
        ['data' => 'note', 'header' => '備註', 'width' => 240, 'type' => 'text'],
    ],
];
