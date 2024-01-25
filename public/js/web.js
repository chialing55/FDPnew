$(document).ready(function() { 


//名錄篩選
    $("#spTable").tablesorter({
        widgets: ['filter'],
        widgetOptions : {
            filter_functions: {
                // 自定義篩選函數，這個例子是針對帶有 Font Awesome 圖標的列
                3: customFilterFunction,
                4: customFilterFunction,
                5: customFilterFunction
                
            }
        },
        textExtraction: {
            3: function(node, table, cellIndex) {
                // 提取 data-value 屬性的值
                return $(node).attr('data-value') || $(node).text();
            },
            4: function(node, table, cellIndex) {
                // 提取 data-value 屬性的值
                return $(node).attr('data-value') || $(node).text();
            },
            5: function(node, table, cellIndex) {
                // 提取 data-value 屬性的值
                return $(node).attr('data-value') || $(node).text();
            }
        },
    });

// 共用的自定義篩選函數
function customFilterFunction(e, n, f, i, $r, c, data) {
    // 檢查 filter input 的值是否為 "1" 或 "0"
    
    return $r.find('td:eq(' + i + ')').data('value') == f;
    
    // 如果 filter input 的值不是 "1" 或 "0"，使用默認的篩選邏輯
    return $.tablesorter.filterFormatter.ui(e, n, f, i, $r, c, data);
}

    $('input[data-column=0]').attr('placeholder', 'keyword');
    $('input[data-column=1]').css('width', '200px').attr('placeholder', 'keyword');
    $('input[data-column=2]').css('width', '100px').attr('placeholder', 'keyword');
    $('input[data-column="3"], input[data-column="4"], input[data-column="5"]')
    	.css('width', '40px').attr('placeholder', '1/0');


//照片顯示
Fancybox.bind('[data-fancybox="gallery"]', {
  // Your custom options
});

})