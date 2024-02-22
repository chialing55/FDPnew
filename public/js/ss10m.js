
var realemptytable;
var realemptytable2;
var plotType='ss10m';

window.addEventListener('data', event => {

  covs=event.detail.covs;
  data=event.detail.record;
  emptytable=event.detail.emptytable;
  emptytable2=event.detail.emptytable2;  //cov
  realemptytable = deepCopy(emptytable);
  realemptytable2 = deepCopy(emptytable2);  //cov
  envi=event.detail.envi;
  csplist=event.detail.csplist;
  covcsplist=event.detail.covcsplist;

  // console.log(emptytable);

    $(".save2").unbind();
    $('.finishnote').html();
    sscovtable(covs, data, covcsplist);
    ssaddcovtable(covs, data, emptytable2, covcsplist); 
    if (covs.length!=0){
      
      $('.covtable').show();
      $('.nocovdata').hide();
 
    } else {
      // $('.addcovtableout').show();
      // ssaddcovtable(covs, data, emptytable2);
      $('.covtable').hide();
      $('.nocovdata').show();
    }
    
    //一開始,thispage=1
    ssenvitable(envi, data[0].sqx, data[0].sqy);

    if (data[0].tag!='無'){
    ssdatatable(data, 1, 20);

    ssrecruittable(data, emptytable, csplist);
  }
});

  const aspectValidator = (value, callback) => {
    if (value === '0') {
      callback(true);
    } else {
      var numericValue = parseFloat(value);
      callback(!isNaN(numericValue) && numericValue >= 0 && numericValue <= 360);
    }
  };

  const slopeValidator = (value, callback) => {
    if (value === '0') {
      callback(true);
    } else {
      var numericValue = parseFloat(value);
      callback(!isNaN(numericValue) && numericValue >= 0 && numericValue <= 90);
    }
  };

function cellfunction(tableType, container, row, col, prop){
      if (tableType=='data'){
          var cellProperties = {};
          if (container.handsontable('getData')[row][7]=='-9'){
            cellProperties.readOnly = false; 
            if (col==1 || col == 7 || col == 14){
              cellProperties.readOnly = true; 
            }
          }
//note字變小
          if (col == 12 || col==14){
            cellProperties.className = 'fs08'; 
          }
         return cellProperties;
      }
}

function ssenvitable(envi, sqx, sqy){

  var container = $(`#envitable${envi[0].plot}${sqx}${sqy}`);
  var saveButtonName=`envisave${envi[0].plot}${sqx}${sqy}`;
  var tabletype='envi';

  var columns = [
      {data: "plot", readOnly:true},
      {data: "slope1", type: 'numeric', allowInvalid: false, validator: slopeValidator, placeholder: "(1,1)"},
      {data: "slope2", type: 'numeric', allowInvalid: false, validator: slopeValidator, placeholder: "(1,2)"},
      {data: "slope3", type: 'numeric', allowInvalid: false, validator: slopeValidator, placeholder: "(2,2)"},
      {data: "slope4", type: 'numeric', allowInvalid: false, validator: slopeValidator, placeholder: "(2,1)"},
      {data: "aspect1", type: 'numeric', allowInvalid: false, validator: aspectValidator, placeholder: "(1,1)"},
      {data: "aspect2", type: 'numeric', allowInvalid: false, validator: aspectValidator, placeholder: "(1,2)"},
      {data: "aspect3", type: 'numeric', allowInvalid: false, validator: aspectValidator, placeholder: "(2,2)"},
      {data: "aspect4", type: 'numeric', allowInvalid: false, validator: aspectValidator, placeholder: "(2,1)"},
      {data: "terrain", type: 'dropdown', source: ['上坡', '中坡', '下坡', '稜線'], allowInvalid: false},
      {data: "rocky", type: 'numeric', allowInvalid: false, validator: numericValidator100},
      {data: "exposed_surface", type: 'numeric', allowInvalid: false, validator: numericValidator100},
      {data: "litter_cover", type: 'numeric', allowInvalid: false, validator: numericValidator100},
      {data: "fallen_tree", type: 'numeric', allowInvalid: false, validator: numericValidator100},
      {data: "T1", type: 'numeric', allowInvalid: false, validator: numericValidator20},
      {data: "T2", type: 'numeric', allowInvalid: false, validator: numericValidator20},
      {data: "S", type: 'numeric', allowInvalid: false, validator: numericValidator20},
      {data: "H", type: 'numeric', allowInvalid: false, validator: numericValidator20}

    ];

  var colWidths=[80, 50, 50,50, 50,50, 50,50, 50, 80, 50, 50, 50, 50, 50, 50, 50, 50];
  var colHeaders=["Plot", "坡度1","坡度2","坡度3","坡度4", "坡向1","坡向2","坡向3","坡向4", "地形", "岩石地<br>比例", "地表<br>裸露度", "凋落物<br>覆蓋度", "倒木<br>覆蓋度", 'T1', 'T2', 'S', 'H'];

  var hiddenColumns =[];

  return createHandsontable(container, columns, envi, saveButtonName, "/ssPlotsaveenvi", tabletype, colWidths, hiddenColumns, colHeaders, 1 );

}

function ssdatatable(data, thispage, pps){

  $('.envisavenote').html('');
  $('.finishnote').html();
  $('.totalnum').html(`共有 ${data.length} 筆資料。`);
  var site=`${data[0].plot}${data[0].sqx}${data[0].sqy}`;
  var container = $(`#datatable${site}`);
  $(`button[name=datasave${site}]`).off();
  var saveButtonName=`datasave${site}`;
  var tabletype='data';
  ppsall=pps;
  var data2 = processDataTable(data, thispage, pps, site, plotType);

  var columns = [
      {data: "date", dateFormat: 'YYYY-MM-DD', type: 'date', allowInvalid: false},
      {data: "plot", readOnly: true},
      {data: "sqx", readOnly: true},
      {data: "sqy", readOnly: true},
      {data: "tag", readOnly: true},
      {data: "branch", readOnly: true},
      {data: "csp", readOnly: true, type: 'autocomplete', source: csplist, strict: true, visibleRows: 10, allowInvalid: false,},
      {data: "status", type: 'dropdown', source: ['', '0', '-1', '-2', '-3', '-4'], allowInvalid: false},
      {data: "code"},
      {data: "dbh", type: 'numeric', allowInvalid: false},
      {data: "ill", type: 'numeric', allowInvalid: false, validator: numericValidator5},
      {data: "leave", type: 'numeric', allowInvalid: false, validator: numericValidator100},
      {data: "note"},
      {data: "confirm", type: 'checkbox', checkedTemplate: '1', uncheckedTemplate: ''},
      {data: "alternotetable", renderer: "html", readOnly: true},
      {data: "update_id"}

    ];

  var colWidths=[120, 80,25,25,80, 40, 120,50,50,60,50,50,160,50, 160];
  var colHeaders=["Date","plot","5x","5y", "tag", "b", "csp",'status', "code","dbh","ill","leave","note","縮水",""];

  var hiddenColumns ={
      columns: [15],
    };

  return createHandsontable(container, columns, data2, saveButtonName, "/ssPlotsavedata", tabletype, colWidths, hiddenColumns, colHeaders, thispage );
}

function ssrecruittable(data, emptytable, csplist){
// console.log(csplist);
// console.log(emptytable);
   var site=`${data[0].plot}${data[0].sqx}${data[0].sqy}`;
   var thispage=Math.ceil(data.length/20);  //指定新增後前往最後一頁
 
$(`button[name=recruitsave${site}]`).off();
  var container = $(`#recruittable${site}`);
  var saveButtonName=`recruitsave${site}`;
  var tabletype='recruit';

  var columns = [
      {data: "date", dateFormat: 'YYYY-MM-DD', type: 'date', allowInvalid: false},
      {data: "plot", readOnly: true},
      {data: "sqx", type: 'numeric', allowInvalid: false, validator: qqValidator},
      {data: "sqy", type: 'numeric', allowInvalid: false, validator: qqValidator},
      {data: "tag"},
      {data: "branch", type: 'numeric', allowInvalid: false},
      {data: "csp", type: 'autocomplete', source: csplist, strict: true, visibleRows: 10, allowInvalid: false},
      {data: "code"},
      {data: "dbh", type: 'numeric' , allowInvalid: false},
      {data: "ill", type: 'numeric', allowInvalid: false, validator: numericValidator5},
      {data: "leave", type: 'numeric', allowInvalid: false, validator: numericValidator100 },
      {data: "note"},
      {data: "tofix", type: 'checkbox', checkedTemplate: '1', uncheckedTemplate: ''}

    ];

  var colWidths=[120, 80,25,25,80, 40, 120,50,60,50,50,160,50];
  var colHeaders=["Date","plot","5x","5y", "tag", "b", "csp", "code","dbh","ill","leave","note","漏資料"];

  var hiddenColumns =[];
  return createHandsontable(container, columns, emptytable, saveButtonName, "/ssPlotsaverecruit", tabletype, colWidths, hiddenColumns, colHeaders, thispage );

}


function alternotetable(alterdata, stemid, entry, thispage){
  $("button[name=alternotesave]").off();
  var container = $("#alternotetable");
 
  if (container.handsontable('getInstance')) {
    container.handsontable('destroy');
  }
  $('.deletealternotebutton').attr({'stemid': stemid,  'thispage': thispage});

  var saveButtonName='alternotesave';
  var tableType='alternote';

  var plotList=['B-F-01', 'B-F-04', 'B-F-06', 'B-F-13', 'B-F-14', 'B-F-19', 'G-F-01', 'G-F-02', 'G-F-03', 'G-F-06', 'Q-F-03', 'S-F-01', 'S-F-02', 'S-F-04', 'S-F-06', 'S-F-07', 'S-F-09', 'S-F-11', 'S-F-14', 'S-F-15', 'S-F-16', 'S-F-17', 'S-F-21', 'S-F-38'];

  var columns = [
      {data: "plot", type: 'autocomplete', source: plotList, strict: true, visibleRows: 10, allowInvalid: false,},
      {data: "sqx", type: 'numeric', allowInvalid: false},
      {data: "sqy", type: 'numeric', allowInvalid: false},
      {data: "tag"},
      {data: "b", type: 'numeric', allowInvalid: false},
      {data: "csp", type: 'autocomplete', source: csplist, strict: true, visibleRows: 10, allowInvalid: false,},
      {data: "dbh", type: 'numeric', allowInvalid: false},
      {data: "other"},
      {data: "stemid"},

    ];

  var colWidths=[80,25,25,80, 40, 120,70, 100];
  var colHeaders=["plot","5x","5y", "tag", "b", "csp", "dbh(<1)", "other","stemid"];

  var hiddenColumns ={
      columns: [8],
    };
  return createHandsontable(container, columns, alterdata, saveButtonName, "/ssPlotsavealternote", tableType, colWidths, hiddenColumns, colHeaders, thispage );  

}


function ssaddcovtable(covs, data, emptytable2, covcsplist){

  var site=`${data[0].plot}${data[0].sqx}${data[0].sqy}`;
    $(`button[name=addcovsave${site}]`).off();  
// console.log(emptytable2);
  var container = $(`#addcovtable${site}`);
  // console.log(site);
  var saveButtonName=`addcovsave${site}`;
  var tableType='addcov';
  var thispage=1;

  var columns = [
      {data: "date", dateFormat: 'YYYY-MM-DD', type: 'date'},
      {data: "plot", readOnly: true},
      {data: "sqx", type: 'numeric', allowInvalid: false, validator: qqValidator},
      {data: "sqy", type: 'numeric', allowInvalid: false, validator: qqValidator},
      {data: "layer", allowInvalid: false, validator: layerValidator},
      {data: "csp", type: 'autocomplete', source: covcsplist, strict: true, visibleRows: 10, allowInvalid: false,},
      {data: "cover", type: 'numeric', allowInvalid: false, validator: numericValidator100},
      {data: "height", type: 'numeric', allowInvalid: false},
      {data: "note"},
      {data: "id"},

    ];

  var colWidths=[120, 80,25,25,50, 120,50, 50,120];
  var colHeaders=["date", "plot", "5x", "5y", "layer" ,"csp","cov","height",  "note"];

  var hiddenColumns ={
      columns: [9],
    };
  return createHandsontable(container, columns, emptytable2, saveButtonName, "/ss10msaveaddcov", tableType, colWidths, hiddenColumns, colHeaders, thispage );  

}


function sscovtable(covs, data, covcsplist){
  var site=`${data[0].plot}${data[0].sqx}${data[0].sqy}`;
$(`button[name=covsave${site}]`).off();
  var container = $(`#covtable${site}`);

  var saveButtonName=`covsave${site}`;
  var tableType='cov';
  var thispage=1;

  var columns = [
      {data: "date", dateFormat: 'YYYY-MM-DD', type: 'date'},
      {data: "plot", readOnly: true},
      {data: "sqx", type: 'numeric', allowInvalid: false, validator: qqValidator},
      {data: "sqy", type: 'numeric', allowInvalid: false, validator: qqValidator},
      {data: "layer", allowInvalid: false, validator: layerValidator},
      {data: "csp", type: 'autocomplete', source: covcsplist, strict: true, visibleRows: 10, allowInvalid: false,},
      {data: "cover", type: 'numeric', allowInvalid: false, validator: numericValidator100},
      {data: "height", type: 'numeric', allowInvalid: false},
      {data: "note"},
      {data: "delete", renderer: "html"},
      {data: "id"},

    ];

  var colWidths=[120, 80,25,25,50, 120,50, 50,120, 50];
  var colHeaders=["date", "plot", "sqx", "sqy", "layer" ,"csp","cov","height",  "note", ""];

  var hiddenColumns ={
      columns: [10],
    };
  return createHandsontable(container, columns, covs, saveButtonName, "/ss10msavecov", tableType, colWidths, hiddenColumns, colHeaders, thispage );  

}



  function deletecov(id, entry){
    
    if(confirm('確定刪除此筆覆蓋度資料??')) 
    {
      $('.covsavenote').html('');

      var saveUrl=`/ss10mdeletecov/${id}/${entry}`;
      var ajaxData={};
      var ajaxType='get';

      function handleSuccess(res) {
            if (res.covsavenote !=''){
              $('.covsavenote').html(res.covsavenote);
            }
            sscovtableupdate(res.covs);
      }
      makeAjaxRequest(
        saveUrl, ajaxData, ajaxType,
        handleSuccess,
        function () {}
      );
    }
  }

function sscovtableupdate(covs){
  // $('.addcovsavenote').html('');
  var site=`${data[0].plot}${data[0].sqx}${data[0].sqy}`;
  // console.log(site);
  if (covs.length>0){
      $('.covtable').show();
      $('.nocovdata').hide();
      // $('.addcovtableout').hide();
      // console.log(covs.legth);
  } else {
      $('.covtable').hide();
      $('.nocovdata').show();
  }
  var container = $(`#covtable${site}`);
  var handsontable = container.data('handsontable');
// console.log(covs);
  handsontable.updateData(covs);
}

