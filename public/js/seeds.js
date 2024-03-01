// console.log(type);
//重新選擇工作項目


$('.listlink').on('click', function(){
	let type=$(this).attr('type');
	console.log(type);
	if (typeof type!='undefined'){
		if (type=='websplist'){
			window.open('/web/splist', '_blank');
		} else {
			location.href=(`/fushan/seeds/${type}`);
		}
	
	}

})


$("#sptable").tablesorter();



// window.addEventListener('resptable', event => {

//   $("#sptable").trigger("updateAll");

// });




// 使用 //上方表單
handleHoverEvents('.list4', '.list4inner');
handleHoverEvents('.list6', '.list6inner');

var plotType='fsseeds';
var thispage=1;
var entry=1;

window.addEventListener('data', event => {

	$('.entrytableout').show();
	$('.keepenter').hide();
	$('.dateinfo').hide();
	fdata=event.detail.record;
	emptytable=event.detail.emptytable;
	census=event.detail.census;
	csplist=event.detail.csplist;
	realemptytable = deepCopy(emptytable);
	// console.log(emptytable);
	// console.log(fdata);
	if (fdata.length>0){
		console.log('1');
		$('#seedstableout').show();
		$('#seedstableout_empty').hide();

		seedstable(fdata, 1, 29, emptytable)
		emptyseedstable(emptytable);
		
	} else {
		$('#seedstableout').hide();
		$('#seedstableout_empty').show();
		seedstable(fdata, 1, 29, emptytable)
		emptyseedstable(emptytable);
	}

});


function handleSuccessAllTable(res, tableType, handsontable) {
  var noteProperty = `${tableType}savenote`;

  if (res[noteProperty] != '') {
    $(`.${noteProperty}`).html(res[noteProperty]);
  }

  if (tableType === 'addseedsdata') {
        if (res.seedssavenote !=''){
        	$('.seedssavenote').html(res.seedssavenote);
        }
        // console.log(emptytable);
        emptytable2=deepCopy(realemptytable);
        console.log(emptytable2);
        handsontable.updateData(emptytable2);

        totalpage=Math.ceil(res.data.length/29);

    $('#seedstableout').show();
    $('#seedstableout_empty').hide();

        fsseedstableupdate(res.data, totalpage, 29);
        fdata=res.data;
  } else if (tableType=='data'){
        if (res.seedssavenote !=''){
        	$('.seedssavenote').html(res.seedssavenote);
        }
        fsseedstableupdate(res.data, res.thispage, 29);
        // console.log(thispage);
        fdata=res.data;  	
  }
}

  const codeValidator = (value, callback) => {
    if ([1, 2, 3, 4, 5, 6,  ''].includes(value)) {   //允許1234和空格
      callback(true);
    } else {
      callback(false);
    }
  };

  const sexValidator = (value, callback) => {
    if (['F', 'M', 'MF', ''].includes(value)) {   //允許1234和空格
      callback(true);
    } else {
      callback(false);
    }
  };

function cellfunction(tableType, container, row, col, prop){
  var cellProperties = {};
 		if (tableType=='data'){
          var curData = container.handsontable('getData')[row][12]; //column 12 is the field "check"

          if (curData!= ''){
          	cellProperties.className = 'text-red-500'; 
          }
         return cellProperties;
 		}
}

function emptyseedstable(emptytable){

// console.log(entry);
	
  const site=emptytable[0]['census'];
  $(`button[name=newdatasave${site}]`).off();
  var container = $(`#seedstable_empty${site}`);
  var saveButtonName=`newdatasave${site}`;
  var tableType='addseedsdata';
  // var emptytable=emptytable;
  var columns = [
    	{data: "id"},
      {data: "census"},
      {data: "trap", allowInvalid: false},
      {data: "csp", type: 'autocomplete', source: csplist, strict: true, visibleRows: 10, allowInvalid: false},
      {data: "code", type: 'numeric', allowInvalid: false, validator: codeValidator},
      {data: "count", type: 'numeric', allowInvalid: false},
      {data: "seeds"},
      {data: "viability"},
      {data: "fragments", type: 'numeric', allowInvalid: false},
      
      {data: "sex", allowInvalid: false, validator: sexValidator},
      {data: "identifier", type: 'autocomplete', source: ['蔡佳秀', '張楊家豪'], allowInvalid: true, visibleRows: 40},
      {data: "note"},

    ];

  var colWidths=[10, 40, 50, 120, 50, 50,60,50,70,40,100, 160];
  var colHeaders=["id","census","Trap", "種類", "類別", "數量", "種子數","活性","碎片3數量","性別","鑑定者","備註"];

  var hiddenColumns ={
      columns: [0, 1],
    };
  var handsontable = createHandsontable(container, columns, emptytable, saveButtonName, "/fsseedssavedata1/record", tableType, colWidths, hiddenColumns, colHeaders, thispage );


//更新大表
	container.parent().find(`button[name=newdatasave2${site}]`).click(function () {
		$('.seedssavenote').html('');

		saveUrl2='/fsseedssavedata1/fulldata';
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
      saveUrl2, ajaxData, ajaxType,
      function(res) {
        handleSuccessAllTable(res, tableType, handsontable);
      },
      function () {}
    );

	});
	
}


  $('button[name=creattable]').click(function () {
    $('#seedstableout').hide();
    $('#seedstableout_empty').show();
    $('.seedssavenote').html('');

  });
  $('button[name=show_seedstable]').click(function () {
    $('#seedstableout').show();
    $('#seedstableout_empty').hide();
    seedstable(fdata, 1, 29, emptytable);
  });


function seedstable(data, thispage, pps, emptytable){

// console.log(data);
// const census=data[0]['census'];

	totalpage=Math.ceil(data.length/pps);
	$('.totalnum').html(`共有 ${data.length} 筆資料。`);

  var site=emptytable[0]['census'];
  var container = $(`#datatable${site}`);

  var saveButtonName=`datasave${site}`;
  var tabletype='data';
  ppsall=pps;
  var data2 = processDataTable(data, thispage, pps, site, plotType);

  var columns = [
    	{data: "id"},
      {data: "census"},
      {data: "trap",  allowInvalid: false},
      {data: "csp", type: 'autocomplete', source: csplist, strict: true, visibleRows: 10, allowInvalid: false},
      {data: "code", type: 'numeric', allowInvalid: false, validator: codeValidator},
      {data: "count", type: 'numeric', allowInvalid: false},
      {data: "seeds"},
      {data: "viability"},
      {data: "fragments", type: 'numeric', allowInvalid: false},
      
      {data: "sex", allowInvalid: false, validator: sexValidator},
      {data: "identifier", type: 'autocomplete', source: ['蔡佳秀', '張楊家豪'], allowInvalid: true, visibleRows: 40},
      {data: "note"},
      {data: "checknote", readOnly: true},
      {data: "d", readOnly: true, renderer: "html"}

    ];

  var colWidths=[30, 40, 50, 120, 50, 50,60,50,70,40,100, 160, 200,40];
  var colHeaders=["id","census","Trap", "種類", "類別", "數量", "種子數","活性","碎片3數量","性別","鑑定者","備註", "檢查", ""];

  var hiddenColumns ={
      columns: [0, 1],
    };

  var handsontable = createHandsontable(container, columns, data2, saveButtonName, "/fsseedssavedata/record", tabletype, colWidths, hiddenColumns, colHeaders, thispage );



    //更新大表
	container.parent().find(`button[name=datasave2${site}]`).click(function () {




		$('.seedssavenote').html('');

		saveUrl2='/fsseedssavedata1/fulldata';
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
      saveUrl2, ajaxData, ajaxType,
      function(res) {
        handleSuccessAllTable(res, tableType, handsontable);
      },
      function () {}
    );

	});


}


function fsseedstableupdate(data, thispage, pps){


  var site=data[0]['census'];


  var tableType='data';
  dataTableUpdate(data, thispage, pps, plotType, tableType, site);

}

// function emptytableupdate(emptytable3){

// 	// $('#seedstableout').hide();
// 	// $('#seedstableout_empty').show();
// 	var container = $("#seedstable_empty");
// 	var handsontable = container.data('handsontable');

// 	handsontable.updateData(emptytable3);
// 	handsontable.updateSettings({
//     cells: function (row, col, prop) {

//    	 },
//     });


// }

  function deleteid(id, info, thispage, type){
    
    if(confirm('確定刪除 '+info+' 種子雨資料??')) 
    {
      $('.seedssavenote').html('');

      var saveUrl=`/fsseedsdeletedata/${id}/${info}/${thispage}/${type}`;
      var ajaxData={};
      var ajaxType='get';

      function handleSuccess(res) {
            if (res.seedssavenote !=''){
              $('.seedssavenote').html(res.seedssavenote);
            }
		        fsseedstableupdate(res.data, res.thispage, 29);
		        fdata=res.data;
      }
      makeAjaxRequest(
        saveUrl, ajaxData, ajaxType,
        handleSuccess,
        function () {}
      );
    }
  }




function finish(){
    // console.log(entry);

      var saveUrl=`/fsseedsfinish`;
      var ajaxData={};
      var ajaxType='get';

      function handleSuccess(res) {
            if (res.finishnote !=''){
              $('.finishnote').html(res.finishnote);
            } else {
            	location.href='/fushan/seeds/entry';
            }
      }
      makeAjaxRequest(
        saveUrl, ajaxData, ajaxType,
        handleSuccess,
        function () {}
      );
}