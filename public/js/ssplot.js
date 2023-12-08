

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