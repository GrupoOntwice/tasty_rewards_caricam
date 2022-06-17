(function ($, window) {

	$(document).ready(function () {

	  	var screen_width = $(window).innerWidth()
	      || document.documentElement.clientWidth
	      || document.body.clientWidth;

	    if (screen_width > 768) {
	      if ( $(".carousel_fullwidth.carousel_desktop").length > 0 ){
	        lazyLoadBrandCarousel(".js-lazyloaded");
	      }
	    } else {
	      if ( $(".carousel_fullwidth.carousel_mobile").length > 0 ){
	        lazyLoadBrandCarousel(".js-lazyloaded-mobile");
	      }
	    }
	    window.fully_loaded = 1;
  });



	function lazyLoadBrandCarousel(js_selector){
		$(document).mousemove(function(e){
			if (window.fully_loaded){
				// console.log("mousemove fully loaded");
				ShowBrandCarouselImage(js_selector);
			}
	   	}); 

	   	$(document).scroll(function () {
			if (window.fully_loaded){
				// console.log("scroll fully loaded");
				ShowBrandCarouselImage(js_selector);
			}
	   	});
	}

	function ShowBrandCarouselImage(js_selector){

		if ( window.has_loaded_home_carousel_image == 1) {
			return;
		}

		$(js_selector).each( function(index, obj) {
			var element = $(obj);
			var delay_ms =  100 + index*1000;
			window.has_loaded_home_carousel_image = 1;

			setTimeout(
				function() 
				{
					element.css('background-image', element.data('style'));
					// console.log(" index is " + index);
				}, 
		  	delay_ms);

		});

	}


}(jQuery, window));/*end of file*/