(function ($, Drupal, window) {
    jQuery(".more-upc").click(function(){
    	var upc_label = window.language == 'fr-ca'? "Code CUP " : "UPC Code "
		var nb_upc = jQuery("input.contest-detail__user-upc-code").length + 1;
		if (nb_upc > 7){
			return;
		}
		jQuery("#nb_upc_code").val(nb_upc);

		jQuery('<div class="contest-detail__user-upc-code"><input type="text" class="contest-detail__user-upc-code extra-upc" name="upc_code_' + nb_upc + '" id="upc-code-' + nb_upc + '" placeholder="' + upc_label + '" value="" ><span class="help-block err_upccode"></span></div>').insertAfter("div.contest-detail__user-upc-code:last")
	});	

	$('.less-upc').click(function() {
		var nb_upc = parseInt($('#nb_upc_code').val());

		$('.extra-upc').each(function(index, key) {
			if (key.value.length < 1) {
				nb_upc -= 1;
				$(key).parent('.contest-detail__user-upc-code').remove();
			}
		});

		$('#nb_upc_code').val(nb_upc);
	});

	

	$(document).ready(function() {
		var backdrop = $('.modal_backdrop');
		var modal = $('.modal_content');

		if (!modal.hasClass('modal_open')) {
			backdrop.hide();
			modal.hide();
		}

		$('.open_modal_btn').click(function() {
			backdrop.toggleClass('modal_closed modal_open');
			modal.toggleClass('modal_closed modal_open');
	
			backdrop.fadeIn(400, function() {
				modal.fadeIn(400, function() {
					$('body').css({
						'overflow-y': 'hidden'
					});
				});
			});
		});
	
		$('.modal_backdrop, .modal_content_close').click(function() {
			modal.fadeOut(400, function() {
				backdrop.fadeOut(400, function() {
					backdrop.toggleClass('modal_open modal_closed');
					modal.toggleClass('modal_open modal_closed');

					$('body').css({
						'overflow-y': 'initial'
					});
				});
			});			
		});

		const OFFSET_AMOUNT = -400;
		const STANDARD_FROM = {
			top: '100px',
			opacity: '0'
		};
		const STANDARD_TO = {
			top: '0',
			opacity: '1'
		};

		function setStandardScene(elem, offset) {
			return {
				triggerElement: elem,
				offset: offset
			};
		}

		var controller = new ScrollMagic.Controller();

		/* Prize/Recipe boxes */
		if ($('.animated-prize-boxes').length) {
			var staggerPrizeBoxes = TweenMax.staggerFromTo('.contest-detail__prize', 0.75, STANDARD_FROM, STANDARD_TO, 0.15);

			var staggerClaimsScene = new ScrollMagic.Scene(setStandardScene('.animated-prize-boxes', OFFSET_AMOUNT))
			.setTween(staggerPrizeBoxes)
			.addTo(controller);
		}
		

		if ($('.animated-text-block').length) {
			var slideUpBrands = TweenMax.fromTo('.animated-text-block', 2.5, STANDARD_FROM, STANDARD_TO);

			var slideUpBrandsScene = new ScrollMagic.Scene(setStandardScene('.animated-text-block', -300))
			.setTween(slideUpBrands)
			.addTo(controller);
		}

		
		
		// $(document).on('mouseover mouseout', '.dosomething', function(){
		$(document).on('focus', 'input.contest-detail__user-upc-code', function(){
			$(this).siblings(".help-block").text("");
			$(this).removeClass('.has-error');
			console.log("focus");

		});

		

		$(document).on('focusout', 'input.contest-detail__user-upc-code', function(){
		// $("input.contest-detail__user-upc-code").focusout(function(){
			var upccode = $(this).val();
			var success = 1;
			// console.log($(this));
			var this_upc_input = $(this);
			upc_data = {"upccode": upccode};
			// console.log(upccode);
			$.ajax({
		          url: "/microsite/validate/upccode",
		          type: "POST",
		          data:  upc_data,
		          success:function(data) {
		          	  var json = data;
		          	//   console.log(data);
		              if (data.status == 1){
		                //   console.log("SUCCESS ");
		                  success = 1;
						this_upc_input.siblings(".help-block").text("");
						this_upc_input.removeClass('.has-error');
						this_upc_input.addClass('valid-upc');
		              }
		              else {
		                //   console.log("Failed")
		                  success = 0;
							this_upc_input.siblings(".help-block").text(window.upc_error_msg);
							this_upc_input.removeClass('valid-upc');
							this_upc_input.addClass('.has-error');
		                  
		              }
		          },
		          error: function(data){
				    var error = data;
				    // console.log("error");
				    
				  }
	        });
			// if (success == 0){
			// } else {
			// }
		})

	
	});	
          
    $("#submitcontest").click(function(){
        event.preventDefault();
        submitsignup = false;
        grecaptcha.execute().then(function (token) {
            //console.log(token);
            $('#grecaptcharesponse_contest').val(token);
            $('#contestsignup-form').submit();
        });
    });	
}(jQuery, Drupal, window));