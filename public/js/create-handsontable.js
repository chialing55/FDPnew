

//深拷貝，讓realemptytable不會跟著變動
function deepCopy(obj) {
  if (obj === null || typeof obj !== 'object') {
    return obj;
  }

  if (Array.isArray(obj)) {
    return obj.map(deepCopy);
  }

  const copy = {};
  for (const key in obj) {
    if (obj.hasOwnProperty(key)) {
      copy[key] = deepCopy(obj[key]);
    }
  }

  return copy;
}

function makeAjaxRequest(url, requestData, requstType, successCallback, errorCallback) {
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
    },
  });
  $.ajax({
    url: url,
    data: requestData,
    type: requstType,
    success: function (res) {
      if (res.result === 'ok') {
        console.log('Data saved');
        console.log(res);        
        successCallback(res);
      } else {
        console.log('Save error');
      }
    },
    error: function (xhr, status, error) {
      console.log('Save error. '+url);
      if (errorCallback) {
        errorCallback({ error: 'Save error', xhr: xhr, status: status, error: error });
      }
    },

  });
}



function createHandsontable(container, columns, sourceData, saveButtonName, saveUrl, tableType, colWidths, hiddenColumns, colHeaders, thispage) {
  var cellChanges = [];
  var parent = container.parent();


  container.handsontable({
    data: sourceData,
    colHeaders: true,
    rowHeaders: true,
    rowHeaderWidth: 25,
    rowHeights: 35,
    colWidths: colWidths,
    colHeaders: colHeaders,
    licenseKey: 'non-commercial-and-evaluation',
    columns: columns,
    currentRowClassName: 'currentRow',
    manualColumnResize: true,
    hiddenColumns: hiddenColumns,
    cells: function (row, col, prop) {
      return cellfunction(tableType, container, row, col, prop);
    },

    afterChange: function (changes, source) {
      if (tableType!='recruit' && tableType!='addcov' && tableType!='alternote' && tableType!='addseedsdata'){
        if (!changes) {
            return;
        }
            $.each(changes, function (index, element) {
                var change = element;
                var rowIndex = change[0];
                var columnIndex = change[1];
                var oldValue = change[2];
                var newValue = change[3];
                col=container.handsontable('propToCol', columnIndex);
                // console.log(col);
                var td = container.handsontable('getCell', rowIndex, col);
                var cellChange = {
                    'rowIndex': rowIndex,
                    'columnIndex': col, 
                    'td': td
                };
                // console.log(td);
                if(oldValue != newValue){
                    cellChanges.push(cellChange);
                    td.style.color = 'forestgreen';
                }
            });
      }
    },
    afterRender: function () {
      if (tableType=='recruit' && tableType!='addcov' && tableType!='alternote' && tableType!='addseedsdata'){
            // var instance = container.handsontable('getInstance');
            $.each(cellChanges, function (index, element) {
                var cellChange = element;
                var rowIndex = cellChange['rowIndex'];
                var columnIndex = cellChange['columnIndex'];
                // var grilla = $('#grilla');
                var td=cellChange['td'];
                // var td = container.handsontable('getCell', rowIndex, columnIndex);
                td.style.color = 'forestgreen'; 
                // cell.style.background = backgroundColor;
                // console.log(td);
            });
      }
    },
  });
  if (tableType === 'recruit' || tableType === 'addcov') {
    container.handsontable('updateSettings', {
      contextMenu: ['row_above', 'row_below', 'remove_row'],
    });
  }

  if (tableType === 'roll') {
    container.handsontable('updateSettings', {
      minSpareRows: 5,
    });
  }

  var noteProperty = `${tableType}savenote`;
  var handsontable = container.data('handsontable');
  // var noteClass=`${tableType}savenote`;
  
// console.log(tableType);
  container.parent().find(`button[name=${saveButtonName}]`).click(function () {
    $('.savenote').html('');

    var ajaxData={
          data: handsontable.getSourceData(),
          entry: entry,
          user: user,
          plotType: plotType,
          thispage: thispage,
        };
    var ajaxType='post';
  // ceartAjax
    makeAjaxRequest(
      saveUrl, ajaxData, ajaxType,
      function(res) {
        handleSuccessAllTable(res, tableType, handsontable);
      },
      function () {}
    );

  });
//新增資料表更新
  parent.find('button[name=clearrecruittable]').click(function () {
    $('.recruitsavenote').html('');
    emptytable2=deepCopy(realemptytable);
    handsontable.updateData(emptytable2);
    // console.log('ww');
  });
//新增地被資料表更新
  parent.find('button[name=clearaddcovtable]').click(function () {
    $('.addcovsavenote').html('');
    emptytable2=deepCopy(realemptytable);
    handsontable.updateData(emptytable2);
  });

  parent.find('button[name=deletecov]').click(function () {
    const id=$(this).attr('deleteid');
    const entry2=$(this).attr('entry');
    deletecov(id, entry2);
  });


  return handsontable;
}


function handleAlternote(stemid, entry, thispage, saveUrl) {
  // console.log(stemid);
  $('.altersavenote').html('');

    var posX = event.pageX;
    var posY = event.pageY;
  console.log(posX+", "+posY);

  $('.alternotetalbeouter').css('top', posY);
  $('.alternotetalbeouter').css('left', posX-650);
  
// $(".deletealternotebutton").removeAttr("stemid thispage");

  $('.alternotetalbeouter').show();
  $('.alterstemid').html(stemid);
  $('.altertag').html(stemid);

    var ajaxData={};
    var ajaxType='get';

    function handleSuccess(res) {
          if (res.datasavenote !=''){
            $('.datasavenote').html(res.datasavenote);
          }

          alternotetable(res.alterdata, stemid, entry, thispage);
          if (res.havedata=='yes'){
            $('.deletealternotebutton').show();
            $('.deletealternotebutton').attr({'stemid': stemid,  'thispage': thispage});
            
          } else {
            $('.deletealternotebutton').hide();
          }
    }
    makeAjaxRequest(
      saveUrl, ajaxData, ajaxType,
      handleSuccess,
      function () {}
    );

  // alternotetable(stemid, entry);
}

function deletealternoteButtonClick(button){
  let stemid = $(button).attr('stemid');
  if (typeof stemid === 'undefined') {
    stemid = $(button).attr('tag');
  }
  const thispage = $(button).attr('thispage');
  deletealternote(stemid, plotType, thispage);  
}

function handleDeleteAlternote(stemid, plotType, saveUrl){
      if(confirm('確定刪除 '+stemid+' 特殊修改??')) 
    {
      $('.altersavenote').html('');

      var ajaxData={};
      var ajaxType='get';

      function handleSuccess(res) {
          if (res.datasavenote !=''){
            $('.altersavenote').html(res.datasavenote);
          }

          if (plotType=='ss10m' || plotType=='ss1ha'){
            ssdatatableupdate(res.data, res.thispage, ppsall);
          } else if (plotType=='fstree'){
            fstreetableupdate(res.data, res.thispage, ppsall);
          } else if (plotType=='fsseedling'){
            fsseedlingtableupdate(res.data, res.maxid, res.thispage);
          }

          // ssdatatableupdate(res.data, res.thispage, ppsall);
          var container = $("#alternotetable");
          var handsontable = container.data('handsontable');
          
          handsontable.updateData(res.realterdata);
          $('.deletealternotebutton').hide();
      }
      makeAjaxRequest(
        saveUrl, ajaxData, ajaxType,
        handleSuccess,
        function () {}
      );
    }
}

function handleDeleteid(stemid, saveUrl){
  // console.log(entry);
  $('.recruitsavenote').html('');
  if(confirm('確定刪除 '+stemid+' 新增樹資料??')) 
  {
    $('.datasavenote').html('');
    
    var ajaxData={};
    var ajaxType='get';

    function handleSuccess(res) {
          if (res.datasavenote !=''){
            $('.datasavenote').html(res.datasavenote);
          }
          if (plotType=='ss10m' || plotType=='ss1ha'){
            ssdatatableupdate(res.recruit, res.thispage, ppsall);
          } else if (plotType=='fstree'){
            fstreetableupdate(res.recruit, res.thispage, ppsall);
          } 
    }
    makeAjaxRequest(
      saveUrl, ajaxData, ajaxType,
      handleSuccess,
      function () {}
    );
  }
}

function processDataTable(data, thispage, pps, site, plotType) {
  // 分頁
  var totalpage = Math.ceil(data.length / pps);

  $('.prev').addClass(`prev${site}`);
  $('.next').addClass(`next${site}`);

  if (totalpage > 1) {
    datapage = pages(data, thispage, totalpage, pps, plotType, site);
    var data2 = datapage[1];
  } else {
    var data2 = data;
  }

  for (let i = 0; i < data2.length; i++) {
    if (data2[i]['date'] === '0000-00-00') {
      data2[i]['date'] = '';
    }
  }
  // 返回处理后的数据
  return data2;
}


function pages(data, thispage, totalpage, pps, plotType, site) {

  let start;
  let end;
  let data2;

  start = pps * (thispage - 1);
  end = start + pps;
  data2 = data.slice(start, end);

  $('.pages').css('display', 'flex');
  $('.pagenote').html(`第 ${thispage} ／ ${totalpage} 頁`);
  $('.prev').attr('thispage', thispage);
  $('.next').attr('thispage', thispage);

  if (totalpage > 1) {
    if (thispage === 1) {
      $('.prev').hide();
      $('.next').show();
    } else if (thispage === totalpage) {
      $('.prev').show();
      $('.next').hide();
    } else {
      $('.prev').show();
      $('.next').show();
    }
  } else {
    $('.pages').hide();
  }

  $(`.prev${site}`).off('click').on('click', function () {
    handlePagination('prev', site, plotType, data, pps);
  });

  $(`.next${site}`).off('click').on('click', function () {
    handlePagination('next', site, plotType, data, pps);
  });


  $('.showall').off('click').on('click', function () {
    let ppsall;

    if (data.length > 40) {
      ppsall = 40;
    } else {
      ppsall = data.length;
      $('.pages').hide();
    }

    if (plotType === 'ss10m' || plotType==='ss1ha') {
      ssdatatableupdate(data, 1, ppsall);
    } else if (plotType == 'fstree'){
      fstreetableupdate(data, 1, ppsall);
    } else if (plotType === 'fsseedling') {
      fsseedlingtableupdate(data, 1, ppsall, maxid);
    }
  });
  
  datapage=[data, data2, thispage];

  return datapage;
  
}

function handlePagination(action, site, plotType, data, pps) {
  thispage = $(`.${action}${site}`).attr('thispage');
  const gopage = (action === 'prev') ? parseInt(thispage) - 1 : parseInt(thispage) + 1;

  if (plotType === 'ss10m' || plotType === 'ss1ha')  {
    ssdatatableupdate(data, gopage, pps);
  } else if (plotType === 'fstree') {
    fstreetableupdate(data, gopage, pps);
  } else if (plotType === 'fsseedling') {
    fsseedlingtableupdate(data, gopage, pps, maxid);
  } else if (plotType === 'fsseeds') {
    fsseedstableupdate(data, gopage, 29);
  }
}


function dataTableUpdate(data, thispage, pps, plotType, tableType, site){
  $('.datasavenote').html('');
  var totalpage=Math.ceil(data.length/pps);
  var container = $(`#datatable${site}`);
  var handsontable = container.data('handsontable');
  // console.log(data);
  
  $('.totalnum').html(`共有 ${data.length} 筆資料。`);

  var data3 = (totalpage > 1) ? pages(data, thispage, totalpage, pps, plotType, site)[1] : data;

    for (let i = 0; i < data3.length; i++) {
      if (data3[i]['date'] === '0000-00-00') {
          data3[i]['date'] = ''; // 使用单等号进行赋值
      }
  }

// console.log(data3);
  handsontable.updateData(data3, thispage);
  handsontable.updateSettings({
    cells: function (row, col, prop) {
  
      return cellfunction(tableType, container, row, col, prop);
    }
    });
}


  const numericValidator5 = (value, callback) => {
    if (value === '0') {
      callback(true);
    } else {
      var numericValue = parseFloat(value);
      callback(!isNaN(numericValue) && numericValue >= 0 && numericValue <= 5);
    }
  };

  const numericValidator10 = (value, callback) => {
    if (value === '0') {
      callback(true);
    } else {
      var numericValue = parseFloat(value);
      callback(!isNaN(numericValue) && numericValue >= 0 && numericValue <= 10);
    }
  };


  const numericValidator20 = (value, callback) => {
    if (value === '0') {
      callback(true);
    } else {
      var numericValue = parseFloat(value);
      callback(!isNaN(numericValue) && numericValue >= 0 && numericValue <= 20);
    }
  };

  const numericValidator100 = (value, callback) => {
    if (value === '0') {
      callback(true);
    } else {
      var numericValue = parseFloat(value);
      callback(!isNaN(numericValue) && numericValue >= 0 && numericValue <= 100);
    }
  };

  const qqValidator = (value, callback) => {
    if ([1, 2, ''].includes(value)) {   //允許1234和空格
      callback(true);
    } else {
      callback(false);
    }
  };

  const qqValidator4 = (value, callback) => {
    if ([1, 2, 3, 4, ''].includes(value)) {   //允許1234和空格
      callback(true);
    } else {
      callback(false);
    }
  };

  const layerValidator = (value, callback) => {
    if (['u', 'o', ''].includes(value)) {   //允許1234和空格
      callback(true);
    } else {
      callback(false);
    }
  };