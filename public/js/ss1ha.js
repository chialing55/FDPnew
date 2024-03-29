var plotType='ss1ha';

window.addEventListener('data', event => {


  data=event.detail.record;
  emptytable=event.detail.emptytable;
  envi=event.detail.envi;
  csplist=event.detail.csplist;


  // console.log(emptytable);

    $(".save2").unbind();
    $('.finishnote').html();
    
    //一開始,thispage=1
    ssenvitable(envi, data[0].qx, data[0].qy, data[0].sqx, data[0].sqy);

    if (data[0].tag!='無'){
    ssdatatable(data, 1, 20);
    ssrecruittable(data, emptytable, csplist);
  }
});

function cellfunction(tableType, container, row, col, prop){
        if (tableType=='data'){
          var cellProperties = {};
          if (container.handsontable('getData')[row][8]=='-9'){
            cellProperties.readOnly = false; 
            if (col==1 || col == 2 || col == 5 || col == 6 || col == 8 || col == 15){
              cellProperties.readOnly = true; 
            }
          }
//note字變小
          if (col == 14 || col==16){
            cellProperties.className = 'fs08'; 
          }
         return cellProperties;
      }
}


function ssenvitable(envi, qx, qy ,sqx, sqy){


  var container = $(`#envitable${qx}${qy}${sqx}${sqy}`);
  // var parent = container.parent();
  // var cellChanges = [];
  var saveButtonName=`envisave${qx}${qy}${sqx}${sqy}`;
  var tableType='envi';


  var columns = [
      {data: "qx", readOnly:true},
      {data: "qy", readOnly:true},
      {data: "rocky", type: 'numeric', allowInvalid: false, allowInvalid: false, validator: numericValidator10},
      {data: "exposed_surface", type: 'numeric', allowInvalid: false, validator: numericValidator10},
      {data: "litter_cover", type: 'numeric', allowInvalid: false, validator: numericValidator10},
      {data: "fallen_tree", type: 'numeric', allowInvalid: false, validator: numericValidator10},
      {data: "arenga", type: 'numeric', allowInvalid: false, validator: numericValidator10},
      {data: "T1", type: 'numeric', allowInvalid: false, validator: numericValidator20},
      {data: "T2", type: 'numeric', allowInvalid: false, validator: numericValidator20},
      {data: "S", type: 'numeric', allowInvalid: false, validator: numericValidator20},
      {data: "H", type: 'numeric', allowInvalid: false, validator: numericValidator20}

    ];

  var colWidths=[50, 50, 50, 50, 50, 50, 50, 50,50,50,50];
  var colHeaders=["10x","10y", "岩石地", "裸露度", "凋落物", "倒木","山棕", 'T1', 'T2', 'S', 'H'];

  var hiddenColumns =[];

  return createHandsontable(container, columns, envi, saveButtonName, "/ssPlotsaveenvi", tableType, colWidths, hiddenColumns, colHeaders, 1 );


}

function ssdatatable(data, thispage, pps){

  $('.envisavenote').html('');
  $('.finishnote').html();
  $('.totalnum').html(`共有 ${data.length} 筆資料。`);
  var site=`${data[0].qx}${data[0].qy}${data[0].sqx}${data[0].sqy}`;
  var container = $(`#datatable${site}`);
  $(`button[name=datasave${site}]`).off();
  var saveButtonName=`datasave${site}`;
  var tableType='data';
  ppsall=pps;
  var data2 = processDataTable(data, thispage, pps, site, plotType);

  var columns = [
      {data: "date", dateFormat: 'YYYY-MM-DD', type: 'date', allowInvalid: false},
      {data: "qx", readOnly: true},
      {data: "qy", readOnly: true},
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
      {data: "pom", type: 'numeric'},
      {data: "note"},
      {data: "confirm", type: 'checkbox', checkedTemplate: '1', uncheckedTemplate: ''},
      {data: "alternotetable", renderer: "html", readOnly: true},
      {data: "update_id"}

    ];

  var colWidths=[120, 30,30,25,25,80, 40, 120,50,50,60,50,50,50,160,50, 160];
  var colHeaders=["Date","10x","10y","5x","5y", "tag", "b", "csp",'status', "code","dbh","ill","leave","pom","note","縮水",""];

  var hiddenColumns ={
      columns: [17],
    };

  return createHandsontable(container, columns, data2, saveButtonName, "/ssPlotsavedata", tableType, colWidths, hiddenColumns, colHeaders, thispage );


 
}

function ssrecruittable(data, emptytable, csplist){

  var site=`${data[0].qx}${data[0].qy}${data[0].sqx}${data[0].sqy}`;
  var thispage=Math.ceil(data.length/20); //指定新增後前往最後一頁
 
  $(`button[name=recruitsave${site}]`).off();
  var container = $(`#recruittable${site}`);
  // console.log(site);
  var saveButtonName=`recruitsave${site}`;
  var tableType='recruit';

  var columns = [
      {data: "date", dateFormat: 'YYYY-MM-DD', type: 'date', allowInvalid: false},
      {data: "qx", readOnly: true},
      {data: "qy", readOnly: true},
      {data: "sqx", type: 'numeric', allowInvalid: false, validator: qqValidator},
      {data: "sqy", type: 'numeric', allowInvalid: false, validator: qqValidator},
      {data: "tag"},
      {data: "branch", type: 'numeric', allowInvalid: false},
      {data: "csp", type: 'autocomplete', source: csplist, strict: true, visibleRows: 10, allowInvalid: false},
      {data: "code"},
      {data: "dbh", type: 'numeric', allowInvalid: false },
      {data: "ill", type: 'numeric', allowInvalid: false, validator: numericValidator5},
      {data: "leave", type: 'numeric', allowInvalid: false, validator: numericValidator100 },
      {data: "pom", type: 'numeric'},
      {data: "note"},
      {data: "tofix", type: 'checkbox', checkedTemplate: '1', uncheckedTemplate: ''}

    ];

  var colWidths=[120, 30, 30,25,25,80, 40, 120,50,60,50,50,50,160,50];
  var colHeaders=["Date","10x","10y","5x","5y", "tag", "b", "csp", "code","dbh","ill","leave","pom","note","漏資料"];

  var hiddenColumns =[];
  return createHandsontable(container, columns, emptytable, saveButtonName, "/ssPlotsaverecruit", tableType, colWidths, hiddenColumns, colHeaders, thispage );

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

  var columns = [
      {data: "qx", type: 'numeric', allowInvalid: false},
      {data: "qy", type: 'numeric', allowInvalid: false},
      {data: "sqx", type: 'numeric', allowInvalid: false},
      {data: "sqy", type: 'numeric', allowInvalid: false},
      {data: "tag"},
      {data: "b", type: 'numeric', allowInvalid: false},
      {data: "csp", type: 'autocomplete', source: csplist, strict: true, visibleRows: 10, allowInvalid: false,},
      {data: "dbh", type: 'numeric', allowInvalid: false},
      {data: "原POM", type: 'numeric'},
      {data: "other"},
      {data: "stemid"},

    ];

  var colWidths=[30, 30,25,25,80, 40, 120,70,70, 100];
  var colHeaders=["10x","10y","5x","5y", "tag", "b", "csp", "dbh(<1)","原POM", "other","stemid"];

  var hiddenColumns ={
      columns: [10],
    };
  return createHandsontable(container, columns, alterdata, saveButtonName, "/ssPlotsavealternote", tableType, colWidths, hiddenColumns, colHeaders, thispage );

}




