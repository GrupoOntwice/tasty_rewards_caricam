(function ($, window) {

	$(document).ready(function(){
		lazyLoadCouponBlock();

	});

	function lazyLoadCouponBlock() {
		
		$(document).scroll(function () {
			var top = $(this).scrollTop();
			var loadAt = $(".js-section-couponblock").offset().top - 500;
			var width = $(window).innerWidth()
				|| document.documentElement.clientWidth
				|| document.body.clientWidth;


			if (top > loadAt && !window.hasLoadedCouponBlock && width > 768) {
				window.hasLoadedCouponBlock = true;
				var element = $(".js-lazyload-coupon");
				// load the gif image
				element.css('background-image', element.data('style'));

			}
			if (top > loadAt && !window.hasLoadedCouponImage ) {
				window.hasLoadedCouponImage = true;
				var el = $(".js-coupon-img-container");
				var _src = el.data('src');
				var _alt = el.data('alt');
				var _img = `<img  src="${_src}" alt="${_alt}" />`;
				el.after(_img);
			}
		});
	}
	
}(jQuery, window));/*end of file*/
