

// console.log(type);
//重新選擇工作項目
	$('.back').on('click', function(){
		location.href=`/choice`;
	})



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


$('.list4, .list4inner').on('mouseenter', function() {
  $('.list4inner').css('display', 'inline-flex');
  $('.list4').addClass('listhover'); 
  $('.now hr').css('color', 'transparent');
}).on('mouseleave', function() {
  $('.list4inner').hide();
  $('.list4').removeClass('listhover');
  $('.now hr').css('color', '#91A21C');
});



window.addEventListener('data', event => {

	covs=event.detail.covs;
	data=event.detail.record;
	emptytable=event.detail.emptytable;
	maxid=event.detail.maxid;
	slroll=event.detail.slroll;
	csplist=event.detail.csplist;

    // data=event.detail;
	// console.log(data);
	// console.log(slroll);
	// console.log(emptytable);
    // $('#slrolltable').html('');
    $(".save2").unbind();

    fscovtable(covs);
    //一開始,thispage=1

    if (data[0].tag!='無'){
    fsseedlingtable(data, maxid, 1);}
    recruittable(data, emptytable);
    fsslrolltable(slroll, covs);

});



function fscovtable(covs){


// console.log(covs);  
// console.log(cov);

  var container = $("#covtable"+covs[0].trap);
  var parent = container.parent();
  var cellChanges = [];
  container.handsontable({
    data: covs,
    // startRows: 3,
    colHeaders: true,
    // rowHeaders: true,
    // minSpareRows: 1,
    currentRowClassName: 'currentRow',
    colWidths: [120, 50, 50, 80, 120, 200],
    licenseKey: 'non-commercial-and-evaluation',
    colHeaders: ["Date", "Trap", "Plot", "覆蓋度", "樣區上方光度", "Note"],
    columns: [
      {data: "date", dateFormat: 'YYYY-MM-DD', type: 'date'},
      {data: "trap", readOnly: true},
      {data: "plot", readOnly: true},
      {data: "cov", type: 'numeric'},
      {data: "canopy", type: 'dropdown', source: ['U', 'I', 'G'], allowInvalid: false},
      {data: "note"},
      {data: "id"}

    ],
    hiddenColumns: {
    // specify columns hidden by default
   		columns: [6],
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

	parent.find('button[name=covsave'+covs[0].trap+']').click(function () {
		$('.covsavenote').html('');

		$.ajaxSetup({
			  headers: {
			    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			  }
			});

		  $.ajax({
		    url: "/fsseedlingsavecov",
		    data: {
		    	data: handsontable.getSourceData(),
		    	entry: entry
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
		        // console.log(res.covs);
		        if (res.covsavenote !=''){
		        	$('.covsavenote').html(res.covsavenote);
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
  function deleteid(tag, entry, thispage){
    
    if(confirm('確定刪除 '+tag+' 新增小苗資料??')) 
    {
      $('.datasavenote').html('');
        $.ajax({
        url: `/fsseedlingdeletedata/${tag}/${entry}/${thispage}`,
        type: 'get',
        success: function (res) {
          if (res.result === 'ok') {
            console.log('Data saved');
            console.log(res);
            if (res.datasavenote !=''){
              $('.datasavenote').html(res.datasavenote);
            }
            fsseedlingtableupdate(res.recruit, res.maxid, res.thispage);
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



//data為原始資料
//data1為切割後得資料

function fsseedlingtable(data, maxid, thispage){

	// datapage=pages(data, thispage);
	// console.log(datapage);


	totalpage=Math.ceil(data.length/20);
	// console.log(data);
		$('.prev').addClass('prev'+data[0]['trap']);
		$('.next').addClass('next'+data[0]['trap']);
		$('.pagenote').html('共 '+data.length+'筆資料。 ');

	if (totalpage>1){
		$('.pagenote').append('第 '+thispage+' ／ '+totalpage+' 頁');
		datapage=pages(data, thispage, totalpage);

		data2=datapage[1];
	} else {
		data2=data;
	}


// console.log(data);  
	// recruittable(data);
  var container = $("#seedlingtable"+data[0].trap);
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
    colWidths: [120, 40, 40, 100, 120,50,50,50,40,40,60,35,35, 160,160],
    licenseKey: 'non-commercial-and-evaluation',
    colHeaders: ["Date", "Trap", "Plot", "Tag", "種類", "長度","子葉","真葉","狀態","新舊","萌櫱","X","Y", "Note","特殊修改"],
    columns: [
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

    ],
    currentRowClassName: 'currentRow',
    autoWrapRow: true,   //自動換行
    hiddenColumns: {
    // specify columns hidden by default
   		columns: [15, 16, 17],
  	},
  	manualColumnResize: true,
  	// manualRowResize: true,
    cells: function (row, col, prop) {
	
          var cellProperties = {};
          var curData = container.handsontable('getData')[row][10]; //column 10 is the field "sprout"
          // if (curData==='TRUE'){
          // 	cellProperties.readOnly = false; 
          // }
          if (container.handsontable('getData')[row][16]>maxid){
          	cellProperties.readOnly = false; 
          	if (col==8 || col == 14){
          		cellProperties.readOnly = true; 
          	}

          }

          if (col == 11 || col==12) {            //column needs to be read only               
           //if status is Active
			if (curData=== 'TRUE') { 
                cellProperties.readOnly = true; 
            }
          }

          if (col == 13 || col == 14){
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

	parent.find('button[name=seedlingsave'+data[0].trap+']').click(function () {
		$('.seedlingsavenote').html('');

		$.ajaxSetup({
			  headers: {
			    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			  }
			});

		  $.ajax({
		    url: "/fsseedlingsavedata",
		    data: {
		    	data: handsontable.getSourceData(),
		    	entry: entry 

		    	// _token : '{{csrf-token()}}'
		    }, //returns all cells' data
		    // dataType: 'json',
		    type: 'POST',
		    success: function (res) {
		      if (res.result === 'ok') {
		        console.log('Data saved');
		        // console.log(res.covs);
		        if (res.datasavenote !=''){
		        	$('.seedlingsavenote').html(res.datasavenote);
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

function pages(data, thispage, totalpage){

	$(".prev").unbind();
	$(".next").unbind();

		start=20*(thispage-1);
		end=start+20;
		// console.log(thispage);
		data2=data.slice(start, end);
		// $('.prev').show();
		// $('.next').show();
		// $('.pagenote').html('共 '+data.length+'筆資料。第 '+thispage+' ／ '+totalpage+' 頁');
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

			$('.prev'+data[0]['trap']).on('click', function() {
				thispage=$(this).attr('thispage');
				gopage=parseInt(thispage)-1;

				fsseedlingtableupdate(data, maxid, gopage);
			})

			$('.next'+data[0]['trap']).on('click', function() {
				thispage=$(this).attr('thispage');
				gopage=parseInt(thispage)+1;

				// console.log(data);
				fsseedlingtableupdate(data, maxid, gopage);
			})


	datapage=[data, data2, thispage];

	return datapage;

}




function fsseedlingtableupdate(data, maxid, thispage){

	var container = $("#seedlingtable"+data[0].trap);
	var handsontable = container.data('handsontable');
	// console.log(data);
	totalpage=Math.ceil(data.length/20); 
  $('.pagenote').html(`共 ${data.length} 筆資料。`);
	if (totalpage>1){
		datapages=pages(data, thispage, totalpage);
    $('.pagenote').append('第 '+thispage+' ／ '+totalpage+' 頁');
		data3=datapages[1];
	} else {data3=data;}

	
// console.log(data3);
	handsontable.updateData(data3, maxid, thispage);
	handsontable.updateSettings({
    cells: function (row, col, prop) {
	
          var cellProperties = {};
          var curData = container.handsontable('getData')[row][10]; //column 10 is the field "sprout"
          // if (curData==='TRUE'){
          // 	cellProperties.readOnly = false; 
          // }
          if (container.handsontable('getData')[row][16]>maxid){
          	cellProperties.readOnly = false; 
          	if (col==8 || col == 14){
          		cellProperties.readOnly = true; 
          	}

          }

          if (col == 11 || col==12) {            //column needs to be read only               
           //if status is Active
			if (curData=== 'TRUE') { 
                cellProperties.readOnly = true; 
            }
          }

          if (col == 13 || col == 14){
          	cellProperties.className = 'fs08'; 
          }
         return cellProperties;

    },
    });

}


function recruittable(data, emptytable){

// console.log(entry);
  var trap=data[0].trap;
  $(`button[name=recruitsave${trap}]`).off();
  var container = $("#recruittable"+data[0].trap);
  var parent = container.parent();
  // var emptytable=emptytable;

	const plotValidator = (value, callback) => {
	  
	    if (value==1 || value ==3 || value ==2 || value=='') {
	      callback(true);

	    } else {
	      callback(false);
	    }
	  
	};

  container.handsontable({
    // data: emptytable,
    dataSchema: {
      trap: data[0].trap,
      recruit: 'R',
      sprout: 'FALSE',
      date: '',
      plot:'',
      tag:'',
      csp:'',
      ht:'',
      cotno:'',
      leafno:'',
      x:'',
      y:'',
      note:''
    },
    startRows: 20,
    colHeaders: true,
    rowHeaders: true,
    rowHeaderWidth: 25,
    contextMenu: ['row_above', 'row_below', 'remove_row'],
    // minSpareRows: 1,
    colWidths: [120, 40, 40, 80, 120,50,50,50,40,90,35,35, 160],
    rowHeights: 35,
    licenseKey: 'non-commercial-and-evaluation',
    colHeaders: ["Date", "Trap", "Plot", "Tag", "種類", "長度","子葉","真葉","新舊","萌櫱","X","Y", "Note"],
    columns: [
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

    ],
    currentRowClassName: 'currentRow',
    manualColumnResize: true,

    cells: function (row, col, prop) {
  
   	 }

  	});


  var handsontable = container.data('handsontable');

	parent.find('button[name=recruitsave'+data[0].trap+']').click(function () {
		$('.recruitsavenote').html('');

		$.ajaxSetup({
			  headers: {
			    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			  }
			});

		  $.ajax({
		    url: "/fsseedlingsaverecruit",
		    data: {
		    	data: handsontable.getSourceData(),
		    	entry: entry
		    
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
        if (res.recruit.length != 0){
				  fsseedlingtableupdate(res.recruit, res.maxid, 1);
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

  function deleteroll(tag, id, entry, trap){
    
    if(confirm('確定刪除 '+tag+' 撿到環資料??')) 
    {
      $('.slrollsavenote').html('');
        $.ajax({
        url: "/fsseedlingdeleteslroll/"+tag+"/"+id+"/"+entry+"/"+trap,
        type: 'get',
        success: function (res) {
          if (res.result === 'ok') {
            console.log('Data saved');
            console.log(res);
            if (res.slrollsavenote !=''){
              $('.slrollsavenote').html(res.slrollsavenote);
            }
            fsfsrolltableupdate(res.data, res.trap);
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

function fsfsrolltableupdate(data, trap){


	var container = $("#slrolltable"+trap);
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

  var container = $("#slrolltable"+covs[0]['trap']);
  var parent = container.parent();
  // var cellChanges = [];
  container.handsontable({
    data: slroll,
    // startRows: 3,
    colHeaders: true,
    rowHeaders: true,
    rowHeaderWidth: 25,
    rowHeaderHeights: 35,
    // contextMenu: ['row_above', 'row_below', 'remove_row'],
    // rowHeaders: true,
    minSpareRows: 5,
    rowHeights: 35,
    currentRowClassName: 'currentRow',
    colWidths: [120, 50, 50, 80, 120, 50],
    licenseKey: 'non-commercial-and-evaluation',
    colHeaders: ["Date", "Trap", "Plot", "Tag", "Note", ""],
    columns: [
      {data: "date", dateFormat: 'YYYY-MM-DD', type: 'date'},
      {data: "trap", type: 'numeric', allowInvalid: false},
      {data: "plot", type: 'numeric', allowInvalid: false},
      {data: "tag"},
      {data: "note"},
      {data: "delete", renderer: "html"},
      {data: "id"},
      {data: "year"},
      {data: "month"}
    ],
    hiddenColumns: {
    // specify columns hidden by default
   		columns: [6, 7, 8],
  	},
  	// dropdownMenu: true,
    cells: function (row, col, prop) {
   	 },
   	

  	});

  var handsontable = container.data('handsontable');

	parent.find('button[name=slrollsave'+covs[0].trap+']').click(function () {
		$('.slrollsavenote').html('');

		$.ajaxSetup({
			  headers: {
			    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			  }
			});

		  $.ajax({
		    url: "/fsseedlingsaveslroll/"+entry+"/"+covs[0]['trap'],
		    data: {
		    	data: handsontable.getSourceData()
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
		        if (res.slrollsavenote !=''){
		        	$('.slrollsavenote').html(res.slrollsavenote);
		        }
		       fsfsrolltableupdate(res.data, res.trap);

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
	$('.altersavenote').html('');

    var posX = event.pageX;
    var posY = event.pageY;
	  // var posX = $("button[name='alternoteshow"+tag+"']").offset();
    // var posY = $("button[name='alternoteshow"+tag+"']").offset().top;
    console.log(posX);

    $('.alternotetalbeouter').css('top', posY);
    $('.alternotetalbeouter').css('left', posX-500);
	


	$('.alternotetalbeouter').show();
	$('.altertag').html(tag);

		  $.ajax({
		    url: "/fsseedlingaddalternote/"+tag+"/"+entry+"/"+thispage,
		    type: 'get',
		    success: function (res) {
		      if (res.result === 'ok') {
		        console.log('Data saved');
		         console.log(res);
						alternotetable(res.alterdata, tag, entry, res.thispage);

				if (res.havedata=='yes'){
					$('.deletealternotebutton').show();
					
					
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

	// alternotetable(tag, entry);
}

function deletealternoteButtonClick(button){
  const tag = $(button).attr('tag');
  const thispage = $(button).attr('thispage');
  deletealternote(tag, thispage);	
}


function deletealternote(tag, thispage){
    if(confirm('確定刪除 '+tag+' 特殊修改??')) 
    {
      $('.altersavenote').html('');
        $.ajax({
        url: `/fsseedlingdeletealter/${tag}/${entry}/${thispage}`,
        type: 'get',
        success: function (res) {
        	// console.log(res);
          if (res.result === 'ok') {
            console.log('Data saved');
            // console.log(res);
            if (res.datasavenote !=''){
              $('.altersavenote').html(res.datasavenote);
            }
            fsseedlingtableupdate(res.data, res.maxid, res.thispage);

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



function alternotetable(alterdata, tag, entry, thispage){
	// console.log(csplist);
	$("button[name=alternotesave]").off();
	$('.deletealternotebutton').attr({'tag': tag,  'thispage': thispage});
  var container = $("#alternotetable");
	container.handsontable('destroy');

  var parent = container.parent();
  var cellChanges = [];
  container.handsontable({
  	data: alterdata,
    // height: 320,
    startRows: 1,
    colHeaders: true,
    removeRowPlugin: true,
    // minSpareRows: 1,
    colWidths: [40,40,80,120,60, 60],
    licenseKey: 'non-commercial-and-evaluation',
 
    colHeaders: ["Trap","Plot","Tag","種類", "原長度", "原葉片數", "狀態"],
    columns: [
      {data: "Trap", type: 'numeric'},
      {data: "Plot", type: 'numeric'},
      {data: "Tag"},
      {data: "csp", type: 'autocomplete', source: csplist, strict: false, visibleRows: 10, allowInvalid: false,},
      {data: "原長度"},
      {data: "原葉片數"},
      {data: "狀態"},
      {data: "id"}
    ],
    currentRowClassName: 'currentRow',
    autoWrapRow: true,   //自動換行
  	manualColumnResize: true,
  	hiddenColumns: {
    // specify columns hidden by default
   		columns: [6, 7],
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
		    url: "/fsseedlingsavealternote",
		    data: {
		    	data: handsontable.getSourceData(),
		    	entry: entry, 
		    	thispage : thispage
		    }, 
		    type: 'POST',
		    success: function (res) {
		      if (res.result === 'ok') {
		        console.log('Data saved');
		        if (res.datasavenote !=''){
		        	$('.altersavenote').html(res.datasavenote);
		        } 
		        console.log(res);
		        fsseedlingtableupdate(res.data, res.maxid, res.thispage);
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


function finish(entry){
    console.log(entry);
        $.ajax({
        url: `/fsseedlingfinish/${entry}`,
        type: 'get',
        success: function (res) {
          // console.log(res);
          if (res.result === 'ok') {
            console.log('Data saved');
            console.log(res);
            if (res.finishnote !=''){
              $('.finishnote').html(res.finishnote);
            }
          }
          else {
            console.log('Save error');
            // console.log(res);
          }
        },
        error: function () {
          console.log('Save error2.');
        }
      });
}