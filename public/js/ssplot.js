//上方選單

$('.listlink').on('click', function(){
	let type=$(this).attr('type');
	if (typeof type!='undefined'){
	location.href=(`/shoushan/plot/${type}`);
	}

})



// 使用
handleHoverEvents('.list4', '.list4inner');
handleHoverEvents('.list6', '.list6inner');



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



var ppsall;


  // 定義成功處理邏輯
function handleSuccessAllTable(res, tableType, handsontable) {
  var noteProperty = `${tableType}savenote`;

  if (res[noteProperty] != '') {
    $(`.${noteProperty}`).html(res[noteProperty]);
  }

  if (tableType === 'envi') {
    if (res.envisavenote != '') {
      $('.envisavenote').html(res.envisavenote);
    }
  } else if (tableType === 'data') {

  } else if (tableType === 'recruit') {
    handsontable.updateData(res.nonsavelist);
    if (res.data.length !== 0) {
      $('.datasavenote').html('');
      ssdatatableupdate(res.data, res.thispage, ppsall);
    }
  } else if (tableType === 'alternote') {
    if (res.datasavenote != '') {
      $('.altersavenote').html(res.datasavenote);
    }
    $('.datasavenote').html('');
    ssdatatableupdate(res.data, res.thispage, ppsall);
    $('.deletealternotebutton').show();
  } else if (tableType === 'addcov') {
    if (res.addcovsavenote != '') {
      $('.addcovsavenote').html(res.addcovsavenote);
    }
    handsontable.updateData(res.nonsavelist);
    if (res.data.length !== 0) {
      sscovtableupdate(res.data);
    }
  } else if (tableType === 'cov') {
    sscovtableupdate(res.data);
  }
}


function ssdatatableupdate(data, thispage, pps){
  
  $('.finishnote').html('');
  if (plotType === 'ss10m'){
    var site=`${data[0].plot}${data[0].sqx}${data[0].sqy}`;
  } else {
    var site=`${data[0].qx}${data[0].qy}${data[0].sqx}${data[0].sqy}`;
  }
  
  var tableType='data';
  dataTableUpdate(data, thispage, pps, plotType, tableType, site);


}

function deleteid(stemid, entry, thispage){  //刪除新增樹資料
  var saveUrl=`/ssPlotdeletedata/${stemid}/${entry}/${plotType}/${thispage}`;
  handleDeleteid(stemid,  saveUrl)
}


function alternote(stemid, entry, thispage, event) {
  // console.log(stemid);
    var saveUrl=`/ssPlotalternote/${stemid}/${entry}/${plotType}/${thispage}`;
    handleAlternote(stemid, entry, thispage, saveUrl);
}


function deletealternote(stemid, plotType, thispage){
  var saveUrl=`/ssPlotdeletealter/${stemid}/${entry}/${plotType}/${thispage}`;
  handleDeleteAlternote(stemid, plotType, saveUrl)
}



