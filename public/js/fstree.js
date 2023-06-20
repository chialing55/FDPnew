

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
	location.href=(`/fushan/tree/${type}`);
	}

})


$('.list4, .list4inner').on('mouseenter', function() {
  $('.list4inner').css('display', 'inline-flex');
  $('.list4').addClass('listhover'); 
  $('.now hr').css('color', 'transparent');
}).on('mouseleave', function() {
  $('.list4inner').hide();
  $('.list4').removeClass('listhover');
  $('.now hr').css('color', '#91A21C');
});

$('.list6, .list6inner').on('mouseenter', function() {
  $('.list6inner').css('display', 'inline-flex');
  $('.list6').addClass('listhover');
  $('.now hr').css('color', 'transparent');
}).on('mouseleave', function() {
  $('.list6inner').hide();
  $('.list6').removeClass('listhover');
  $('.now hr').css('color', '#91A21C');
});


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


// $(document).ready(function() {
//     adjustFooterPosition();
// });

// $(document).ajaxComplete(function() {
//     adjustFooterPosition();
// });

// function adjustFooterPosition() {
//     var windowHeight = $(window).height();
//     var bodyHeight = $('body').height();

//     if (bodyHeight < windowHeight) {

//         $('.footer').css('position', 'relative').css('bottom', 0);
//     }
// }

window.addEventListener('data', event => {

	data=event.detail.record;
	emptytable=event.detail.emptytable;
	csplist=event.detail.csplist;
	// entry=event.detail.entry;
    // data=event.detail;
	// console.log(data);
	// console.log(slroll);
	// console.log(emptytable);
    // $('#slrolltable').html('');
    $(".save2").off();


    // fscovtable(covs);
    // //一開始,thispage=1

    fstreetable(data, 1);

    recruittable(data, emptytable, csplist);
    // fsslrolltable(slroll, covs);
	// return(entry, user);
});








function alternote(stemid, entry, thispage) {
	// console.log(stemid);
	$('.altersavenote').html('');

	var posX = $("button[name='alternoteshow"+stemid+"']").offset().left;
    var posY = $("button[name='alternoteshow"+stemid+"']").offset().top;
    console.log(posX+", "+posY);

    $('.alternotetalbeouter').css('top', posY);
    $('.alternotetalbeouter').css('left', posX-500);
	
// $(".deletealternotebutton").removeAttr("stemid thispage");

	$('.alternotetalbeouter').show();
	$('.alterstemid').html(stemid);

		  $.ajax({
		    url: "/fstreeaddalternote/"+stemid+"/"+entry+"/"+thispage,
		    type: 'get',
		    success: function (res) {
		      if (res.result === 'ok') {
		        console.log('Data saved');
				alternotetable(res.alterdata,stemid, entry, res.thispage);

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
  deletealternote(stemid, thispage);	
}


function deletealternote(stemid, thispage){
    if(confirm('確定刪除 '+stemid+' 特殊修改??')) 
    {
      $('.altersavenote').html('');
        $.ajax({
        url: `/fstreedeletealter/${stemid}/${entry}/${thispage}`,
        type: 'get',
        success: function (res) {
        	// console.log(res);
          if (res.result === 'ok') {
            console.log('Data saved');
            // console.log(res);
            if (res.datasavenote !=''){
              $('.altersavenote').html(res.datasavenote);
            }
            fstreetableupdate(res.data, res.thispage);

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



function alternotetable(alterdata, stemid, entry, thispage){
	$("button[name=alternotesave]").off();
  var container = $("#alternotetable");
  var container = $("#alternotetable");
	container.handsontable('destroy');
	$('.deletealternotebutton').attr({'stemid': stemid,  'thispage': thispage});

  var parent = container.parent();
  var cellChanges = [];
  container.handsontable({
  	data: alterdata,
    // height: 320,
    startRows: 1,
    colHeaders: true,
    removeRowPlugin: true,
    // minSpareRows: 1,
    colWidths: [25,25,25,25,80, 40, 120,50],
    licenseKey: 'non-commercial-and-evaluation',
 
    colHeaders: ["20x","20y","5x","5y", "tag", "b", "csp","POM", "stemid"],
    columns: [
      {data: "qx", type: 'numeric'},
      {data: "qy", type: 'numeric'},
      {data: "sqx", type: 'numeric'},
      {data: "sqy", type: 'numeric'},
      {data: "tag"},
      {data: "b", type: 'numeric'},
      {data: "csp", type: 'autocomplete', source: csplist, strict: false, visibleRows: 10, allowInvalid: false,},
      {data: "pom"},
      {data: "stemid"}
    ],
    currentRowClassName: 'currentRow',
    autoWrapRow: true,   //自動換行
  	manualColumnResize: true,
  	hiddenColumns: {
    // specify columns hidden by default
   		columns: [8],
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
		    url: "/fstreesavealternote",
		    data: {
		    	data: handsontable.getSourceData(),
		    	entry: entry,
		    	thispage: thispage

		    }, 
		    type: 'POST',
		    success: function (res) {
		      if (res.result === 'ok') {
		        console.log('Data saved');
		        if (res.datasavenote !=''){
		        	$('.altersavenote').html(res.datasavenote);
		        } 
		        console.log(res);
		        fstreetableupdate(res.data, res.thispage);
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



  function deleteid(stemid, entry, thispage){
    // console.log(entry);
    if(confirm('確定刪除 '+stemid+' 新增樹資料??')) 
    {
      $('.datasavenote').html('');
        $.ajax({
        url: `/fstreedeletedata/${stemid}/${entry}/${thispage}`,
        type: 'get',
        success: function (res) {
        	// console.log(res);
          if (res.result === 'ok') {
            console.log('Data saved');
            // console.log(res);
            if (res.datasavenote !=''){
              $('.datasavenote').html(res.datasavenote);
            }
            fstreetableupdate(res.recruit, res.thispage);
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


function fstreetable(data, thispage){

	// datapage=pages(data, thispage);
	// console.log(datapage);
	// console.log(entry, user);

	var site=data[0].qx+data[0].qy+data[0].sqx+data[0].sqy;
	$('.totalnum').html(`共有 ${data.length} 筆資料`);

// 分頁
	var totalpage=Math.ceil(data.length/20);
	// console.log(data);
		$('.prev').addClass('prev'+site);
		$('.next').addClass('next'+site);

	if (totalpage>1){
		datapage=pages(data, thispage, totalpage);
		var data2=datapage[1];
	} else {
		var data2=data;
	}

// console.log(data);  
	// recruittable(data);
  var container = $("#datatable"+site);
  var parent = container.parent();
  var cellChanges = [];
  container.handsontable({
    data: data2,
    // height: 320,
    // startRows: 3,
    colHeaders: true,
    rowHeaders: true,
    rowHeaderWidth: 25,
    rowHeights: 38,
    removeRowPlugin: true,
    // minSpareRows: 1,
    colWidths: [120, 25,25,25,25,80, 40, 120,50,50,60,50,160,50,50, 160],
    licenseKey: 'non-commercial-and-evaluation',
 
    colHeaders: ["Date","20x","20y","5x","5y", "tag", "b", "csp",'status', "code","dbh/h高","POM","note","縮水","查舊",""],
    columns: [
      {data: "date", dateFormat: 'YYYY-MM-DD', type: 'date', allowInvalid: false},
      {data: "qx", readOnly: true},
      {data: "qy", readOnly: true},
      {data: "sqx", readOnly: true},
      {data: "sqy", readOnly: true},
      {data: "tag", readOnly: true},
      {data: "branch", readOnly: true},
      {data: "csp", readOnly: true, type: 'autocomplete', source: csplist, strict: false, visibleRows: 10, allowInvalid: false,},
      {data: "status", type: 'dropdown', source: ['', '0', '-1', '-2', '-3'], allowInvalid: false},
      {data: "code"},
      {data: "dbh", type: 'numeric', allowInvalid: false},

      {data: "pom"},
      {data: "note"},
      {data: "confirm", type: 'checkbox', checkedTemplate: '1', uncheckedTemplate: ''},
      {data: "tocheck", type: 'checkbox', checkedTemplate: '1', uncheckedTemplate: ''},
      {data: "alternotetable", renderer: "html"},
    ],
    currentRowClassName: 'currentRow',
    autoWrapRow: true,   //自動換行
  	manualColumnResize: true,
  	// manualRowResize: true,
    cells: function (row, col, prop) {
	
          var cellProperties = {};
          // var curData = container.handsontable('getData')[row][10]; //column 10 is the field "sprout"
          if (container.handsontable('getData')[row][8]=='-9'){
          	cellProperties.readOnly = false; 
          	if (col==1 || col ==2){
          		cellProperties.readOnly = true; 
          	}
          }

          // if (col == 11 || col==12) {            //column needs to be read only               
          //  //if status is Active
		// 	if (curData=== 'TRUE') { 
          //       cellProperties.readOnly = true; 
          //   }
          // }
//note字變小
          if (col == 12 || col==15){
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

	parent.find('button[name=datasave'+site+']').click(function () {
		$('.datasavenote').html('');

		$.ajaxSetup({
			  headers: {
			    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			  }
			});

		  $.ajax({
		    url: "/fstreesavedata",
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
		        	// console.log(res.datasavenote);
		        	// container.render();
		        } 
		        // console.log(res);
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


function pages(data, thispage, totalpage){

	// $(".prev").unbind();
	// $(".next").unbind();
		site=data[0].qx+data[0].qy+data[0].sqx+data[0].sqy;

		start=20*(thispage-1);
		end=start+20;
		// console.log(thispage);
		data2=data.slice(start, end);
		$('.pages').css('display', 'flex');
		$('.pagenote').html('第 '+thispage+' ／ '+totalpage+' 頁');
		$('.prev').attr('thispage', thispage);
		$('.next').attr('thispage', thispage);


		// console.log('1');

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

		$('.prev'+site).off('click').on('click', function() {
			thispage=$(this).attr('thispage');
			gopage=parseInt(thispage)-1;

			fstreetableupdate(data, gopage);
		})

		$('.next'+site).off('click').on('click', function() {
			thispage=$(this).attr('thispage');
			gopage=parseInt(thispage)+1;
								// console.log(data);
			fstreetableupdate(data, gopage);
		})

			datapage=[data, data2, thispage];

	return datapage;

}





function fstreetableupdate(data, thispage){
    var site=data[0].qx+data[0].qy+data[0].sqx+data[0].sqy;
	$('.datasavenote').html('');
	var container = $("#datatable"+site);
	var handsontable = container.data('handsontable');
	// console.log(data);
	var totalpage=Math.ceil(data.length/20);
	$('.totalnum').html(`共有 ${data.length} 筆資料`);

	var data3 = (totalpage > 1) ? pages(data, thispage, totalpage)[1] : data;

	
// console.log(data3);
	handsontable.updateData(data3, thispage);
	handsontable.updateSettings({
		cells: function (row, col, prop) {
	
          var cellProperties = {};
          // var curData = container.handsontable('getData')[row][10]; //column 10 is the 
          if (container.handsontable('getData')[row][8]=='-9'){
          	cellProperties.readOnly = false; 
          	if (col==1 || col ==2){
          		cellProperties.readOnly = true; 
          	}
          }

          if (col == 12 || col == 15){
          	cellProperties.className = 'fs08'; 
          }
         return cellProperties;
		}
    });

    Livewire.emit('updateAmount', data.length);

}




function recruittable(data, emptytable, csplist){
// console.log(csplist);
// console.log(csplist);
  var site=data[0].qx+data[0].qy+data[0].sqx+data[0].sqy;
 
$(`button[name=recruitsave${site}]`).off();
  var container = $("#recruittable"+site);
  var parent = container.parent();
  // var emptytable=emptytable;

	const qqValidator = (value, callback) => {
		if ([1, 2, 3, 4, ''].includes(value)) {   //允許1234和空格
		  callback(true);
		} else {
		  callback(false);
		}
	};

  container.handsontable({
    // data: emptytable,
    dataSchema: {
      qx: data[0].qx,
      qy: data[0].qy,
      date: '',
      sqx: '',
      sqy: '',
      tag: '',
      branch: '0',
      csp: '',
      code: '',
      dbh: '',
      pom: '1.3',
      note: '',
      tofix: ''
    },
    startRows: 20,
    colHeaders: true,
    rowHeaders: true,
    rowHeaderWidth: 25,
    contextMenu: ['row_above', 'row_below', 'remove_row'],
    // minSpareRows: 1,
    rowHeights: 35,
    colWidths: [120, 25,25,25,25,80, 40, 120,50,60,60,160,50],
    licenseKey: 'non-commercial-and-evaluation',
 
    colHeaders: ["Date","20x","20y","5x","5y", "tag", "b", "csp", "code","dbh/h高","pom/h低","note","漏資料"],
    columns: [
      {data: "date", dateFormat: 'YYYY-MM-DD', type: 'date', allowInvalid: false},
      {data: "qx", type: 'numeric', readOnly: true},
      {data: "qy", type: 'numeric', readOnly: true},
      {data: "sqx", type: 'numeric', allowInvalid: false, validator: qqValidator},
      {data: "sqy", type: 'numeric', allowInvalid: false, validator: qqValidator},
      {data: "tag"},
      {data: "branch", type: 'numeric'},
      {data: "csp", type: 'autocomplete', source: csplist, strict: false, visibleRows: 10, allowInvalid: false,},
      {data: "code"},
      {data: "dbh", type: 'numeric', allowInvalid: false},
      {data: "pom", type: 'numeric', allowInvalid: false},
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
		    url: "/fstreesaverecruit",
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
				fstreetableupdate(res.data, 1);
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