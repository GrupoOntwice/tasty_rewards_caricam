
(function ($, window) {
	if ($("#CouponFrame").length){
		document.getElementById('CouponFrame').onload= function() {
			if ($(".offer-item").length){
	        	jQuery("#offers").removeClass("hidden");
			}
			$("a.offer-img").each(function(){
				var link = $(this).closest(".offer-item-image").siblings(".offer-cta").find("a").attr('href');
				$(this).attr('href', link);
			});

			$(".offer-item-text-title h2").each(function(){
				// The <br> in the field_subtitle could not be 
				// interpreted from the template file 
				// views-view-fields--other-offers--coupon-other-offers.html.twig
				if ($(this).text().indexOf("<br>") >= 0){
					var arr = $(this).text().split("<br>");
					$(this).html(arr[0]);
					$(this).append('<br>');
					$(this).append(arr[1]);
				}
			})

			var url_params = window.location.search;
			if (url_params.includes('?savings=true') || url_params.includes('&savings=true') ){
				setTimeout(
				  function() 
				  {
						$([document.documentElement, document.body]).animate({
					        scrollTop: $("#offers").offset().top
					    }, 1500);
				  }, 1200);
				
			}

	    };
		
	}
        
}(jQuery, window));/*end of file*/

