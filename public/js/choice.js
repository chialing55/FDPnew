$(document).ready(function() { 




//choice
	$('.choice').on('click', function(){
		thissite=$(this).attr('site');
		thisproject=$(this).attr('project');
		if (thisproject=='splist'){
			location.href='/web/splist';
		} else {
			location.href=`${thissite}/${thisproject}`;
		}
		

	})



})