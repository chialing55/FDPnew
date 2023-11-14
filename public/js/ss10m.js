

window.addEventListener('data', event => {

  covs=event.detail.covs;
  data=event.detail.record;
  emptytable=event.detail.emptytable;
  emptytable2=event.detail.emptytable2;
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
    sstreetable(data);

    ssrecruittable(data, emptytable, csplist);
  }
});



function ssenvitable(envi, sqx, sqy){

  var container = $("#envitable"+envi[0].plot+sqx+sqy);
  var parent = container.parent();
  var cellChanges = [];

  const numericValidator = (value, callback) => {
    if (value === '0') {
      callback(true);
    } else {
      var numericValue = parseFloat(value);
      callback(!isNaN(numericValue) && numericValue >= 0 && numericValue <= 100);
    }
  };

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

  const numericValidator2 = (value, callback) => {
    if (value === '0') {
      callback(true);
    } else {
      var numericValue = parseFloat(value);
      callback(!isNaN(numericValue) && numericValue >= 0 && numericValue <= 20);
    }
  };

  container.handsontable({
    data: envi,
    // startRows: 3,
    colHeaders: true,
    // rowHeaders: true,
    // minSpareRows: 1,
    currentRowClassName: 'currentRow',
    colWidths: [80, 50, 50,50, 50,50, 50,50, 50, 80, 50, 50, 50, 50, 50, 50, 50, 50],
    licenseKey: 'non-commercial-and-evaluation',
    colHeaders: ["Plot", "坡度1","坡度2","坡度3","坡度4", "坡向1","坡向2","坡向3","坡向4", "地形", "岩石地<br>比例", "地表<br>裸露度", "凋落物<br>覆蓋度", "倒木<br>覆蓋度", 'T1', 'T2', 'S', 'H'],
    columns: [
      {data: "plot", readOnly:true},
      {data: "aspect1", type: 'numeric', validator: aspectValidator, placeholder: "(1,1)"},
      {data: "aspect2", type: 'numeric', validator: aspectValidator, placeholder: "(1,2)"},
      {data: "aspect3", type: 'numeric', validator: aspectValidator, placeholder: "(2,2)"},
      {data: "aspect4", type: 'numeric', validator: aspectValidator, placeholder: "(2,1)"},
      {data: "slope1", type: 'numeric', validator: slopeValidator, placeholder: "(1,1)"},
      {data: "slope2", type: 'numeric', validator: slopeValidator, placeholder: "(1,2)"},
      {data: "slope3", type: 'numeric', validator: slopeValidator, placeholder: "(2,2)"},
      {data: "slope4", type: 'numeric', validator: slopeValidator, placeholder: "(2,1)"},
      {data: "terrain", type: 'dropdown', source: ['上坡', '中坡', '下坡', '稜線'], allowInvalid: false},
      {data: "rocky", type: 'numeric', validator: numericValidator},
      {data: "exposed_surface", type: 'numeric', validator: numericValidator},
      {data: "litter_cover", type: 'numeric', validator: numericValidator},
      {data: "fallen_tree", type: 'numeric', validator: numericValidator},
      {data: "T1", type: 'numeric', validator: numericValidator2},
      {data: "T2", type: 'numeric', validator: numericValidator2},
      {data: "S", type: 'numeric', validator: numericValidator2},
      {data: "H", type: 'numeric', validator: numericValidator2}

    ],
    hiddenColumns: {
    // specify columns hidden by default
      // columns: [0],
    },
    // dropdownMenu: true,
    cells: function (row, col, prop) {
     },
    afterChange: function (changes, source) {
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
    },
        afterRender: function () {
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
        },
    });
  var handsontable = container.data('handsontable');

  parent.find('button[name=envisave'+envi[0].plot+sqx+sqy+']').click(function () {
    $('.envisavenote').html('');

    $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
      });

      $.ajax({
        url: "/ss10msaveenvi",
        data: {
          data: handsontable.getSourceData(),
          entry: entry,
          user:user
          // _token : '{{csrf-token()}}'
        }, //returns all cells' data
        dataType: 'json',
        type: 'POST',
        headers: 
          {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
        success: function (res) {
          if (res.result === 'ok') {
            console.log('Data saved');
            console.log(res);
            if (res.envisavenote !=''){
              $('.envisavenote').html(res.envisavenote);
            }


          }
          else {
            console.log('Save error');
          }
        },
        error: function () {
          console.log('Save error2.');
        }
      });
      // console.log(handsontable.getData());
  });



}

function sstreetable(data){
  $('.finishnote').html();
  $('.totalnum').html(`共有 ${data.length} 筆資料`);
  var container = $("#datatable"+data[0].plot+data[0].sqx+data[0].sqy);
  var parent = container.parent();
  var cellChanges = [];
  const numericValidator = (value, callback) => {
    if (value === '0') {
      callback(true);
    } else {
      var numericValue = parseFloat(value);
      callback(!isNaN(numericValue) && numericValue >= 0 && numericValue <= 100);
    }
  };

  container.handsontable({
    data: data,
    // height: 320,
    // startRows: 3,
    colHeaders: true,
    rowHeaders: true,
    rowHeaderWidth: 25,
    rowHeights: 38,
    removeRowPlugin: true,
    // minSpareRows: 1,
    colWidths: [120, 80,25,25,80, 40, 120,50,50,60,50,50,160,50, 160],
    licenseKey: 'non-commercial-and-evaluation',
 
    colHeaders: ["Date","plot","5x","5y", "tag", "b", "csp",'status', "code","dbh","ill","leave","note","縮水",""],
    columns: [
      {data: "date", dateFormat: 'YYYY-MM-DD', type: 'date', allowInvalid: false},
      {data: "plot", readOnly: true},
      {data: "sqx", readOnly: true},
      {data: "sqy", readOnly: true},
      {data: "tag", readOnly: true},
      {data: "branch", readOnly: true},
      {data: "csp", readOnly: true, type: 'autocomplete', source: csplist, strict: true, visibleRows: 10, allowInvalid: false,},
      {data: "status", type: 'dropdown', source: ['', '0', '-1', '-2', '-3'], allowInvalid: false},
      {data: "code"},
      {data: "dbh", type: 'numeric'},
      {data: "ill", type: 'numeric', validator: numericValidator},
      {data: "leave", type: 'numeric', validator: numericValidator},
      {data: "note"},
      {data: "confirm", type: 'checkbox', checkedTemplate: '1', uncheckedTemplate: ''},
      {data: "alternotetable", renderer: "html", readOnly: true},
      {data: "update_id"}
    ],
    hiddenColumns: {
    // specify columns hidden by default
      columns: [15],
    },
    currentRowClassName: 'currentRow',
    autoWrapRow: true,   //自動換行
    manualColumnResize: true,
    // manualRowResize: true,
    cells: function (row, col, prop) {
  
          var cellProperties = {};
          // var curData = container.handsontable('getData')[row][10]; //column 10 is the field "sprout"
          if (container.handsontable('getData')[row][7]=='-9'){
            cellProperties.readOnly = false; 
            if (col==1 || col == 7){
              cellProperties.readOnly = true; 
            }

          }

//note字變小
          if (col == 12 || col==14){
            cellProperties.className = 'fs08'; 
          }

         return cellProperties;

    },

    afterChange: function (changes, source) {
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
    },
        afterRender: function () {
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
        },
});
 

  var handsontable = container.data('handsontable');

  parent.find('button[name=datasave'+data[0].plot+data[0].sqx+data[0].sqy+']').click(function () {
    $('.datasavenote').html('');
// console.log(handsontable.getSourceData());
    $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
      });

      $.ajax({
        url: "/ss10msavedata",
        data: {
          data: handsontable.getSourceData(),
          entry: entry,
          user: user
          // _token : '{{csrf-token()}}'
        }, //returns all cells' data
        // dataType: 'json',
        type: 'POST',
        success: function (res) {
          if (res.result === 'ok') {
            console.log('Data saved');
         
            if (res.datasavenote !=''){
              $('.datasavenote').html(res.datasavenote);
              // ss10mtableupdate(res.data, thispage);
              // console.log(thispage);
              // handsontable.updateData(res.data, thispage);
              // console.log(res.datasavenote);
              // container.render();
            } 
            console.log(res);
            // console.log(res.list);

          }
          else {
            console.log('Save error');
          }
        },
        error: function () {
          console.log('Save error2.');
        }
      });
      // console.log(handsontable.getSourceData());
  });  
}

function ssrecruittable(data, emptytable, csplist){
// console.log(csplist);
// console.log(emptytable);
  var site=data[0].plot+data[0].sqx+data[0].sqy;
 
$(`button[name=recruitsave${site}]`).off();
  var container = $("#recruittable"+site);
  var parent = container.parent();
  // var emptytable=emptytable;

  const qqValidator = (value, callback) => {
    if ([1, 2, ''].includes(value)) {   //允許1234和空格
      callback(true);
    } else {
      callback(false);
    }
  };
  const numericValidator = (value, callback) => {
    if (value === '0') {
      callback(true);
    } else {
      var numericValue = parseFloat(value);
      callback(!isNaN(numericValue) && numericValue >= 0 && numericValue <= 100);
    }
  };
  container.handsontable({
    data: emptytable,
    // dataSchema: {
    //   plot: data[0].qx,
    //   qy: data[0].qy,
    //   date: '',
    //   sqx: '',
    //   sqy: '',
    //   tag: '',
    //   branch: '0',
    //   csp: '',
    //   code: '',
    //   dbh: '',
    //   ill:'',
    //   leave:'',
    //   note: '',
    //   tofix: ''
    // },
    startRows: 30,
    colHeaders: true,
    rowHeaders: true,
    rowHeaderWidth: 25,
    contextMenu: ['row_above', 'row_below', 'remove_row'],
    // minSpareRows: 1,
    rowHeights: 35,
    colWidths: [120, 80,25,25,80, 40, 120,50,60,50,50,160,50],
    licenseKey: 'non-commercial-and-evaluation',
 
    colHeaders: ["Date","plot","5x","5y", "tag", "b", "csp", "code","dbh","ill","leave","note","漏資料"],
    columns: [
      {data: "date", dateFormat: 'YYYY-MM-DD', type: 'date', allowInvalid: false},
      {data: "plot", readOnly: true},
      {data: "sqx", type: 'numeric', allowInvalid: false, validator: qqValidator},
      {data: "sqy", type: 'numeric', allowInvalid: false, validator: qqValidator},
      {data: "tag"},
      {data: "branch", type: 'numeric'},
      {data: "csp", type: 'autocomplete', source: csplist, strict: true, visibleRows: 10, allowInvalid: false},
      {data: "code"},
      {data: "dbh", type: 'numeric' },
      {data: "ill", type: 'numeric', validator: numericValidator},
      {data: "leave", type: 'numeric', validator: numericValidator },
      {data: "note"},
      {data: "tofix", type: 'checkbox', checkedTemplate: '1', uncheckedTemplate: ''}

    ],
    currentRowClassName: 'currentRow',
    manualColumnResize: true,

    cells: function (row, col, prop) {
  
     }

    });


  var handsontable = container.data('handsontable');

  parent.find('button[name=recruitsave'+site+']').click(function () {
    $('.recruitsavenote').html('');

    $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
      });

      $.ajax({
        url: "/ss10msaverecruit",
        data: {
          data: handsontable.getSourceData(),
          entry: entry,
          user: user
          // _token : '{{csrf-token()}}'
        }, //returns all cells' data
        // dataType: 'json',
        type: 'POST',
        success: function (res) {
          if (res.result === 'ok') {
            console.log('Data saved');
            console.log(res);

            if (res.recruitsavenote !=''){
              $('.recruitsavenote').html(res.recruitsavenote);
            }

            // $('#seedlingtable'+res.recruit[0].trap).html('');
            // $('#recruittable'+res.recruit[0].trap).html('');
        // console.log(res.recruit.data[0].trap);
        //新增完，thispage=1
            handsontable.updateData(res.nonsavelist);
            if (res.data.length !== 0){
              ss10mtableupdate(res.data);
            }
        // handsontable.clear();
        // handsontable.loadData(res.temp);

          }
          else {
            console.log('Save error');
          }
        },
        error: function () {
          console.log('Save error2.');
        }
      });
      // console.log(handsontable.getData());
  });

// console.log(emptytable);
  parent.find('button[name=clearrecruittable]').click(function () {
    // data2=[];
    // handsontable.clear();
    // console.log(emptytable);
    $('.recruitsavenote').html('');
    handsontable.updateData(emptytable);
  });

}


  function deleteid(stemid, entry){
    // console.log(entry);
    if(confirm('確定刪除 '+stemid+' 新增樹資料??')) 
    {
      $('.datasavenote').html('');
        $.ajax({
        url: `/ss10mdeletedata/${stemid}/${entry}`,
        type: 'get',
        success: function (res) {
          // console.log(res);
          if (res.result === 'ok') {
            console.log('Data saved');
            console.log(res);
            if (res.datasavenote !=''){
              $('.datasavenote').html(res.datasavenote);
            }
            ss10mtableupdate(res.recruit);
          }
          else {
            console.log('Save error');
          }
        },
        error: function () {
          console.log('Save error2.');
        }
      });
    }
  }


function alternote(stemid, entry) {
  // console.log(stemid);
  $('.altersavenote').html('');


  var posX = $("button[name='alternoteshow"+stemid+"']").offset().left;
    var posY = $("button[name='alternoteshow"+stemid+"']").offset().top;
    console.log(posX+", "+posY);

    $('.alternotetalbeouter').css('top', posY);
    $('.alternotetalbeouter').css('left', posX-550);
  
// $(".deletealternotebutton").removeAttr("stemid thispage");

  $('.alternotetalbeouter').show();
  $('.alterstemid').html(stemid);

      $.ajax({
        url: "/ss10maddalternote/"+stemid+"/"+entry,
        type: 'get',
        success: function (res) {
          if (res.result === 'ok') {
            console.log('Data show');
            alternotetable(res.alterdata,stemid, entry);

        if (res.havedata=='yes'){
          $('.deletealternotebutton').show();
          $('.deletealternotebutton').attr({'stemid': stemid});
          
        } else {
          $('.deletealternotebutton').hide();
        }
            // console.log(res);
          }
          else {
            console.log('Save error');
          }
        },
        error: function () {
          console.log('Save error2.');
        }
      });

  // alternotetable(stemid, entry);
}

function deletealternoteButtonClick(button){
  const stemid = $(button).attr('stemid');
 
  deletealternote(stemid);  
}

function deletealternote(stemid){
    if(confirm('確定刪除 '+stemid+' 特殊修改??')) 
    {
      $('.altersavenote').html('');
        $.ajax({
        url: `/ss10mdeletealter/${stemid}/${entry}`,
        type: 'get',
        success: function (res) {
          // console.log(res);
          if (res.result === 'ok') {
            console.log('Data saved');
            // console.log(res);
            if (res.datasavenote !=''){
              $('.altersavenote').html(res.datasavenote);
            }
            ss10mtableupdate(res.data);

      var container = $("#alternotetable");
      var handsontable = container.data('handsontable');
      handsontable.updateData(res.realterdata);
      $('.deletealternotebutton').hide();

          }
          else {
            console.log('Save error');
          }
        },
        error: function () {
          console.log('Save error2.');
        }
      });
    }

}

function alternotetable(alterdata, stemid, entry){
  $("button[name=alternotesave]").off();
  var container = $("#alternotetable");
 
  container.handsontable('destroy');
  $('.deletealternotebutton').attr({'stemid': stemid});

  var parent = container.parent();
  var cellChanges = [];
  container.handsontable({
    data: alterdata,
    // height: 320,
    startRows: 1,
    colHeaders: true,
    removeRowPlugin: true,
    // minSpareRows: 1,
    colWidths: [80,25,25,80, 40, 120,70],
    licenseKey: 'non-commercial-and-evaluation',
 
    colHeaders: ["plot","5x","5y", "tag", "b", "csp", "dbh(<1)", "stemid"],
    columns: [
      {data: "plot", type: 'numeric'},
      {data: "sqx", type: 'numeric'},
      {data: "sqy", type: 'numeric'},
      {data: "tag"},
      {data: "b", type: 'numeric'},
      {data: "csp", type: 'autocomplete', source: csplist, strict: true, visibleRows: 10, allowInvalid: false,},
      {data: "dbh", type: 'numeric'},
      
      {data: "stemid"}
    ],
    currentRowClassName: 'currentRow',
    autoWrapRow: true,   //自動換行
    manualColumnResize: true,
    hiddenColumns: {
    // specify columns hidden by default
      columns: [7],
    },
  });

  var handsontable = container.data('handsontable');

  parent.find('button[name=alternotesave]').click(function () {
    $('.altersavenote').html('');

    $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
      });

      $.ajax({
        url: "/ss10msavealternote",
        data: {
          data: handsontable.getSourceData(),
          entry: entry,
          user: user

        }, 
        type: 'POST',
        success: function (res) {
          if (res.result === 'ok') {
            console.log('Data saved');
            if (res.datasavenote !=''){
              $('.altersavenote').html(res.datasavenote);
            } 
            console.log(res);
            ss10mtableupdate(res.data);
            $('.deletealternotebutton').show();
          }
          else {
            console.log('Save error');
          }
        },
        error: function () {
          console.log('Save error2.');
        }
      });
  });
}

function ss10mtableupdate(data){
  $('.finishnote').html('');
    var site=data[0].plot+data[0].sqx+data[0].sqy;
  $('.datasavenote').html('');
  var container = $("#datatable"+site);
  var handsontable = container.data('handsontable');
  // console.log(data);
  
  $('.totalnum').html(`共有 ${data.length} 筆資料`);

  
// console.log(data3);
  handsontable.updateData(data);
  handsontable.updateSettings({
    cells: function (row, col, prop) {
  
          var cellProperties = {};
          if (container.handsontable('getData')[row][7]=='-9'){
            cellProperties.readOnly = false; 
            if (col==1 || col == 7){
              cellProperties.readOnly = true; 
            }
          }

//note字變小
          if (col == 12 || col==14){
            cellProperties.className = 'fs08'; 
          }
         return cellProperties;
    }
    });

    // Livewire.emit('updateAmount', data.length);

}




function ssaddcovtable(covs, data, emptytable2, covcsplist){
  var site=data[0].plot+data[0].sqx+data[0].sqy;
// console.log(emptytable2);
  var container = $("#addcovtable"+site);
  var parent = container.parent();
  // var cellChanges = [];
  const qqValidator = (value, callback) => {
    if ([1, 2, ''].includes(value)) {   //允許1234和空格
      callback(true);
    } else {
      callback(false);
    }
  };
  const numericValidator = (value, callback) => {
    if (value === '0') {
      callback(true);
    } else {
      var numericValue = parseFloat(value);
      callback(!isNaN(numericValue) && numericValue >= 0 && numericValue <= 100);
    }
  };

  const layerValidator = (value, callback) => {
    if (['u', 'o', ''].includes(value)) {   //允許1234和空格
      callback(true);
    } else {
      callback(false);
    }
  };

  container.handsontable({
    data:emptytable2,
    // startRows: 3,
    colHeaders: true,
    rowHeaders: true,
    rowHeaderWidth: 25,
    rowHeaderHeights: 35,
    contextMenu: ['row_above', 'row_below', 'remove_row'],
    // rowHeaders: true,
    // minSpareRows: 5,
    rowHeights: 35,
    currentRowClassName: 'currentRow',
    colWidths: [120, 80,25,25,50, 120,50, 50,120],
    licenseKey: 'non-commercial-and-evaluation',
    colHeaders: ["date", "plot", "5x", "5y", "layer" ,"csp","cov","height",  "note"],
    columns: [
      {data: "date", dateFormat: 'YYYY-MM-DD', type: 'date'},
      {data: "plot", readOnly: true},
      {data: "sqx", type: 'numeric', allowInvalid: false, validator: qqValidator},
      {data: "sqy", type: 'numeric', allowInvalid: false, validator: qqValidator},
      {data: "layer", allowInvalid: false, validator: layerValidator},
      {data: "csp", type: 'autocomplete', source: covcsplist, strict: true, visibleRows: 10, allowInvalid: false,},
      {data: "cover", type: 'numeric', allowInvalid: false, validator: numericValidator},
      {data: "height", type: 'numeric'},
      {data: "note"},
      {data: "id"},

    ],
    hiddenColumns: {
    // specify columns hidden by default
      columns: [9],
    },
    // dropdownMenu: true,
    cells: function (row, col, prop) {
     },
    

    });

  var handsontable = container.data('handsontable');

  parent.find('button[name=addcovsave'+site+']').click(function () {
    $('.covsavenote').html('');

    $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
      });

      $.ajax({
        url: "/ss10msaveaddcov",
        data: {
          data: handsontable.getSourceData(),
          entry: entry,
          user: user
          // _token : '{{csrf-token()}}'
        }, //returns all cells' data
        dataType: 'json',
        type: 'POST',
        headers: 
          {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
        success: function (res) {
          if (res.result === 'ok') {
            console.log('Data saved');
            console.log(res);
            if (res.covsavenote !=''){
              $('.addcovsavenote').html(res.addcovsavenote);
            }
            handsontable.updateData(res.nonsavelist);
            if (res.data.length !== 0){
              sscovtableupdate(res.data);
            }

          }
          else {
            console.log('Save error');
          }
        },
        error: function () {
          console.log('Save error2.');
        }
      });
      // console.log(handsontable.getData());
  });

// console.log(emptytable);
  parent.find('button[name=clearaddcovtable]').click(function () {
    // data2=[];
    // handsontable.clear();
    // console.log(emptytable);
    $('.recruitsavenote').html('');
    handsontable.updateData(emptytable2);
  });


}


function sscovtable(covs, data, covcsplist){
  var site=data[0].plot+data[0].sqx+data[0].sqy;

  // if (covs.legth>0){
  //     $('.covtable').show();
  //     $('.nocovdata').hide();
  // } else {
  //     $('.covtable').hide();
  //     $('.nocovdata').show();
  // }

  var container = $("#covtable"+site);
  var parent = container.parent();
  var cellChanges = [];

  const qqValidator = (value, callback) => {
    if ([1, 2, ''].includes(value)) {   //允許1234和空格
      callback(true);
    } else {
      callback(false);
    }
  };

  const numericValidator = (value, callback) => {
    if (value === '0') {
      callback(true);
    } else {
      var numericValue = parseFloat(value);
      callback(!isNaN(numericValue) && numericValue >= 0 && numericValue <= 100);
    }
  };

  const layerValidator = (value, callback) => {
    if (['u', 'o', ''].includes(value)) {   //允許1234和空格
      callback(true);
    } else {
      callback(false);
    }
  };

  container.handsontable({
    data: covs,
    // startRows: 3,
    colHeaders: true,
    rowHeaders: true,
    rowHeaderWidth: 25,
    rowHeaderHeights: 35,
    contextMenu: ['row_above', 'row_below', 'remove_row'],
    // rowHeaders: true,
    // minSpareRows: 5,
    rowHeights: 35,
    currentRowClassName: 'currentRow',
    colWidths: [120, 80,25,25,50, 120,50, 50,120, 50],
    licenseKey: 'non-commercial-and-evaluation',
    colHeaders: ["date", "plot", "sqx", "sqy", "layer" ,"csp","cov","height",  "note", ""],
    columns: [
      {data: "date", dateFormat: 'YYYY-MM-DD', type: 'date'},
      {data: "plot", readOnly: true},
      {data: "sqx", type: 'numeric', allowInvalid: false, validator: qqValidator},
      {data: "sqy", type: 'numeric', allowInvalid: false, validator: qqValidator},
      {data: "layer", allowInvalid: false, validator: layerValidator},
      {data: "csp", type: 'autocomplete', source: covcsplist, strict: true, visibleRows: 10, allowInvalid: false,},
      {data: "cover", type: 'numeric', allowInvalid: false, validator: numericValidator},
      {data: "height", type: 'numeric'},
      {data: "note"},
      {data: "delete", renderer: "html"},
      {data: "id"},

    ],
    hiddenColumns: {
    // specify columns hidden by default
      columns: [10],
    },
    // dropdownMenu: true,
    cells: function (row, col, prop) {
     },
    afterChange: function (changes, source) {
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
    },
        afterRender: function () {
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
        },    

    });

  var handsontable = container.data('handsontable');

  parent.find('button[name=covsave'+site+']').click(function () {
    $('.covsavenote').html('');

    $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
      });

      $.ajax({
        url: "/ss10msavecov",
        data: {
          data: handsontable.getSourceData(),
          entry: entry,
          user: user
          // _token : '{{csrf-token()}}'
        }, //returns all cells' data
        dataType: 'json',
        type: 'POST',
        headers: 
          {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
        success: function (res) {
          if (res.result === 'ok') {
            console.log('Data saved');
            console.log(res);
            if (res.covsavenote !=''){
              $('.covsavenote').html(res.covsavenote);
            }
           sscovtableupdate(res.data);

          }
          else {
            console.log('Save error');
          }
        },
        error: function () {
          console.log('Save error2.');
        }
      });
      // console.log(handsontable.getData());
  });

  $('.deletecov').on('click', function(){
    const id=$(this).attr('deleteid');
    const entry2=$(this).attr('entry');
    deletecov(id, entry2);
  })
}

  function deletecov(id, entry){
    
    if(confirm('確定刪除此筆覆蓋度資料??')) 
    {
      $('.covsavenote').html('');
        $.ajax({
        url: "/ss10mdeletecov/"+id+"/"+entry,
        type: 'get',
        success: function (res) {
          if (res.result === 'ok') {
            console.log('Data saved');
            console.log(res);
            if (res.covsavenote !=''){
              $('.covsavenote').html(res.covsavenote);
            }
            sscovtableupdate(res.covs);
          }
          else {
            console.log('Save error');
          }
        },
        error: function () {
          console.log('Save error2.');
        }
      });
    }
  }

function sscovtableupdate(covs){

  var site=covs[0].plot+covs[0].sqx+covs[0].sqy;
  console.log(site);
  if (covs.length>0){
      $('.covtable').show();
      $('.nocovdata').hide();
      // $('.addcovtableout').hide();
      // console.log(covs.legth);
  } else {
      $('.covtable').hide();
      $('.nocovdata').show();
  }
  var container = $("#covtable"+site);
  var handsontable = container.data('handsontable');
// console.log(covs);
  handsontable.updateData(covs);

  $('.deletecov').on('click', function(){
    id=$(this).attr('deleteid');
    entry1=$(this).attr('entry');
    deletecov(id, entry1);
  })
}