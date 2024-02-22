
//download record


	$(".button1").click(function(){
		var start=$("#start").val();
		var end=$("#end").val();

		// var tempwindow1=window.open('_blank');
		location.href='/fsseedling-record-pdf/'+start+'/'+end;
	});


$('.listlink').on('click', function(){
	let type=$(this).attr('type');
	console.log(type);
	if (typeof type!='undefined'){
	location.href=(`/fushan/seedling/${type}`);
	}

})


// 使用
handleHoverEvents('.list4', '.list4inner');
// handleHoverEvents('.list6', '.list6inner');

var plotType='fsseedling';
var thispage=1;


window.addEventListener('initTablesorter', event => {

  tag=event.detail.tag;
  // console.log(tag);
  $(`#progressTable${tag}`).tablesorter();

});


window.addEventListener('data', event => {

	covs=event.detail.covs;
	data=event.detail.record;
	emptytable=event.detail.emptytable;
	maxid=event.detail.maxid;
	slroll=event.detail.slroll;
	csplist=event.detail.csplist;
  realemptytable = deepCopy(emptytable);

    // data=event.detail;
	// console.log(data);
	// console.log(slroll);
	// console.log(emptytable);
    // $('#slrolltable').html('');
    $(".save2").unbind();

    fscovtable(covs);
    //一開始,thispage=1

    if (data[0].tag!='無'){
    fsseedlingtable(data, 1, 20, maxid);}
    recruittable(data, emptytable, csplist);
    fsslrolltable(slroll, covs);

});

function handleSuccessAllTable(res, tableType, handsontable) {
  var noteProperty = `${tableType}savenote`;

  if (res[noteProperty] != '') {
    $(`.${noteProperty}`).html(res[noteProperty]);
  }

  if (tableType === 'data') {
        // handsontable.updateData(res.data);

  } else if (tableType === 'recruit') {
        handsontable.updateData(res.nonsavelist);
        if (res.recruit.length != 0){
          fsseedlingtableupdate(res.recruit, res.thispage, ppsall,  res.maxid);
        }
  } else if (tableType === 'alternote') {
    if (res.datasavenote != '') {
      $('.altersavenote').html(res.datasavenote);
    }
    $('.datasavenote').html('');
    fsseedlingtableupdate(res.data, res.thispage, ppsall,  res.maxid);
    $('.deletealternotebutton').show();
  } else if (tableType === 'roll'){
    fsrolltableupdate(res.data, res.trap);
  }
}

function cellfunction(tableType, container, row, col, prop){
  var cellProperties = {};
      if (tableType=='data'){

          var curData = container.handsontable('getData')[row][10]; //column 10 is 
          if (container.handsontable('getData')[row][16]>maxid){
            cellProperties.readOnly = false; 
            if (col==8 || col == 14){
              cellProperties.readOnly = true; 
            }
          }

          if (col == 11 || col==12) {            //column needs to be read 
          if (curData=== 'TRUE') { 
                cellProperties.readOnly = true; 
            }
          }

          if (col == 13 || col == 14){
            cellProperties.className = 'fs08'; 
          }
          return cellProperties;
      } 
}


function fscovtable(covs){


// console.log(covs);  
// console.log(cov);
  var site=`${covs[0].trap}`;

  var container = $(`#covtable${site}`);

  var saveButtonName=`covsave${site}`;
  var tableType='cov';

  for (let i = 0; i < covs.length; i++) {
    if (covs[i]['date'] === '0000-00-00') {
      covs[i]['date'] = '';
    }
  }

  var columns = [
      {data: "date", dateFormat: 'YYYY-MM-DD', type: 'date'},
      {data: "trap", readOnly: true},
      {data: "plot", readOnly: true},
      {data: "cov", type: 'numeric'},
      {data: "canopy", type: 'dropdown', source: ['U', 'I', 'G'], allowInvalid: false},
      {data: "note"},
      {data: "id"}

    ];

  var colWidths=[120, 50, 50, 80, 120, 200];
  var colHeaders=["Date", "Trap", "Plot", "覆蓋度", "樣區上方光度", "Note"];

  var hiddenColumns ={
      columns: [6],
    };
  return createHandsontable(container, columns, covs, saveButtonName, "/fsseedlingsavecov", tableType, colWidths, hiddenColumns, colHeaders, thispage );

}

  function deleteid(tag, entry, thispage){
    
    if(confirm('確定刪除 '+tag+' 新增小苗資料??')) 
    {
      $('.datasavenote').html('');

      var saveUrl=`/fsseedlingdeletedata/${tag}/${entry}/${thispage}`;
      var ajaxData={};
      var ajaxType='get';

      function handleSuccess(res) {
            if (res.datasavenote !=''){
              $('.datasavenote').html(res.datasavenote);
            }

            fsseedlingtableupdate(res.recruit, res.thispage, ppsall, res.maxid);
      }
      makeAjaxRequest(
        saveUrl, ajaxData, ajaxType,
        handleSuccess,
        function () {}
      );
    }
  }



//data為原始資料
//data1為切割後得資料

function fsseedlingtable(data, thispage, pps, maxid){

	// datapage=pages(data, thispage);
	// console.log(datapage);
  $('.totalnum').html(`共有 ${data.length} 筆資料。`);

	totalpage=Math.ceil(data.length/20);

  var site=`${data[0].trap}`;
  var container = $(`#datatable${site}`);
  $(`button[name=datasave${site}]`).off();
  var saveButtonName=`datasave${site}`;
  var tabletype='data';
  ppsall=pps;
  pps=pps;
  var data2 = processDataTable(data, thispage, pps, site, plotType);

  var columns = [
      {data: "date", dateFormat: 'YYYY-MM-DD', type: 'date', allowInvalid: false},
      {data: "trap", readOnly: true},
      {data: "plot", readOnly: true},
      {data: "tag", readOnly: true},
      {data: "csp", readOnly: true, type: 'autocomplete', source: csplist, strict: false, visibleRows: 10},
      {data: "ht", type: 'numeric', allowInvalid: false},
      {data: "cotno", type: 'numeric', allowInvalid: false},
      {data: "leafno", type: 'numeric', allowInvalid: false},
      {data: "status", type: 'dropdown', source: ['A', 'G', 'D', 'N'], allowInvalid: false},
      {data: "recruit", readOnly: true},
      {data: "sprout", readOnly: true},
      {data: "x", type: 'numeric', allowInvalid: false},
      {data: "y", type: 'numeric', allowInvalid: false},
      {data: "note"},
      {data: "alternotetable", readOnly: true, renderer: "html"},
      {data: "entry"},
      {data: "id"},
      {data: "user"}

    ];

  var colWidths=[120, 40, 40, 100, 120,50,50,50,40,40,60,35,35, 160,160];
  var colHeaders=["Date", "Trap", "Plot", "Tag", "種類", "長度","子葉","真葉","狀態","新舊","萌櫱","X","Y", "Note","特殊修改"];

  var hiddenColumns ={
      columns: [15, 16, 17],
    };

  return createHandsontable(container, columns, data2, saveButtonName, "/fsseedlingsavedata", tabletype, colWidths, hiddenColumns, colHeaders, thispage );

}




function fsseedlingtableupdate(data, thispage, pps, maxid){


  $('.finishnote').html('');
  var site=`${data[0].trap}`;
  
  var tableType='data';
  dataTableUpdate(data, thispage, pps, plotType, tableType, site);

}


function recruittable(data, emptytable, csplist){

// console.log(entry);
  var site=data[0].trap;
  var thispage=1; 
  $(`button[name=recruitsave${site}]`).off();
  var container = $(`#recruittable${site}`);
  var saveButtonName=`recruitsave${site}`;
  var tabletype='recruit';
  // var emptytable=emptytable;

	const plotValidator = (value, callback) => {
	  
	    if (value==1 || value ==3 || value ==2 || value=='') {
	      callback(true);

	    } else {
	      callback(false);
	    }
	  
	};

  var columns = [
      {data: "date", dateFormat: 'YYYY-MM-DD', type: 'date', allowInvalid: false},
      {data: "trap", type: 'numeric', allowInvalid: false},
      {data: "plot", type: 'numeric', allowInvalid: false, validator: plotValidator},
      {data: "tag"},
      {data: "csp", type: 'autocomplete', source: csplist, strict: false, visibleRows: 10},
      {data: "ht", type: 'numeric', allowInvalid: false},
      {data: "cotno", type: 'numeric', allowInvalid: false},
      {data: "leafno", type: 'numeric', allowInvalid: false},
      
      {data: "recruit", type: 'dropdown', source: ['R', 'O', 'T'], allowInvalid: false, visibleRows: 10},
      {data: "sprout", type: 'dropdown', source: ['FALSE', 'TRUE'], allowInvalid: false, visibleRows: 10},
      {data: "x", type: 'numeric', allowInvalid: false},
      {data: "y", type: 'numeric', allowInvalid: false},
      {data: "note"},
      {data: "tofix", type: 'checkbox', checkedTemplate: '1', uncheckedTemplate: ''}

    ];

  var colWidths=[120, 40, 40, 80, 120,50,50,50,40,90,35,35, 160,50];
  var colHeaders=["Date", "Trap", "Plot", "Tag", "種類", "長度","子葉","真葉","新舊","萌櫱","X","Y", "Note", "漏資料"];

  var hiddenColumns =[];
  return createHandsontable(container, columns, emptytable, saveButtonName, "/fsseedlingsaverecruit", tabletype, colWidths, hiddenColumns, colHeaders, thispage );  

} 

  function deleteroll(tag, id, entry, trap){
    
    if(confirm('確定刪除 '+tag+' 撿到環資料??')) 
    {
      $('.slrollsavenote').html('');

      var saveUrl=`/fsseedlingdeleteslroll/${tag}/${id}/${entry}/${trap}`;
      var ajaxData={};
      var ajaxType='get';

      function handleSuccess(res) {
            if (res.slrollsavenote !=''){
              $('.slrollsavenote').html(res.slrollsavenote);
            }
            fsrolltableupdate(res.data, res.trap);
      }
      makeAjaxRequest(
        saveUrl, ajaxData, ajaxType,
        handleSuccess,
        function () {}
      );
    }
  }

function fsrolltableupdate(data, trap){

	var container = $(`#slrolltable${trap}`);
	var handsontable = container.data('handsontable');

	handsontable.updateData(data);

	$('.deleteroll').on('click', function(){
		id=$(this).attr('deleteid');
		tag=$(this).attr('tag');
		entry1=$(this).attr('entry');
		trap=$(this).attr('trap');
		deleteroll(tag, id, entry1, trap);
	})
}


function fsslrolltable(slroll, covs){
  var site=covs[0]['trap'];

  var container = $(`#slrolltable${site}`);

  var saveButtonName=`slrollsave${site}`;
  var tableType='roll';
  var thispage=1;

  var columns = [
      {data: "date", dateFormat: 'YYYY-MM-DD', type: 'date'},
      {data: "trap", type: 'numeric', allowInvalid: false},
      {data: "plot", type: 'numeric', allowInvalid: false},
      {data: "tag"},
      {data: "note"},
      {data: "delete", renderer: "html"},
      {data: "id"},
      {data: "year"},
      {data: "month"}

    ];

  var colWidths=[120, 50, 50, 80, 120, 50];
  var colHeaders=["Date", "Trap", "Plot", "Tag", "Note", ""];

  var hiddenColumns ={
      columns: [6, 7, 8],
    };
  return createHandsontable(container, columns, slroll, saveButtonName, `/fsseedlingsaveslroll/${entry}/${site}`, tableType, colWidths, hiddenColumns, colHeaders, thispage );


	$('.deleteroll').on('click', function(){
		const id=$(this).attr('deleteid');
		const tag=$(this).attr('tag');
		const entry2=$(this).attr('entry');
		const trap=$(this).attr('trap');
		deleteroll(tag, id, entry2, trap);
	})
}


function alternote(tag, entry, thispage, envet) {
	// console.log(tag);

    var saveUrl=`/fsseedlingaddalternote/${tag}/${entry}/${thispage}`;
    handleAlternote(tag, entry, thispage, saveUrl);

}


function deletealternote(tag, thispage){

  var saveUrl=`/fsseedlingdeletealter/${tag}/${entry}/${thispage}`;
  handleDeleteAlternote(tag, plotType, saveUrl)

}





function alternotetable(alterdata, tag, entry, thispage){

  $("button[name=alternotesave]").off();
  $('.deletealternotebutton').attr({'tag': tag,  'thispage': thispage});
  var container = $("#alternotetable");
 
  if (container.handsontable('getInstance')) {
    container.handsontable('destroy');
  }


  var saveButtonName='alternotesave';
  var tableType='alternote';

  var columns = [
      {data: "Trap", type: 'numeric'},
      {data: "Plot", type: 'numeric'},
      {data: "Tag"},
      {data: "csp", type: 'autocomplete', source: csplist, strict: false, visibleRows: 10, allowInvalid: false,},
      {data: "原長度"},
      {data: "原葉片數"},
      {data: "other"},
      {data: "狀態"},
      {data: "id"}

    ];

  var colWidths=[40,40,80,120,60, 60, 100];
  var colHeaders=["Trap","Plot","Tag","種類", "原長度", "原葉片數",  "other", "狀態"];

  var hiddenColumns ={
      columns: [7, 8],
    };
  return createHandsontable(container, columns, alterdata, saveButtonName, "/fsseedlingsavealternote", tableType, colWidths, hiddenColumns, colHeaders, thispage ); 


}


function finish(entry){
    console.log(entry);

      var saveUrl=`/fsseedlingfinish/${entry}`;
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