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
  var cellProperties = {};
        if (tableType=='data'){
          
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
      } else if (tableType=='updateData1'){
          if (container.handsontable('getData')[row][5]!='0' & container.handsontable('getData')[row][7]!='y'){
            cellProperties.readOnly = true; 
              if (col==5 || col==4){
                cellProperties.readOnly = false; 
              }
          } else if (container.handsontable('getData')[row][7]=='y'){
            cellProperties.readOnly = false; 
              if (col==6){
                cellProperties.readOnly = true; 
              }
          }
          return cellProperties;
      } else if (tableType=='updateData2'){
        if (container.handsontable('getData')[row][1]!=''){ 
          if (container.handsontable('getData')[row][0]=='2015'){
            cellProperties.readOnly = true; 
              if (col==4 || col ==7 || col==8 ){
                cellProperties.readOnly = false; 
              }
          }

          if (col == 8 || col==10){
            cellProperties.className = 'fs08'; 
          }
        } else {
          cellProperties.readOnly = true; 
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

  return createHandsontable(container, columns, envi, saveButtonName, `${urlbase}/saveenvi`, tableType, colWidths, hiddenColumns, colHeaders, 1 );


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

  return createHandsontable(container, columns, data2, saveButtonName, `${urlbase}/savedata`, tableType, colWidths, hiddenColumns, colHeaders, thispage );


 
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
  return createHandsontable(container, columns, emptytable, saveButtonName, `${urlbase}/saverecruit`, tableType, colWidths, hiddenColumns, colHeaders, thispage );

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
  return createHandsontable(container, columns, alterdata, saveButtonName, `${urlbase}/savealternote`, tableType, colWidths, hiddenColumns, colHeaders, thispage );

}


//進行個別修改 //資料處理，後端資料更正

window.addEventListener('stemiddata', event => {

  stemid=event.detail.stemid;
  stemdata=event.detail.stemdata;
  csplist=event.detail.csplist;
  from=event.detail.from;
  console.log(stemid);
    ss1haupdatatable(stemid, stemdata, csplist, from);

});

Livewire.on('updateStemidlist', function(data) {
    // 更新 Livewire 组件中的数组
    Livewire.emit('updateStemidList', data);
});

//後端資料更正

function ss1haupdatatable(stemid, stemdata, csplist, from){

//basetable
  stemid = stemid.replace('.', ''); // Remove the period
console.log(stemid);
  var container1 = $(`#basetable${stemid}`);
  var saveButtonName=`basetable${stemid}`;
  var tabletype1='updateData1';


  var columns1 = [
      {data: "qx", type: 'numeric'},
      {data: "qy", type: 'numeric'},
      {data: "sqx", type: 'numeric'},
      {data: "sqy", type: 'numeric'},
      {data: "tag",},
      {data: "branch", type: 'numeric'},
      {data: "csp", type: 'autocomplete', source: csplist, strict: true, visibleRows: 10, allowInvalid: false,},
      {data: 'r'}

    ];

  var colWidths1=[25,25,25,25,80, 40, 120];
  var colHeaders1=["20x","20y","5x","5y", "tag", "b", "csp"];

  var hiddenColumns1 ={
    columns: [7],
  };

  var handsontable1=createHandsontable(container1, columns1, stemdata[0], saveButtonName, "/ss1haupdate", tabletype1, colWidths1, hiddenColumns1, colHeaders1, 1 );

  var stemdata2=stemdata.slice(1, 6);

  var container2 = $(`#datatable${stemid}`);
  var saveButtonName=`basetable${stemid}`;
  var tabletype2='updateData2';


  var columns2 = [
      {data: "census", readOnly:true},
      {data: "date", dateFormat: 'YYYY-MM-DD', type: 'date', allowInvalid: false},
      {data: "status", type: 'dropdown', source: ['', '0', '-1', '-2', '-3'], allowInvalid: false},
      {data: "code"},
      {data: "dbh", type: 'numeric', allowInvalid: false },
      {data: "ill", type: 'numeric', allowInvalid: false, validator: numericValidator5},
      {data: "leave", type: 'numeric', allowInvalid: false, validator: numericValidator100 },
      {data: "pom"},
      {data: "note"},
      {data: "confirm", type: 'checkbox', checkedTemplate: '1', uncheckedTemplate: ''},
      {data: "alternote"},
      {data: "stemid"}

    ];

  var colWidths2=[80,120, 50,50,50,50,50,50,170,50, 170];
  var colHeaders2=["census","date",'status', "code","dbh","ill", "leave", "POM","note","縮水","特殊修改"];

  var hiddenColumns2 ={
    columns: [11],
  }


  var handsontable2=createHandsontable(container2, columns2, stemdata2, saveButtonName, "/ss1haupdate", tabletype2, colWidths2, hiddenColumns2, colHeaders2, 1 );


  var handsontable1 = container1.data('handsontable');
  var handsontable2 = container2.data('handsontable');

  container2.parent().find(`button[name=datasave${stemid}]`).click(function () {
    $('.datasavenote').html('');

      var data1 = handsontable1.getSourceData();
      var data2 = handsontable2.getSourceData();

      var saveUrl=`${urlbase}/update`;
      var ajaxData={
          data1: data1,
          data2: data2,
          from: from,
          user: user,
          plotType: plotType,
      };
      var ajaxType='POST';

      function handleSuccess(res) {
            if (res.datasavenote !=''){
              $('.datasavenote').html(res.datasavenote);
            } 
            if (res.thisstemid !=''){
              Livewire.emit('updateStemidlist', {thisstemid: res.thisstemid, from: res.from});
            }
      }
      makeAjaxRequest(
        saveUrl, ajaxData, ajaxType,
        handleSuccess,
        function () {}
      );
  });
}

function deleteCensusDataButtonClick(button){
  const stemid = $(button).attr('stemid');
  const from = $(button).attr('from');
  deleteCensusData(stemid, from); 
}


function deleteCensusData(stemid, from){
    if(confirm('確定刪除 '+stemid+' 的所有資料??')) 
    {
      $('.altersavenote').html('');

      var saveUrl=`${urlbase}/deletecensusdata`;
      var ajaxData={
            stemid: stemid,
            from: from,
            // stemid: stemid,
            user: user,
            plotType: plotType
          };
      var ajaxType='post';

      function handleSuccess(res) {
            if (res.datasavenote !=''){
              $('.datasavenote').html(res.datasavenote);
            } 
            if (res.thisstemid !=''){
              Livewire.emit('updateStemidlist', {thisstemid: res.thisstemid, from: res.from});
            }
      }
      makeAjaxRequest(
        saveUrl, ajaxData, ajaxType,
        handleSuccess,
        function () {}
      );
    }

}




