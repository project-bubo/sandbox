// document ready
$(document).ready(function() {
	Shadowbox.init();

	$('#flipbook').booklet({
		width:  960,
	    height: 500,

	    pageNumbers: false,
	    manual: false,
	    overlays: false,
	    pagePadding:0,
	    hash: true,

	    next: '#next',				  
		prev: '#prev'

	});

	$('.page-btn').click(function() {
		$(this).find('span').hide();
	});

	$('.page-btn.prev-page, .outer-arrow.prev').hover(function() {
		$('.page-btn.prev-page').find('span').show();
		$('.page-btn.prev-page').find('span').stop().animate({top: 0,left: 0},300,'easeOutQuad');
	}, function() {
		$('.page-btn.prev-page').find('span').stop().animate({top: -40,left: -40},100,'easeOutQuad');
	});

	$('.page-btn.next-page, .outer-arrow.next').hover(function() {
		$('.page-btn.next-page').find('span').show();
		$('.page-btn.next-page').find('span').stop().animate({top: 0,right: 0},300,'easeOutQuad');
	}, function() {
		$('.page-btn.next-page').find('span').stop().animate({top: -40,right: -40},100,'easeOutQuad');
	});

	$('.outer-arrow.prev').click(function(){
		$('.page-btn.prev-page').trigger('click');
	})
	$('.outer-arrow.next').click(function(){
		$('.page-btn.next-page').trigger('click');
	})

	$('.outer-arrow').find('span').hide().end().hover(function(){
		$(this).find('span').fadeIn('fast');
	},function(){
		$(this).find('span').fadeOut('fast');
	});


});
// END document ready