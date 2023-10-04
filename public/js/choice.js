$(document).ready(function() { 

	box1H=$('.censustype').outerHeight();
	box1H1=Math.round(box1H/2)+1;
	// box1H2=box1H-box1H1;
	$('.triangle').css('border-top-width', box1H1);
	$('.triangle').css('border-bottom-width', box1H1);
	// $('.triangle').css('border-top-width', box1H);


//choice
	$('.choice').on('click', function(){
		thissite=$(this).attr('site');
		thisproject=$(this).attr('project');
		location.href=`${thissite}/${thisproject}`;

	})



})