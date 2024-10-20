
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
  site=event.detail.site;

  // console.log(data);

    $(".save2").unbind();
    $('.finishnote').html();
    sscovtable(covs, site, covcsplist);
    ssaddcovtable(covs, site, emptytable2, covcsplist); 
    if (covs.length!=0){
      
      $('.covtable').show();
      $('.nocovdata').hide();
 
    } else {

      $('.covtable').hide();
      $('.nocovdata').show();
    }
    console.log(data);
    //一開始,thispage=1
    ssenvitable(envi, site);

    if (data!='無'){
    ssdatatable(data, 1, 20, site);

    }
    ssrecruittable(data, emptytable, csplist, site);
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
 var cellProperties = {}; 
      if (tableType=='data'){
          var cellProperties = {};
          if (container.handsontable('getData')[row][7]=='-9'){
            cellProperties.readOnly = false; 
            if (col==1 || col == 7 || col == 4 || col == 5 || col == 14){
              cellProperties.readOnly = true; 
            }
          }
//note字變小
          if (col == 13 || col==15){
            cellProperties.className = 'fs08'; 
          }
         return cellProperties;
      } else if (tableType=='updateData1'){

          if (container.handsontable('getData')[row][4]!='0' & container.handsontable('getData')[row][6]!='y'){
            cellProperties.readOnly = true; 
              if (col==4 || col==3){
                cellProperties.readOnly = false; 
              }
          } else if (container.handsontable('getData')[row][6]=='y'){
            cellProperties.readOnly = false; 
              if (col==5){
                cellProperties.readOnly = true; 
              }
          }
          return cellProperties;
      } else if (tableType=='updateData2'){
        if (container.handsontable('getData')[row][1]!=''){ 
          if (container.handsontable('getData')[row][0]=='2014'){
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

function ssenvitable(envi, site){

  var container = $(`#envitable${site}`);
  var saveButtonName=`envisave${site}`;
  var tabletype='envi';
  console.log(envi);
 
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

  return createHandsontable(container, columns, envi, saveButtonName, `${urlbase}/saveenvi`, tabletype, colWidths, hiddenColumns, colHeaders, 1 );

}

function ssdatatable(data, thispage, pps, site){

  $('.envisavenote').html('');
  $('.finishnote').html();
  $('.totalnum').html(`共有 ${data.length} 筆資料。`);
 
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
      {data: "pom", type: 'numeric'},
      {data: "note"},
      {data: "confirm", type: 'checkbox', checkedTemplate: '1', uncheckedTemplate: ''},
      {data: "alternotetable", renderer: "html", readOnly: true},
      {data: "update_id"}

    ];

  var colWidths=[120, 80,25,25,80, 40, 120,50,50,60,50,50,50,160,50, 160];
  var colHeaders=["Date","plot","5x","5y", "tag", "b", "csp",'status', "code","dbh","ill","leave","pom","note","縮水",""];

  var hiddenColumns ={
      columns: [16],
    };

  return createHandsontable(container, columns, data2, saveButtonName, `${urlbase}/savedata`, tabletype, colWidths, hiddenColumns, colHeaders, thispage );
}

  const tagValidator = (value, callback) => {
  if (!value || value === '') {
    callback(true);
  } else {
    callback(value.length <= 3);
  }
  };

function ssrecruittable(data, emptytable, csplist, site){
// console.log(csplist);
// console.log(emptytable);
   
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
      {data: "tag", validator: tagValidator},
      {data: "branch", type: 'numeric', allowInvalid: false},
      {data: "csp", type: 'autocomplete', source: csplist, strict: true, visibleRows: 10, allowInvalid: false},
      {data: "code"},
      {data: "dbh", type: 'numeric' , allowInvalid: false},
      {data: "ill", type: 'numeric', allowInvalid: false, validator: numericValidator5},
      {data: "leave", type: 'numeric', allowInvalid: false, validator: numericValidator100 },
      {data: "pom", type: 'numeric'},
      {data: "note"},
      {data: "tofix", type: 'checkbox', checkedTemplate: '1', uncheckedTemplate: ''}

    ];

  var colWidths=[120, 80,25,25,80, 40, 120,50,60,50,50,50,160,50];
  var colHeaders=["Date","plot","5x","5y", "tag", "b", "csp", "code","dbh","ill","leave","pom","note","漏資料"];

  var hiddenColumns =[];
  return createHandsontable(container, columns, emptytable, saveButtonName, `${urlbase}/saverecruit`, tabletype, colWidths, hiddenColumns, colHeaders, thispage );

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

  var plotList=['A1', 'A2', 'A3', 'B-F-01', 'B-F-04', 'B-F-06', 'B-F-13', 'B-F-14', 'B-F-19', 'G-F-01', 'G-F-02', 'G-F-03', 'G-F-06', 'Q-F-03', 'S-F-01', 'S-F-02', 'S-F-04', 'S-F-06', 'S-F-07', 'S-F-09', 'S-F-11', 'S-F-14', 'S-F-15', 'S-F-16', 'S-F-17', 'S-F-21', 'S-F-38'];

  var columns = [
      {data: "plot", type: 'autocomplete', source: plotList, strict: true, visibleRows: 10, allowInvalid: false,},
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

  var colWidths=[80,25,25,80, 40, 120,70,70, 100];
  var colHeaders=["plot","5x","5y", "tag", "b", "csp", "dbh(<1)", "原POM","other","stemid"];

  var hiddenColumns ={
      columns: [9],
    };
  return createHandsontable(container, columns, alterdata, saveButtonName, `${urlbase}/savealternote`, tableType, colWidths, hiddenColumns, colHeaders, thispage );  

}


function ssaddcovtable(covs, site, emptytable2, covcsplist){

  
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
  return createHandsontable(container, columns, emptytable2, saveButtonName, `${urlbase}/10msaveaddcov`, tableType, colWidths, hiddenColumns, colHeaders, thispage );  

}


function sscovtable(covs, site, covcsplist){

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
  return createHandsontable(container, columns, covs, saveButtonName, `${urlbase}/10msavecov`, tableType, colWidths, hiddenColumns, colHeaders, thispage );  

}



  function deletecov(id, entry){
    
    if(confirm('確定刪除此筆覆蓋度資料??')) 
    {
      $('.covsavenote').html('');

      var saveUrl=`${urlbase}/10mdeletecov/${id}/${entry}`;
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

  // console.log(site);
  console.log(covs);
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



//進行個別修改 //資料處理，後端資料更正

window.addEventListener('stemiddata', event => {

  stemid=event.detail.stemid;
  stemdata=event.detail.stemdata;
  csplist=event.detail.csplist;
  from=event.detail.from;
  console.log(stemid);
    ss10mupdatatable(stemid, stemdata, csplist, from);

});

Livewire.on('updateStemidlist', function(data) {
    // 更新 Livewire 组件中的数组
    Livewire.emit('updateStemidList', data);
});

//後端資料更正

function ss10mupdatatable(stemid, stemdata, csplist, from){

//basetable
  stemid = stemid.replace('.', ''); // Remove the period
console.log(stemid);
  var container1 = $(`#basetable${stemid}`);
  var saveButtonName=`basetable${stemid}`;
  var tabletype1='updateData1';


  var columns1 = [
      {data: "plot"},
      {data: "sqx", type: 'numeric'},
      {data: "sqy", type: 'numeric'},
      {data: "tag",},
      {data: "branch", type: 'numeric'},
      {data: "csp", type: 'autocomplete', source: csplist, strict: true, visibleRows: 10, allowInvalid: false,},
      {data: 'r'}

    ];

  var colWidths1=[80,25,25,80, 40, 120];
  var colHeaders1=["plot","5x","5y", "tag", "b", "csp"];

  var hiddenColumns1 ={
    columns: [6],
  };

  var handsontable1=createHandsontable(container1, columns1, stemdata[0], saveButtonName, "/ss10mupdate", tabletype1, colWidths1, hiddenColumns1, colHeaders1, 1 );

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


  var handsontable2=createHandsontable(container2, columns2, stemdata2, saveButtonName, "/ss10mupdate", tabletype2, colWidths2, hiddenColumns2, colHeaders2, 1 );


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