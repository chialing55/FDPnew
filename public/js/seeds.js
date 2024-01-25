// console.log(type);
//重新選擇工作項目
	$('.back').on('click', function(){
		location.href=`/choice`;
	})


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



function handleHoverEvents(selector, innerSelector) {
  $(selector + ', ' + innerSelector).on('mouseenter', function() {
    $(innerSelector).css('display', 'inline-flex');
    $(selector).css({'color': '#fff', 'background-color': '#91A21C'}); 
    $('.now hr').css('color', 'transparent');
  }).on('mouseleave', function() {
    $(innerSelector).hide();
    $(selector).css({'color': '', 'background-color': ''}); 
    $('.now hr').css('color', '#91A21C');
  });
}

// 使用
handleHoverEvents('.list4', '.list4inner');
handleHoverEvents('.list6', '.list6inner');

window.addEventListener('data', event => {

	$('.entrytableout').show();
	$('.keepenter').hide();
	$('.dateinfo').hide();
	fdata=event.detail.record;
	emptytable=event.detail.emptytable;
	census=event.detail.census;
	csplist=event.detail.csplist;
	// console.log(emptytable);
	// console.log(fdata);
	if (fdata.length>0){
		console.log('1');
		$('#seedstableout').show();
		$('#seedstableout_empty').hide();
		seedstable(fdata, emptytable, 1);
		emptyseedstable(emptytable);
		
	} else {
		$('#seedstableout').hide();
		$('#seedstableout_empty').show();
		seedstable(emptytable, emptytable, 1);
		emptyseedstable(emptytable);
	}

});


function emptyseedstable(table){

// console.log(entry);
	
  const census=table[0]['census'];
  $('button[name=newdatasave'+census+']').off();
  var container = $("#seedstable_empty"+census);
  var parent = container.parent();
  // var emptytable=emptytable;

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

  container.handsontable({
    data: table,
    startRows: 29,
    colHeaders: true,
    rowHeaders: true,
    rowHeaderWidth: 25,
    contextMenu: ['row_above', 'row_below', 'remove_row'],
    // minSpareRows: 1,
    colWidths: [10, 40, 50, 120, 50, 50,60,50,70,40,100, 160],
    rowHeights: 35,
    licenseKey: 'non-commercial-and-evaluation',
    colHeaders: ["id","census","Trap", "種類", "類別", "數量", "種子數","活性","碎片3數量","性別","鑑定者","備註"],
    columns: [
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

    ],
    currentRowClassName: 'currentRow',
    manualColumnResize: true,
    hiddenColumns: {
    // specify columns hidden by default
   		columns: [0, 1],
  	},

    cells: function (row, col, prop) {
  
   	 }

  	});


  var handsontable = container.data('handsontable');

	parent.find('button[name=show_seedstable]').click(function () {
		$('#seedstableout').show();
		$('#seedstableout_empty').hide();
		seedstable(fdata, table, 1);
	});


	parent.find('button[name=newdatasave'+census+']').click(function () {
		$('.seedssavenote').html('');

		$.ajaxSetup({
			  headers: {
			    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			  }
			});

		  $.ajax({
		    url: "/fsseedssavedata1/record",
		    data: {
		    	data: handsontable.getSourceData(),
		    	// _token : '{{csrf-token()}}'
		    }, //returns all cells' data
		    // dataType: 'json',
		    type: 'POST',
		    success: function (res) {
		      if (res.result === 'ok') {
		        console.log('Data saved');
		        console.log(res);

		        if (res.seedssavenote !=''){
		        	$('.seedssavenote').html(res.seedssavenote);
		        }
		        // console.log(emptytable);
		        handsontable.updateData(res.emptytable);
		        totalpage=Math.ceil(res.data.length/29);
		        seedstableupdate(res.data, totalpage);
		        fdata=res.data;


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
		  // handsontable.updateData(emptytable);
	});

//更新大表
	parent.find('button[name=newdatasave2'+census+']').click(function () {
		$('.seedssavenote').html('');

		$.ajaxSetup({
			  headers: {
			    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			  }
			});

		  $.ajax({
		    url: "/fsseedssavedata1/fulldata",
		    data: {
		    	data: handsontable.getSourceData(),
		    	// _token : '{{csrf-token()}}'
		    }, //returns all cells' data
		    // dataType: 'json',
		    type: 'POST',
		    success: function (res) {
		      if (res.result === 'ok') {
		        console.log('Data saved');
		        console.log(res);

		        if (res.seedssavenote !=''){
		        	$('.seedssavenote').html(res.seedssavenote);
		        }
		        // console.log(emptytable);
		        handsontable.updateData(res.emptytable);
		        totalpage=Math.ceil(res.data.length/29);
		        seedstableupdate(res.data, totalpage);
		        fdata=res.data;


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
		  // handsontable.updateData(emptytable);
	});
	


}


function seedstable(data, table, thispage){

// console.log(data);
// const census=data[0]['census'];

	totalpage=Math.ceil(data.length/29);
	console.log(totalpage);
		// $('.prev').addClass('prev'+data[0]['trap']);
		// $('.next').addClass('next'+data[0]['trap']);
		$('.pagenote').html('共 '+data.length+'筆資料。 ');

	if (totalpage>1){
		$('.pagenote').append('第 '+thispage+' ／ '+totalpage+' 頁');
		datapage=pages(data, thispage, totalpage);

		data=datapage[1];
	} else {
		$('.prev').hide();
		$('.next').hide();
		data=data;
	}

// console.log(thispage);
  
  $('button[name=datasave'+census+']').off();
  var container = $("#seedstable"+census);
  var parent = container.parent();
  // var emptytable=emptytable;

  const codeValidator = (value, callback) => {
    if ([1, 2, 3, 4, 5, 6,  ''].includes(value)) {   //允許1234和空格
      callback(true);
    } else {
      callback(false);
    }
  };

  const sexValidator = (value, callback) => {
    if (['F', 'M', 'MF' ,''].includes(value)) {   //允許1234和空格
      callback(true);
    } else {
      callback(false);
    }
  };
var cellChanges = [];
  container.handsontable({
    data: data,
    startRows: 29,
    colHeaders: true,
    rowHeaders: true,
    rowHeaderWidth: 26,
    
    // minSpareRows: 1,
    colWidths: [30, 40, 50, 120, 50, 50,60,50,70,40,100, 160, 200,40],
    rowHeights: 35,
    licenseKey: 'non-commercial-and-evaluation',
    colHeaders: ["id","census","Trap", "種類", "類別", "數量", "種子數","活性","碎片3數量","性別","鑑定者","備註", "檢查", ""],
    columns: [
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

    ],
    currentRowClassName: 'currentRow',
    manualColumnResize: true,
    hiddenColumns: {
    // specify columns hidden by default
   		columns: [0, 1],
  	},
    cells: function (row, col, prop) {
          var cellProperties = {};
          var curData = container.handsontable('getData')[row][12]; //column 12 is the field "check"

          if (curData!= ''){
          	cellProperties.className = 'text-red-500'; 
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


	parent.find('button[name=creattable]').click(function () {
		$('#seedstableout').hide();
		$('#seedstableout_empty').show();
		$('.seedssavenote').html('');

	});
  var handsontable = container.data('handsontable');


	parent.find('button[name=datasave'+census+']').click(function () {
		$('.seedssavenote').html('');

		$.ajaxSetup({
			  headers: {
			    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			  }
			});

		  $.ajax({
		    url: "/fsseedssavedata/record",
		    data: {
		    	data: handsontable.getSourceData(),
		    	// _token : '{{csrf-token()}}'
		    }, //returns all cells' data
		    // dataType: 'json',
		    type: 'POST',
		    success: function (res) {
		      if (res.result === 'ok') {
		        console.log('Data saved');
		        console.log(res);

		        if (res.seedssavenote !=''){
		        	$('.seedssavenote').html(res.seedssavenote);
		        }
		        seedstableupdate(res.data, res.thispage);
		        // console.log(thispage);
		        fdata=res.data;

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

	parent.find('button[name=datasave2'+census+']').click(function () {
		$('.seedssavenote').html('');

		$.ajaxSetup({
			  headers: {
			    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			  }
			});

		  $.ajax({
		    url: "/fsseedssavedata/fulldata",
		    data: {
		    	data: handsontable.getSourceData(),
		    	// _token : '{{csrf-token()}}'
		    }, //returns all cells' data
		    // dataType: 'json',
		    type: 'POST',
		    success: function (res) {
		      if (res.result === 'ok') {
		        console.log('Data saved');
		        console.log(res);

		        if (res.seedssavenote !=''){
		        	$('.seedssavenote').html(res.seedssavenote);
		        }
		        seedstableupdate(res.data, res.thispage);
		        // console.log(thispage);
		        fdata=res.data;

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


function seedstableupdate(data, thispage){

const census=data[0]['census'];
	$('#seedstableout').show();
	$('#seedstableout_empty').hide();
	var container = $("#seedstable"+census);
	var handsontable = container.data('handsontable');

	totalpage=Math.ceil(data.length/29);
	$('.pagenote').html(`共 ${data.length} 筆資料。`);

	if (totalpage>1){
		$('.pagenote').append('第 '+thispage+' ／ '+totalpage+' 頁');
		datapages=pages(data, thispage, totalpage);
		data3=datapages[1];
	} else {data3=data;}
	
	console.log(data3, thispage);


	handsontable.updateData(data3, thispage);
	handsontable.updateSettings({
    cells: function (row, col, prop) {
          var cellProperties = {};
          var curData = container.handsontable('getData')[row][12]; //column 12 is the field "check"

          if (curData!= ''){
          	cellProperties.className = 'text-red-500'; 
          }
         return cellProperties;
   	 },
    });


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
        $.ajax({
        url: `/fsseedsdeletedata/${id}/${info}/${thispage}/${type}`,
        type: 'get',
        success: function (res) {
          if (res.result === 'ok') {
            console.log('Data saved');
            console.log(res);
            if (res.seedssavenote !=''){
              $('.seedssavenote').html(res.seedssavenote);
            }
		        seedstableupdate(res.data, res.thispage);
		        fdata=res.data;

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


  function pages(data, thispage, totalpage){

	$(".prev").unbind();
	$(".next").unbind();
	

		start=29*(thispage-1);
		end=start+29;
		// console.log(thispage);
		data2=data.slice(start, end);
		// $('.prev').show();
		// $('.next').show();
		// $('.pagenote').html('共 '+data.length+'筆資料。第 '+thispage+' ／ '+totalpage+' 頁');
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
				$('.prev').hide();
				$('.next').hide();			
		}



			$('.prev').on('click', function() {
				thispage=$(this).attr('thispage');
				gopage=parseInt(thispage)-1;
				$('.seedssavenote').html('');
				seedstableupdate(data, gopage);
			})

			$('.next').on('click', function() {
				thispage=$(this).attr('thispage');
				gopage=parseInt(thispage)+1;
				$('.seedssavenote').html('');
				// console.log(data);
				seedstableupdate(data, gopage);
			})


	datapage=[data, data2, thispage];

	return datapage;

}

function finish(){
    // console.log(entry);
        $.ajax({
        url: `/fsseedsfinish`,
        type: 'get',
        success: function (res) {
          // console.log(res);
          if (res.result === 'ok') {
            console.log('Data saved');
            console.log(res);
            if (res.finishnote !=''){
              $('.finishnote').html(res.finishnote);
            } else {
            	location.href='/fushan/seeds/entry';
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