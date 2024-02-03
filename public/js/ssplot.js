

// console.log(type);
//重新選擇工作項目
	$('.back').on('click', function(){
		location.href=`/choice`;
	})

//左側選單

$('.listlink').on('click', function(){
	let type=$(this).attr('type');
	console.log(type);
	if (typeof type!='undefined'){
	location.href=(`/shoushan/plot/${type}`);
	}

})


$('.list4, .list4inner').on('mouseenter', function() {
  $('.list4inner').css('display', 'inline-flex');
  $('.list4').css({'color': '#fff','background-color': '#91A21C'}); 
  $('.now hr').css('color', 'transparent');
}).on('mouseleave', function() {
  $('.list4inner').hide();
  $('.list4').css({'color': '','background-color': ''}); 
  $('.now hr').css('color', '#91A21C');
});

$('.list6, .list6inner').on('mouseenter', function() {
  $('.list6inner').css('display', 'inline-flex');
  $('.list6').css({'color': '#fff','background-color': '#91A21C'}); 
  $('.now hr').css('color', 'transparent');
}).on('mouseleave', function() {
  $('.list6inner').hide();
  $('.list6').css({'color': '','background-color': ''}); 
  $('.now hr').css('color', '#91A21C');
});



//download record
  $(".button1").click(function(){
    let plot = $("select[name='plot']").val();
// console.log(qx, qy);
    // var tempwindow1=window.open('_blank');
    if (plot!=''){
      let url='/ssplot-10m-record-pdf/'+plot;
      window.open(url);
    }
  });

  $(".button2").click(function(){
    let qx = $("select[name='qx']").val();
    let qy = $("select[name='qy']").val();
// console.log(qx, qy);
    // var tempwindow1=window.open('_blank');
    if (qx!='' && qy!=''){
      let url='/ssplot-1ha-record-pdf/'+qx+'/'+qy;
      window.open(url);
    }
  });

//ss1ha和ss10m共有的code

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

var ppsall;

function createHandsontable(container, columns, sourceData, saveButtonName, saveUrl, tableType, colWidths, hiddenColumns, colHeaders, thispage) {
  var cellChanges = [];
  var parent = container.parent();


  container.handsontable({
    data: sourceData,
    // startRows: startRows,
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
      if (tableType!='recruit' || tableType!='addcov'){
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
      if (tableType!='recruit' || tableType!='addcov'){
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
  if (tableType === 'recruit') {
    container.handsontable('updateSettings', {
      contextMenu: ['row_above', 'row_below', 'remove_row'],
    });
  }

  var handsontable = container.data('handsontable');
  // var noteClass=`${tableType}savenote`;
  var noteProperty = `${tableType}savenote`;
// console.log(tableType);
  container.parent().find(`button[name=${saveButtonName}]`).click(function () {
    // $(`.${noteProperty}`).html('');

    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
      },
    });

    $.ajax({
      url: saveUrl,
      data: {
        data: handsontable.getSourceData(),
        entry: entry,
        user: user,
        plotType: plotType,
        thispage: thispage,
      },
      type: 'POST',
      success: function (res) {
        if (res.result === 'ok') {
          console.log('Data saved');
          console.log(res);
          if (res[noteProperty] !=''){
            $(`.${noteProperty}`).html(res[noteProperty]);
          }

          if (tableType=='envi'){
            if (res.envisavenote !=''){
              $('.envisavenote').html(res.envisavenote);
            }
          } else if (tableType=='data'){
            
          } else if (tableType=='recruit'){
 
            handsontable.updateData(res.nonsavelist);
            if (res.data.length !== 0){
              $('.datasavenote').html('');
              ssdatatableupdate(res.data, res.thispage, ppsall);
            }
          } else if (tableType=='alternote'){
            if (res.datasavenote !=''){
              $('.altersavenote').html(res.datasavenote);
            } 
            $('.datasavenote').html('');
            ssdatatableupdate(res.data, res.thispage, ppsall);
            $('.deletealternotebutton').show();            
          } else if (tableType=='addcov'){
            if (res.addcovsavenote !=''){
              $('.covsavenote').html(res.addcovsavenote);
            } 
            handsontable.updateData(res.nonsavelist);
            if (res.data.length !== 0){
              sscovtableupdate(res.data);
            }
          } else if (tableType=='cov'){
              sscovtableupdate(res.data);
          }



        } else {
          console.log('Save error');
        }
      },
      error: function () {
        console.log('Save error.');
      },
    });
  });

  parent.find('button[name=clearrecruittable]').click(function () {
    $('.recruitsavenote').html('');
    handsontable.updateData(realemptytable);
    // console.log('ww');
  });

  parent.find('button[name=clearaddcovtable]').click(function () {
    $('.recruitsavenote').html('');
    handsontable.updateData(realemptytable2);
  });

  $('.deletecov').on('click', function(){
    const id=$(this).attr('deleteid');
    const entry2=$(this).attr('entry');
    deletecov(id, entry2);
  })

  return handsontable;
}


function ssdatatableupdate(data, thispage, pps){
  $('.finishnote').html('');

  if (data[0].plot != undefined){
    var site=`${data[0].plot}${data[0].sqx}${data[0].sqy}`;
  } else {
    var site=`${data[0].qx}${data[0].qy}${data[0].sqx}${data[0].sqy}`;
  }
  
  var totalpage=Math.ceil(data.length/pps);
  var container = $("#datatable"+site);
  var handsontable = container.data('handsontable');
  // console.log(data);
  
  $('.totalnum').html(`共有 ${data.length} 筆資料`);

  var data3 = (totalpage > 1) ? pages(data, thispage, totalpage, pps)[1] : data;

    for (let i = 0; i < data3.length; i++) {
      if (data3[i]['date'] === '0000-00-00') {
          data3[i]['date'] = ''; // 使用单等号进行赋值
      }
  }

  var tableType='data';
// console.log(data3);
  handsontable.updateData(data3, thispage);
  handsontable.updateSettings({
    cells: function (row, col, prop) {
  
      return cellfunction(tableType, container, row, col, prop);
    }
    });

}

function deleteid(stemid, entry, thispage){
  // console.log(entry);
  if(confirm('確定刪除 '+stemid+' 新增樹資料??')) 
  {
    $('.datasavenote').html('');
      $.ajax({
      url: `/ssPlotdeletedata/${stemid}/${entry}/${plotType}/${thispage}`,
      type: 'get',
      success: function (res) {
        // console.log(res);
        if (res.result === 'ok') {
          console.log('Data saved');
          console.log(res);
          if (res.datasavenote !=''){
            $('.datasavenote').html(res.datasavenote);
          }
          ssdatatableupdate(res.recruit, thispage, ppsall);
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


function alternote(stemid, entry, thispage) {
  // console.log(stemid);
  $('.altersavenote').html('');

  var posX = $("button[name='alternoteshow"+stemid+"']").offset().left;
  var posY = $("button[name='alternoteshow"+stemid+"']").offset().top;
  // console.log(posX+", "+posY);

  $('.alternotetalbeouter').css('top', posY);
  $('.alternotetalbeouter').css('left', posX-550);
  
// $(".deletealternotebutton").removeAttr("stemid thispage");

  $('.alternotetalbeouter').show();
  $('.alterstemid').html(stemid);

  $.ajax({
    url: `/ssPlotalternote/${stemid}/${entry}/${plotType}/${thispage}`,
    type: 'get',
    success: function (res) {
      if (res.result === 'ok') {
        console.log('Data show');
        alternotetable(res.alterdata,stemid, entry, thispage);

    if (res.havedata=='yes'){
      $('.deletealternotebutton').show();
      $('.deletealternotebutton').attr({'stemid': stemid,  'thispage': thispage});
      
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
  const thispage = $(button).attr('thispage');
  deletealternote(stemid, plotType, thispage);  
}

function deletealternote(stemid, plotType, thispage){
    if(confirm('確定刪除 '+stemid+' 特殊修改??')) 
    {
      $('.altersavenote').html('');
        $.ajax({
        url: `/ssPlotdeletealter/${stemid}/${entry}/${plotType}/${thispage}`,
        type: 'get',
        success: function (res) {
          // console.log(res);
          if (res.result === 'ok') {
            console.log('Data saved');
            // console.log(res);
            if (res.datasavenote !=''){
              $('.altersavenote').html(res.datasavenote);
            }
            ssdatatableupdate(res.data, res.thispage, ppsall);

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

function processDataTable(data, thispage, pps, site) {
  // 分頁
  var totalpage = Math.ceil(data.length / pps);

  $('.prev').addClass(`prev${site}`);
  $('.next').addClass(`next${site}`);

  if (totalpage > 1) {
    datapage = pages(data, thispage, totalpage, 20);
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




function pages(data, thispage, totalpage, pps){

  if (plotType=='ss10m'){
    var site=`${data[0].plot}${data[0].sqx}${data[0].sqy}`;
  } else {
    var site=`${data[0].qx}${data[0].qy}${data[0].sqx}${data[0].sqy}`;
  }
    

    start=pps*(thispage-1);
    end=start+pps;
    // console.log(thispage);
    data2=data.slice(start, end);
    $('.pages').css('display', 'flex');
    $('.pagenote').html('第 '+thispage+' ／ '+totalpage+' 頁');
    $('.prev').attr('thispage', thispage);
    $('.next').attr('thispage', thispage);


    // console.log('1');
    if (totalpage>1){
      if (thispage==1){
        $('.prev').hide();
        $('.next').show();
      } else if (thispage==totalpage){
        $('.prev').show();
        $('.next').hide();      
      } else {
        $('.prev').show();
        $('.next').show();
      }
    } else {
      $('.pages').hide();

    }

    $('.prev'+site).off('click').on('click', function() {
      thispage=$(this).attr('thispage');
      gopage=parseInt(thispage)-1;

      ssdatatableupdate(data, gopage, pps);
    })

    $('.next'+site).off('click').on('click', function() {
      thispage=$(this).attr('thispage');
      gopage=parseInt(thispage)+1;
                // console.log(data);
      ssdatatableupdate(data, gopage, pps);

    })

    $('.showall').off('click').on('click', function() {
                // console.log(data);
      
      if (data.length>40){
        ppsall=40;

      } else {
        ppsall=data.length;
        $('.pages').hide();
      }
    
      ssdatatableupdate(data, 1, ppsall);
      // recruittable(data, emptytable, csplist);
    })

      datapage=[data, data2, thispage];

  return datapage;

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

  const layerValidator = (value, callback) => {
    if (['u', 'o', ''].includes(value)) {   //允許1234和空格
      callback(true);
    } else {
      callback(false);
    }
  };