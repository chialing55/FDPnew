//上方選單

$('.listlink').on('click', function(){
	let type=$(this).attr('type');
	console.log(type);
	if (typeof type!='undefined'){
	location.href=(`/fushan/tree/${type}`);
	}

})

  function toggleTip(element) {
    $('.tip').toggle();
    $('.tiptriangle').toggleClass('tiptriangletoggled');
  }


// 使用
handleHoverEvents('.list4', '.list4inner');
handleHoverEvents('.list6', '.list6inner');


//download record
	$(".button1").click(function(){
		let qx = $("select[name='qx']").val();
		let qy = $("select[name='qy']").val();
// console.log(qx, qy);
		// var tempwindow1=window.open('_blank');
		if (qx!='' && qy!=''){
			let url='/fstree-record-pdf/'+qx+'/'+qy+'/1';
			window.open(url);
		}
	});

	

//download record 全線
$(".button2").click(function(){

$("#downloadMessage").text("下載中...").show();

let totalRequests = 25;  // 請求的總數量
let completedRequests = 0;  // 已完成的請求數量
		 
for (let i = 0; i < totalRequests; i++) {
  let qx = $("select[name='qx2']").val();
  let qy = i;
  let url = `/fstree-record-pdf/${qx}/${qy}/2`;
	$("#downloadMessage2").text("載入中...").show();
  $.ajax({
    url: url,
    method: 'GET',
    // xhrFields: {
    //   responseType: 'blob'
    // },
    
    success: function (data) {
    	console.log(data);

      $("#downloadMessage").append(`(${qx}, ${qy})`);
      if (i === (totalRequests-1)) {
        // 顯示下載完成訊息
        $("#downloadMessage").append("下載完成");
      }

    },
    complete: function () {
      // 隱藏載入中訊息
      completedRequests++;  // 增加已完成的請求數量
      if (completedRequests === totalRequests) {
        // 所有請求都已完成，隱藏載入中訊息
        $("#downloadMessage2").hide();
      }
    }


  });

	}
	
	});



var ppsall;
var plotType='fstree';
window.addEventListener('data', event => {

	data=event.detail.record;
	emptytable=event.detail.emptytable;
	csplist=event.detail.csplist;
	realemptytable = deepCopy(emptytable);

  $(".save2").off();
	$('.finishnote').html();

  fstreetable(data, 1, 20);
  recruittable(data, emptytable, csplist);

});

function handleSuccessAllTable(res, tableType, handsontable) {
  var noteProperty = `${tableType}savenote`;

  if (res[noteProperty] != '') {
    $(`.${noteProperty}`).html(res[noteProperty]);
  }

  if (tableType === 'data') {

  } else if (tableType === 'recruit') {
    handsontable.updateData(res.nonsavelist);
    if (res.data.length !== 0) {
      $('.datasavenote').html('');
      fstreetableupdate(res.data, res.thispage, ppsall);
    }
  } else if (tableType === 'alternote') {
    if (res.datasavenote != '') {
      $('.altersavenote').html(res.datasavenote);
    }
    $('.datasavenote').html('');
		fstreetableupdate(res.data, res.thispage, ppsall);
		$('.deletealternotebutton').show();
  } else if (tableType==='addTreeData'){
    handsontable.updateData(res.nonsavelist);
    if (res.recruitsavenote != '') {
      $(`.recruitsavenote`).html(res.recruitsavenote);
    }

  }
}

function cellfunction(tableType, container, row, col, prop){
	var cellProperties = {};
      if (tableType=='data'){

          if (container.handsontable('getData')[row][8]=='-9'){
            cellProperties.readOnly = false; 
            if (col==1 || col ==2 || col ==5 || col ==6 || col == 8 || col == 14){
              cellProperties.readOnly = true; 
            }
          }
//note字變小
          if (col == 12 || col==14){
            cellProperties.className = 'fs08'; 
          }
         return cellProperties;
      } else if (tableType=='updateData1'){
      	  if (container.handsontable('getData')[row][5]!='0'){
          	cellProperties.readOnly = true; 
	          	if (col==5 || col==4){
	          		cellProperties.readOnly = false; 
	          	}
          }
          return cellProperties;
      } else if (tableType=='updateData2'){
          if (col == 7 || col==9){
          	cellProperties.className = 'fs08'; 
          }
         return cellProperties;
      }
}


function alternote(stemid, entry, thispage, event) {
	// console.log(stemid);
    var saveUrl=`/fstreeaddalternote/${stemid}/${entry}/${thispage}`;
    handleAlternote(stemid, entry, thispage, saveUrl);

}

function deletealternote(stemid, thispage){

  var saveUrl=`/fstreedeletealter/${stemid}/${entry}/${thispage}`;
  handleDeleteAlternote(stemid, plotType, saveUrl)

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
      {data: "qx", type: 'numeric'},
      {data: "qy", type: 'numeric'},
      {data: "sqx", type: 'numeric'},
      {data: "sqy", type: 'numeric'},
      {data: "tag"},
      {data: "b", type: 'numeric'},
      {data: "csp", type: 'autocomplete', source: csplist, strict: false, visibleRows: 10, allowInvalid: false,},
      {data: "dbh(<1)", type: 'numeric'},
      {data: "pom"},
      {data: "other"},
      {data: "stemid"}

    ];

  var colWidths=[25,25,25,25,80, 35, 120,55,55,100];
  var colHeaders=["20x","20y","5x","5y", "tag", "b", "csp", "dbh(<1)" ,"原POM","其他", "stemid"];

  var hiddenColumns ={
      columns: [10],
    };
  return createHandsontable(container, columns, alterdata, saveButtonName, "/fstreesavealternote", tableType, colWidths, hiddenColumns, colHeaders, thispage );  

}


function deleteid(stemid, entry, thispage){  //刪除新增樹資料
  var saveUrl=`/fstreedeletedata/${stemid}/${entry}/${thispage}`;
  handleDeleteid(stemid,  saveUrl)
}



function fstreetable(data, thispage, pps){

  $('.finishnote').html();
  $('.totalnum').html(`共有 ${data.length} 筆資料。`);
  var site=`${data[0].qx}${data[0].qy}${data[0].sqx}${data[0].sqy}`;
  var container = $(`#datatable${site}`);
  $(`button[name=datasave${site}]`).off();
  var saveButtonName=`datasave${site}`;
  var tabletype='data';
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
      {data: "status", type: 'dropdown', source: ['', '0', '-1', '-2', '-3'], allowInvalid: false},
      {data: "code"},
      {data: "dbh", type: 'numeric', allowInvalid: false},

      {data: "pom", type: 'numeric', allowInvalid: false},
      {data: "note"},
      {data: "confirm", type: 'checkbox', checkedTemplate: '1', uncheckedTemplate: ''},
      {data: "alternotetable", renderer: "html", readOnly: true},
      {data: "update_id"}

    ];

  var colWidths=[120, 25,25,25,25,80, 40, 120,50,50,60,50,160,50, 160];
  var colHeaders=["Date","20x","20y","5x","5y", "tag", "b", "csp",'status', "code","dbh/h高","POM","note","縮水",""];

  var hiddenColumns ={
      columns: [15],
    };

  return createHandsontable(container, columns, data2, saveButtonName, "/fstreesavedata", tabletype, colWidths, hiddenColumns, colHeaders, thispage );
}


function fstreetableupdate(data, thispage, pps){
	$('.finishnote').html('');
  var site=`${data[0].qx}${data[0].qy}${data[0].sqx}${data[0].sqy}`;


  var tableType='data';
  dataTableUpdate(data, thispage, pps, plotType, tableType, site);
}




function recruittable(data, emptytable, csplist){
// console.log(csplist);
// console.log(csplist);
  var site=`${data[0].qx}${data[0].qy}${data[0].sqx}${data[0].sqy}`;
  var thispage=Math.ceil(data.length/20); //指定新增後前往最後一頁
 
	$(`button[name=recruitsave${site}]`).off();
  var container = $(`#recruittable${site}`);

  var saveButtonName=`recruitsave${site}`;
  var tableType='recruit';

  var columns = [
      {data: "date", dateFormat: 'YYYY-MM-DD', type: 'date', allowInvalid: false},
      {data: "qx", type: 'numeric', readOnly: true},
      {data: "qy", type: 'numeric', readOnly: true},
      {data: "sqx", type: 'numeric', allowInvalid: false, validator: qqValidator4},
      {data: "sqy", type: 'numeric', allowInvalid: false, validator: qqValidator4},
      {data: "tag"},
      {data: "branch", type: 'numeric', allowInvalid: false},
      {data: "csp", type: 'autocomplete', source: csplist, strict: true, visibleRows: 10, allowInvalid: false,},
      {data: "code"},
      {data: "dbh", type: 'numeric', allowInvalid: false},
      {data: "pom", type: 'numeric', allowInvalid: false},
      {data: "note"},
      {data: "tofix", type: 'checkbox', checkedTemplate: '1', uncheckedTemplate: ''}

    ];

  var colWidths=[120, 25,25,25,25,80, 40, 120,50,60,70,160,50];
  var colHeaders=["Date","20x","20y","5x","5y", "tag", "b", "csp", "code","dbh/h高","POM/h低","note","漏資料"];

  var hiddenColumns =[];
  return createHandsontable(container, columns, emptytable, saveButtonName, "/fstreesaverecruit", tableType, colWidths, hiddenColumns, colHeaders, thispage );

}


  function finish(qx, qy, entry){

  	console.log(qx, qy, entry);

    var saveUrl=`/fstreefinish/${qx}/${qy}/${entry}`;
    var ajaxData={};
    var ajaxType='get';

    function handleSuccess(res) {
          if (res.finishnote !=''){
            $('.finishnote').html(res.finishnote);
          }
    }
    makeAjaxRequest(
      saveUrl, ajaxData, ajaxType,
      handleSuccess,
      function () {}
    );

  }


///調查進度

var value=[];


$('.canselect').click(function(){
		i=$(this).attr('i');
		j=$(this).attr('j');
		MouseDownSite(i,j);
})


function MouseDownSite(sqx, sqy) {
    const element = $(event.target); // 使用 event.target 來取得被點擊的元素
    const show = element.attr('show');
    // console.log(show);
    if (show == 1) {
        element.attr('show', '0');
        element.addClass('plotunselect');
        element.removeClass('plotselect');
        //移除(sqx, sqy)
        value = $.grep(value, function(item) {
				  return item.sqx === sqx && item.sqy === sqy;
				}, true);
    } else {
        element.attr('show', '1');
        element.addClass('plotselect');
        element.removeClass('plotunselect');
        value.push([ sqx, sqy ]);

				value.sort(function(a, b) {
				  // 先按照 sqx 欄位進行排序
				  if (a < b) {
				    return -1;
				  }
				  if (a > b) {
				    return 1;
				  }
				  // 如果 sqx 相同，則按照 sqy 欄位進行排序
				  if (a < b) {
				    return -1;
				  }
				  if (a > b) {
				    return 1;
				  }
				  return 0;  // 如果 sqx 和 sqy 都相同，保持原始順序
				});
    }
 			Livewire.emit('updateValue', value);
}





window.addEventListener('reProgress', event => {

	plots=event.detail.plots;
	$('.canselect').off();
  canselectchcek(plots);
  value=[];

  $("#progressTable").trigger("updateAll");

});



window.addEventListener('rePlots', event => {
  plots=event.detail.plots;
  $('.canselect').off();
  canselectchcek(plots)


});

window.addEventListener('rePlotsentry', event => {
  const sqx=event.detail.sqx;
  const sqy=event.detail.sqy;
  $('.plottable2').removeClass('selected');
  $('.plot'+sqx+sqy).addClass('selected');
  // canselectchcek(plots)

});

function canselectchcek(plots){

	$('.plottable').attr('show', '0').removeClass('plotselect').removeClass('cannotselect').addClass('canselect');

	

	plots.forEach(function(element) {

			xy=`${element[0]}${element[1]}`;
			$('.plot'+xy).removeClass('plotselect').removeClass('canselect').addClass('cannotselect');

	    console.log(xy);  // 輸出每個元素
	});

$('.canselect').click(function(){
		i=$(this).attr('i');
		j=$(this).attr('j');
		MouseDownSite(i,j);
})
	// console.log(plots);

}




//進行個別修改 //資料處理，後端資料更正

window.addEventListener('stemiddata', event => {

	stemid=event.detail.stemid;
	stemdata=event.detail.stemdata;
	csplist=event.detail.csplist;
	from=event.detail.from;
	console.log(stemid);
    fstreeupdatatable(stemid, stemdata, csplist, from);

});

Livewire.on('updateStemidlist', function(data) {
    // 更新 Livewire 组件中的数组
    Livewire.emit('updateStemidList', data);
});

//後端資料更正

function fstreeupdatatable(stemid, stemdata, csplist, from){

//basetable
	stemid = stemid.replace('.', ''); // Remove the period

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

    ];

  var colWidths1=[25,25,25,25,80, 40, 120];
  var colHeaders1=["20x","20y","5x","5y", "tag", "b", "csp"];

  var hiddenColumns1 =[];

  var handsontable1=createHandsontable(container1, columns1, stemdata[0], saveButtonName, "/fstreeupdate", tabletype1, colWidths1, hiddenColumns1, colHeaders1, 1 );

	var stemdata2=stemdata.slice(1, 6);

  var container2 = $(`#datatable${stemid}`);
  var saveButtonName=`basetable${stemid}`;
  var tabletype2='updateData2';


  var columns2 = [
    	{data: "census", readOnly:true},
      {data: "date", dateFormat: 'YYYY-MM-DD', type: 'date', allowInvalid: false},
      {data: "status", type: 'dropdown', source: ['', '0', '-1', '-2', '-3'], allowInvalid: false},
      {data: "code"},
      {data: "dbh", type: 'numeric'},
      {data: "h高", type: 'numeric'},

      {data: "pom"},
      {data: "note"},
      {data: "confirm", type: 'checkbox', checkedTemplate: '1', uncheckedTemplate: ''},
      {data: "alternote"},

    ];

  var colWidths2=[80,120, 50,50,50,50,50,170,50, 170];
  var colHeaders2=["census","date",'status', "code","dbh","h高","POM","note","縮水","特殊修改"];

  var hiddenColumns2 =[];

  var handsontable2=createHandsontable(container2, columns2, stemdata2, saveButtonName, "/fstreeupdate", tabletype2, colWidths2, hiddenColumns2, colHeaders2, 1 );


  var handsontable1 = container1.data('handsontable');
  var handsontable2 = container2.data('handsontable');

	container2.parent().find(`button[name=datasave${stemid}]`).click(function () {
		$('.datasavenote').html('');

		  var data1 = handsontable1.getSourceData();
  		var data2 = handsontable2.getSourceData();

      var saveUrl=`/fstreeupdate`;
      var ajaxData={
		    	data1: data1,
		    	data2: data2,
		    	from: from,
		    	user: user
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

      var saveUrl=`/fstreedeletecensusdata`;
      var ajaxData={
      			stemid: stemid,
		    		from: from,
		    		// stemid: stemid,
		    		user: user
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

//資料處理=>新增資料

window.addEventListener('updata', event => {
  emptytable_addtree=event.detail.emptytable;
  csplist=event.detail.csplist;
  realemptytable_addtree = deepCopy(emptytable_addtree);
  updatelist=event.detail.updatelist;
  // console.log('1');
  // $('.updataTableOut').show();
  addDataTable(emptytable_addtree, csplist);

});

  const qxValidator = (value, callback) => {
    if (updatelist.includes(value)) {   //
      callback(true);
    } else {
      callback(false);
    }
  };


function addDataTable(emptytable, csplist){
// console.log(csplist);
// console.log(csplist);
  var entry=3;

 var thispage='1';
  $(`button[name=recruitsave]`).off();
  var container = $(`#recruittable`);

  var saveButtonName=`recruitsave`;
  var tableType='addTreeData';

  var columns = [
      {data: "date", dateFormat: 'YYYY-MM-DD', type: 'date', allowInvalid: false},
      {data: "qx", type: 'numeric', validator: qxValidator},
      {data: "qy", type: 'numeric', validator: qxValidator},
      {data: "sqx", type: 'numeric', allowInvalid: false, validator: qqValidator4},
      {data: "sqy", type: 'numeric', allowInvalid: false, validator: qqValidator4},
      {data: "tag"},
      {data: "branch", type: 'numeric', allowInvalid: false},
      {data: "csp", type: 'autocomplete', source: csplist, strict: true, visibleRows: 10, allowInvalid: false,},
      {data: "code"},
      {data: "dbh", type: 'numeric', allowInvalid: false},
      {data: "pom", type: 'numeric', allowInvalid: false},
      {data: "note"},

    ];

  var colWidths=[120, 30,30,25,25,80, 40, 120,50,60,70,160];
  var colHeaders=["Date","20x","20y","5x","5y", "tag", "b", "csp", "code","dbh/h高","POM/h低","note"];

  var hiddenColumns =[];
  return createHandsontable(container, columns, emptytable, saveButtonName, "/fstreeadddata", tableType, colWidths, hiddenColumns, colHeaders, thispage );

}