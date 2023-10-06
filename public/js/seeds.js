// console.log(type);
//重新選擇工作項目
	$('.back').on('click', function(){
		location.href=`/choice`;
	})


$('.listlink').on('click', function(){
	let type=$(this).attr('type');
	console.log(type);
	if (typeof type!='undefined'){
	location.href=(`/fushan/seeds/${type}`);
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

	$('.entrytableout').show();
	$('.keepenter').hide();
	$('.dateinfo').hide();
	fdata=event.detail.record;
	emptytable=event.detail.emptytable;
	census=event.detail.census;
	csplist=event.detail.csplist;
	console.log(emptytable);
	console.log(fdata);
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
	

    // $(".save2").unbind();

    //一開始,thispage=1


});


function emptyseedstable(table){

// console.log(entry);
	
  
  $(`button[name=newdatasave]`).off();
  var container = $("#seedstable_empty");
  var parent = container.parent();
  // var emptytable=emptytable;

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
      {data: "csp", type: 'autocomplete', source: csplist, strict: false, visibleRows: 10},
      {data: "code", type: 'dropdown', source: ['', '1', '2', '3', '4', '5', '6'], allowInvalid: false, visibleRows: 10},
      {data: "count", type: 'numeric', allowInvalid: false},
      {data: "seeds"},
      {data: "viability"},
      {data: "fragments", type: 'numeric', allowInvalid: false},
      
      {data: "sex", type: 'dropdown', source: ['', 'F', 'M'], allowInvalid: false, visibleRows: 10},
      {data: "identifier", type: 'dropdown', source: ['蔡佳秀', '張楊家豪'], allowInvalid: true, visibleRows: 40},
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


	parent.find('button[name=newdatasave]').click(function () {
		$('.seedssavenote').html('');

		$.ajaxSetup({
			  headers: {
			    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			  }
			});

		  $.ajax({
		    url: "/fsseedssavedata1",
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
		        totalpage=Math.ceil(res.data.length/20);
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

// console.log(entry);

	totalpage=Math.ceil(data.length/20);
	// console.log(data);
		// $('.prev').addClass('prev'+data[0]['trap']);
		// $('.next').addClass('next'+data[0]['trap']);
		$('.pagenote').html('共 '+data.length+'筆資料。 ');

	if (totalpage>1){
		$('.pagenote').append('第 '+thispage+' ／ '+totalpage+' 頁');
		datapage=pages(data, thispage, totalpage);

		data=datapage[1];
	} else {
		data=data;
	}


  
  $(`button[name=datasave]`).off();
  var container = $("#seedstable");
  var parent = container.parent();
  // var emptytable=emptytable;

  container.handsontable({
    data: data,
    startRows: 29,
    colHeaders: true,
    rowHeaders: true,
    rowHeaderWidth: 25,
    
    // minSpareRows: 1,
    colWidths: [30, 40, 50, 120, 50, 50,60,50,70,40,100, 160, 200,40],
    rowHeights: 35,
    licenseKey: 'non-commercial-and-evaluation',
    colHeaders: ["id","census","Trap", "種類", "類別", "數量", "種子數","活性","碎片3數量","性別","鑑定者","備註", "檢查", ""],
    columns: [
    	{data: "id"},
      {data: "census"},
      {data: "trap",  allowInvalid: false},
      {data: "csp", type: 'autocomplete', source: csplist, strict: false, visibleRows: 10},
      {data: "code", type: 'dropdown', source: [' ', '1', '2', '3', '4', '5', '6'], allowInvalid: false, visibleRows: 10},
      {data: "count", type: 'numeric', allowInvalid: false},
      {data: "seeds"},
      {data: "viability"},
      {data: "fragments", type: 'numeric', allowInvalid: false},
      
      {data: "sex", type: 'dropdown', source: [' ', 'F', 'M'], allowInvalid: false, visibleRows: 10},
      {data: "identifier", type: 'dropdown', source: ['蔡佳秀', '張楊家豪'], allowInvalid: true, visibleRows: 40},
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
   	 }

  	});


	parent.find('button[name=creattable]').click(function () {
		$('#seedstableout').hide();
		$('#seedstableout_empty').show();
		$('.seedssavenote').html('');

	});
  var handsontable = container.data('handsontable');

	parent.find('button[name=datasave]').click(function () {
		$('.seedssavenote').html('');

		$.ajaxSetup({
			  headers: {
			    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			  }
			});

		  $.ajax({
		    url: "/fsseedssavedata",
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
		        seedstableupdate(res.data, thispage);
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

	$('#seedstableout').show();
	$('#seedstableout_empty').hide();
	var container = $("#seedstable");
	var handsontable = container.data('handsontable');

	totalpage=Math.ceil(data.length/20);
	$('.pagenote').html(`共 ${data.length} 筆資料。`);

	if (totalpage>1){
		$('.pagenote').append('第 '+thispage+' ／ '+totalpage+' 頁');
		datapages=pages(data, thispage, totalpage);
		data3=datapages[1];
	} else {data3=data;}
	



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

  function deleteid(id, info, thispage){
    
    if(confirm('確定刪除 '+info+' 種子雨資料??')) 
    {
      $('.seedssavenote').html('');
        $.ajax({
        url: `/fsseedsdeletedata/${id}/${info}/${thispage}`,
        type: 'get',
        success: function (res) {
          if (res.result === 'ok') {
            console.log('Data saved');
            console.log(res);
            if (res.seedssavenote !=''){
              $('.seedssavenote').html(res.seedssavenote);
            }
		        seedstableupdate(res.data, thispage);
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

			$('.prev').on('click', function() {
				thispage=$(this).attr('thispage');
				gopage=parseInt(thispage)-1;

				seedstableupdate(data, gopage);
			})

			$('.next').on('click', function() {
				thispage=$(this).attr('thispage');
				gopage=parseInt(thispage)+1;

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