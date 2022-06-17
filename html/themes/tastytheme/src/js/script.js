/* eslint-disable */

/*
 * jQuery throttle / debounce - v1.1 - 3/7/2010
 * http://benalman.com/projects/jquery-throttle-debounce-plugin/
 *
 * Copyright (c) 2010 "Cowboy" Ben Alman
 * Dual licensed under the MIT and GPL licenses.
 * http://benalman.com/about/license/
 */
(function (b, c) { var $ = b.jQuery || b.Cowboy || (b.Cowboy = {}), a; $.throttle = a = function (e, f, j, i) { var h, d = 0; if (typeof f !== "boolean") { i = j; j = f; f = c } function g() { var o = this, m = +new Date() - d, n = arguments; function l() { d = +new Date(); j.apply(o, n) } function k() { h = c } if (i && !h) { l() } h && clearTimeout(h); if (i === c && m > e) { l() } else { if (f !== true) { h = setTimeout(i ? k : l, i === c ? e - m : e) } } } if ($.guid) { g.guid = j.guid = j.guid || $.guid++ } return g }; $.debounce = function (d, e, f) { return f === c ? a(d, e, false) : a(d, f, e !== false) } })(this);




(function ($, window) {
	'use strict';
	var droplang = $('html').attr('lang');
	const fullWidthOptions = {
		dots: true,
		autoplay: true,
		autoplaySpeed: 5000,
		fade: true,
		pauseOnHover: false
	};

	window.screen_width = $(window).innerWidth()
      || document.documentElement.clientWidth
      || document.body.clientWidth;



	window.carousel_fullWidthOptions = fullWidthOptions;
	const brandsOptions = {
		slidesToShow: 5,
		pauseOnHover: false,
		responsive: [
			{
				breakpoint: 1024,
				settings: {
					slidesToShow: 4,
				}
			},
			{
				breakpoint: 770,
				settings: {
					slidesToShow: 3,
				}
			},
			{
				breakpoint: 415,
				settings: {
					slidesToShow: 2,
				}
			}
		]
	};

	window.runSlickCarousel = function runcarousel(){
	  if (arguments.length != 2){
	    return;
	  }

	  var selector = arguments[0];
	  var slick_options = arguments[1];

	  if ($(selector).length > 0 ){
	    $(selector).slick(slick_options);
	  }
	}


	// Fritolay Varity Pack, Video and Coupon Section Animation

	function isScrolledIntoView(elem) {
		let docViewTop = $(window).scrollTop();
		let docViewBottom = docViewTop + $(window).height();

		let AnimationHeightOffset = 100;
		if ($(window).width() < 769) {
			AnimationHeightOffset = 75;
		} else if ($(window).width() < 480) {
			AnimationHeightOffset = 50;
		} else if ($(window).width() < 380) {
			AnimationHeightOffset = 25;
		}

		let elemTop = $(elem).offset().top - AnimationHeightOffset;
		let elemBottom = elemTop + $(elem).height() - AnimationHeightOffset;

		return ((elemBottom <= docViewBottom) && (elemTop >= docViewTop));
	}

	$(".main-coupon-right, .main-coupon-left, .vr-coupon-left, .vr-coupon-right").css("visibility", "hidden");


	$(window).on('scroll', function () {
		$(".main-coupon-left, .vr-coupon-left").each(function () {
			if (isScrolledIntoView($(this))) {
				$(this).css("visibility", "visible").addClass("animate__fadeInLeft");
			}
		});
		$(".main-coupon-right, .vr-coupon-right").each(function () {
			if (isScrolledIntoView($(this))) {
				$(this).css("visibility", "visible").addClass("animate__fadeInRight");
			}
		});
	});

	//homepage background click to target the button


	const backgroundClick = document.querySelectorAll(".banner_slide");

	for (let i = 0; i < backgroundClick.length; i++) {
		let el = backgroundClick[i];

		el.addEventListener("click", function () {
			let button = this.querySelector(".btn_red");
			if (button !== null)
				button.click();
		})
		el.addEventListener("click", function () {
			let button = this.querySelector(".btn_transparent");
			if (button !== null)
				button.click();
		})
	}

	//homepage background click for external link to open new tab

	jQuery(".banner_slide--text a").each(function () {
		if (jQuery(this).attr('href').startsWith("https:") || jQuery(this).attr('href').startsWith("www.")) {
			jQuery(this).attr('target', '_blank')
		}
	});

	//end homepage background click to target the button

	window.setCardImgSizes = function setCardImgSizes() {
		$('.background-zoom__parent .img-recipe').each(function () {
			$(this).css('height', $(this).innerWidth() + 'px');
		});

		$('.truncate_text').each(function () {
			$(this).html($(this).find('> p:first-of-type'));
			$(this).find('> p:first-of-type').clamp();
		});
	}

	Drupal.behaviors.myBehavior = {
		attach: function (context, settings) {
			window.setCardImgSizes();
		}
	};

	// document.getElementById('CouponFrame').onload= function() {
	// 	if ($(".offer-item").length){
 //        	jQuery("#offers").removeClass("hidden");
	// 	}
 //    };

	// function set_ads_cookie(){
	// 	jQuery.ajax({
	//          url:"/pepsibam/ads-cookie",
	//          type: "POST",
	//          data: {"val": "empty"},
	//          success:function(data) {
	//          	  console.log(data);
	//          	  var result = JSON.parse(data);
	//              if (result.status){
	//              	console.log(" Ads banner cookie set")
	//              }
	//              else {
	//              	console.log("error ")
	//              }
	//          }
	//        });
	// }
	function is_on_US_site() {
		if ($('html').attr('lang') === 'en-us' || $('html').attr('lang') === 'es-us') {
			return true;
		}
		return false;
	}

	function is_on_Canada_site() {
		if ($('html').attr('lang') === 'en' || $('html').attr('lang') === 'fr') {
			return true;
		}
		return false;
	}

	function is_USA_user() {
		if ($("#user_logged").data('value') === "en-us" || $("#user_logged").data('value') === "es-us") {
			return true;
		}
		return false;
	}

	function is_Canada_user() {
		if ($("#user_logged").data('value') === "en" || $("#user_logged").data('value') === "fr") {
			return true;
		}
		return false;
	}


	$(document).ready(function () {

		if ($('#contest').length) {
			/* Code moved to help_modal.js */
		}


		var ad_name = ("-" + $(".ads-name").text()) || "";

		if (!localStorage.getItem("ads_banner_cookie" + ad_name)) {
			$("#ads-unit").show("slow");
			// ads-name
		}

		$(".js-signin-form").click(function () {
			$('.jsModalSignIn').trigger('click');
			// $('.jsModalSignIn').removeClass('disabled');
		})

		/* Remove disabled class on sign in button after the page load */
		$('.jsModalSignIn').removeClass('disabled');

		$(".modal__close-ads-banner").click(function () {
			$("#ads-unit").addClass("hidden");
			// update the cookie?
			// set_ads_cookie();
			localStorage.setItem("ads_banner_cookie" + ad_name, 1);
		});

		window.setCardImgSizes();

		window.addEventListener("resize", function (e) {
			window.setCardImgSizes();
		});

		window.addEventListener("message", receiveMessage, false);
		function receiveMessage(event) {
			if (event.data === "bamLogOut=true") {
				// ACTION TO OPEN THE LOGIN
				$('.jsModalSignIn').trigger('click');
			}
			else
				return;
		}

		/***
	   * Redirect if Language is not the one
	   */

		if ($("#toolbar-administration").length > 0) {
			//Admin tool.. do nothing
		}
		else{
                   // console.log($('html').attr('lang'));
                   if ($("#user_logged").length > 0) {

                       // console.log($("#user_logged").data('value'));
                       if ( GetCountryCode() === 'usa' && GetUserCountry() !== 'usa' ) {
                           if ($("#user_logged").data('value') === "en"){
                            document.location.href = "/en-ca";
                           }
                           else{
                            document.location.href = "/fr-ca";
                           }
                       }
                       else{
                           if ( GetCountryCode() !== 'usa' && GetUserCountry() === 'usa' ){
                           	var user_lang = $("#user_logged").data('value');
                               document.location.href = "/" + user_lang;
                           }
                       }
                   }
                   else{
                       // console.log("Anonymous");
                   }
        }

		//Carousel for homepage, recipe and lifestyle page
		var screen_width = $(window).innerWidth()
			|| document.documentElement.clientWidth
			|| document.body.clientWidth;

		if (screen_width > 768) {
			// $('.carousel_fullwidth.carousel_desktop.js-main-carousel').slick(fullWidthOptions);
			// $('.carousel_fullwidth.carousel_desktop.js-recipe-carousel').slick(fullWidthOptions);
			// $('.carousel_fullwidth.carousel_desktop.js-article-carousel').slick(fullWidthOptions);

			load_homepage_carousels(false);
		} else {
			load_homepage_carousels(true);
			// $('.carousel_fullwidth.carousel_mobile.js-main-carousel').slick(fullWidthOptions);
			// $('.carousel_fullwidth.carousel_mobile.js-recipe-carousel').slick(fullWidthOptions);
			// $('.carousel_fullwidth.carousel_mobile.js-article-carousel').slick(fullWidthOptions);
		}

		function load_homepage_carousels(is_mobile = false){
			if (is_mobile){
				if (window.has_loaded_mobile_slides){
					return;
				}
				window.has_loaded_home_carousel_image = 0;
				window.runSlickCarousel('.carousel_fullwidth.carousel_mobile.js-main-carousel', fullWidthOptions);
				window.runSlickCarousel('.carousel_fullwidth.carousel_mobile.js-recipe-carousel', fullWidthOptions);
				window.runSlickCarousel('.carousel_fullwidth.carousel_mobile.js-article-carousel', fullWidthOptions);
				lazyLoadHomepageCarousel(".js-lazyloaded-mobile");
				window.has_loaded_mobile_slides = 1;
			} else {
				if (window.has_loaded_desktop_slides){
					return;
				}
				window.has_loaded_home_carousel_image = 0;
				window.runSlickCarousel('.carousel_fullwidth.carousel_desktop.js-main-carousel', fullWidthOptions);
				window.runSlickCarousel('.carousel_fullwidth.carousel_desktop.js-recipe-carousel', fullWidthOptions);
				window.runSlickCarousel('.carousel_fullwidth.carousel_desktop.js-article-carousel', fullWidthOptions);
				lazyLoadHomepageCarousel(".js-lazyloaded");
				window.has_loaded_desktop_slides = 1;
			}

		}

		window.addEventListener('resize', function(event) {
		    let screen_width = $(window).innerWidth();
		    if (screen_width > 768) {
		    	load_homepage_carousels(false);
		    	$(".carousel_mobile").addClass('hidden');
		    	$(".carousel_desktop").removeClass('hidden');
		    	ShowCarouselImage(".js-lazyloaded");
		    } else{
		    	load_homepage_carousels(true);
		    	$(".carousel_desktop").addClass('hidden');
		    	$(".carousel_mobile").removeClass('hidden');
		    	ShowCarouselImage(".js-lazyloaded-mobile");
		    }
		}, true);

		// load this later when the user scrolls to this part
		// $('.brands_carousel .views-element-container').slick(brandsOptions);

		// $('#lifestyle .slick-dots').appendTo('.lifestyle_slide--text');
		// $('#recipes .slick-dots').appendTo('.recipe_slide--text');


		if ($('#CouponFrame').length > 0) {
			$('#CouponFrame').iFrameResize();
		}

		$(".jsModalSignUpClose").click(function () {
			$("body").removeClass('modal--is-open-sign-up');
		});

		/* ScrollMagic Start */

		const OFFSET_AMOUNT = -200; //-400
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

		/* Frontpage claims boxes */
		if ($('.claims--row').length) {
			var staggerClaims = TweenMax.staggerFromTo('.claims--box', 0.75, STANDARD_FROM, STANDARD_TO, 0.15);

			var staggerClaimsScene = new ScrollMagic.Scene(setStandardScene('.claims--row', OFFSET_AMOUNT))
				.setTween(staggerClaims)
				.addTo(controller);
		}

		/* About Us claims boxes */
		if ($('.about-us__claims-box').length) {
			var staggerAboutUsClaims = TweenMax.staggerFromTo('.about-us__claims-box', 0.75, STANDARD_FROM, STANDARD_TO, 0.15);
			var staggerAboutUsClaimsScene = new ScrollMagic.Scene(setStandardScene('.about-us__claims-box', OFFSET_AMOUNT))
				.setTween(staggerAboutUsClaims)
				.addTo(controller);
		}

		/* Coupon block */
		if ($('section#coupons').length) {
			if ($(window).innerWidth() > 768) {
				var slideInCouponLeft = TweenMax.fromTo('.coupon--leftcol', 0.75, {
					left: '-1000px',
					opacity: '0'
				}, {
					left: '0',
					opacity: '1',
				});

				var slideInCouponRight = TweenMax.fromTo('.coupon--text', 0.75, {
					right: '-1000px',
					opacity: '0'
				}, {
					right: '0',
					opacity: '1',
				});
			}
			else {
				var slideInCouponLeft = TweenMax.fromTo('.coupon--leftcol', 0.75, STANDARD_FROM, STANDARD_TO);
				var slideInCouponRight = TweenMax.fromTo('.coupon--text', 0.75, STANDARD_FROM, STANDARD_TO);
			}

			var timeline = new TimelineMax();
			timeline.add(slideInCouponLeft).add(slideInCouponRight, 0);

			var slideInCouponsScene = new ScrollMagic.Scene(setStandardScene('section#coupons', OFFSET_AMOUNT))
				.setTween(timeline)
				.addTo(controller);
		}

		/* Brands carousel */
		if ($('section#brands').length) {
			var slideUpBrands = TweenMax.fromTo('.brands_carousel--container', 0.75, STANDARD_FROM, STANDARD_TO);

			var slideUpBrandsScene = new ScrollMagic.Scene(setStandardScene('section#brands', -300))
				.setTween(slideUpBrands)
				.addTo(controller);
		}

		/* Active contest preview boxes */
		if ($('.contest__detail-view').length) {
			var cards = document.getElementsByClassName('contest__detail-view');
			for (var i = 0; i < cards.length; i++) {
				var slideUpContestsScene = new ScrollMagic.Scene(setStandardScene(cards[i], OFFSET_AMOUNT))
					.setTween(TweenMax.fromTo(cards[i], 0.75, STANDARD_FROM, STANDARD_TO))
					.addTo(controller);
			}
		}
		/* Closed contest preview boxes */
		if ($('.contest__menu-detail-section').length) {
			var staggerContests = TweenMax.staggerFromTo('.contest__menu-detail-section', 0.75, STANDARD_FROM, STANDARD_TO, 0.15);

			var staggerContestsScene = new ScrollMagic.Scene(setStandardScene('.contest__filter-wrapper', OFFSET_AMOUNT))
				.setTween(staggerContests)
				.addTo(controller);
		}

		/* Recipe cards */
		if ($('.recipe__menu-detail-section').length && $('section#contest').length) {
			var slideUpRecipeCard = TweenMax.staggerFromTo('.recipe__menu-detail-section', 0.75, STANDARD_FROM, STANDARD_TO, 0.15);

			var slideUpRecipeCardScene = new ScrollMagic.Scene(setStandardScene('.recipe__menu-detail-wrapper', OFFSET_AMOUNT))
				.setTween(slideUpRecipeCard)
				.addTo(controller);
		}
		window.hasLoadedBrandsCarousel = false;
		window.hasLoadedArticleCarousel = false;
		window.hasLoadedRecipeCarousel = false;

		window.hasLoadedSocialBlock = false;
		if (drupalSettings.path.isFront) {
			lazyLoadSocialBlock();
		}
		/* ScrollMagic End   */

		translateRecipeFilters();
		switchToSavedLanguage();
		submitLoginFormCallback();
		setAjaxPollAttributes();
		// contestLoginFormAction();
		collapseAccordionFAQ();
		makeSignupCTAPopup();
		smallerLogoOnScroll();
		customRecipeLandingpage();
		removeParamLoginFormAction();
		removeRedBackgroundLogin();   // temporary fix: Will clean it up later, but will do for the deadline.
		signupFromExternalUrl();
		submitFormOnEnter();
		handleMenuListener(); // test screen size and turn on or off mobile menu click listener
		setRecipeFilter();
		redirectFromThankYouPage();
		clickHomeCarousel();
		window.hasLoadedSignupForm = false;

		lazyloadBrandsCarousel();
		/*if (droplang != 'en' && droplang != 'fr') {
			silentLoadSignupForm();
		}*/
	});

	function lazyLoadHomepageCarousel(js_selector){
		if (drupalSettings.path.isFront) {
			$(document).mousemove(function(e){
				// window.carousel_fullWidthOptions;
				ShowCarouselImage(js_selector);
		   	});

		   	$(document).scroll(function () {
				ShowCarouselImage(js_selector);
		   	});
		}
	}

	function ShowCarouselImage(js_selector){

		if ( window.has_loaded_home_carousel_image == 1) {
			return;
		}

		$(js_selector).each( function(index, obj) {
			var element = $(obj);
			var delay_ms =  100 + index*1000;
			window.has_loaded_home_carousel_image = 1;

			if (element.css('background-image') == 'none' ){
				setTimeout(
					function()
					{
						element.css('background-image', element.data('style'));
						// console.log(" index is " + index);
					},
			  	delay_ms);

			}


		});

	}


	function lazyloadBrandsCarousel() {
		$(document).scroll(function () {
			if ($("#brands").length == 0){
				return;
			}
			var top = $(this).scrollTop();
			var width = $(window).innerWidth()
				|| document.documentElement.clientWidth
				|| document.body.clientWidth;
			// load the carousel when a little above wrapping div
			var lang_prefix = "/" + drupalSettings.path.pathPrefix; // lang_prefix =  /en-ca/
			var loadAt = $("#brands").offset().top - 800;
			if (top > loadAt && !window.hasLoadedBrandsCarousel && (lang_prefix == '/en-us/' || lang_prefix == '/es-us/')) {
				window.hasLoadedBrandsCarousel = true;
				$(".brands_carousel").load(lang_prefix + "brandss  .views-element-container", function () {
					$('.brands_carousel .views-element-container').slick(brandsOptions);
				});
			}

			if (drupalSettings.path.isFront) {

				var recipeTriggerHeight = $("#recipes").offset().top - 700;
				if (top > recipeTriggerHeight && !window.hasLoadedRecipeCarousel) {
					window.hasLoadedRecipeCarousel = true;
					var carousel_class = 'carousel_desktop';
					if (width && width < 768) {
						carousel_class = 'carousel_mobile';
					}

					$("#recipes").load(lang_prefix + "views/carousel-2  ." + carousel_class, function () {
						$('.js-recipe-carousel').slick(fullWidthOptions);
					});
				}

				var articleTriggerHeight = $("#lifestyle").offset().top - 700;
				// if (top > articleTriggerHeight && !window.hasLoadedArticleCarousel){
				// 	 window.hasLoadedArticleCarousel = true;
				// 	$("#lifestyle").load("/views/carousel2/article  .carousel_desktop", function (){
				// 		$('.js-article-carousel').slick(fullWidthOptions);
				// 	});
				// }
			}
		});
	}

	function GetCountryCode(){
		var currentlang = $('html').attr('lang');
		if ( currentlang === 'en-us' || currentlang === 'es-us'){
			return 'usa';
		} else{
			return 'ca';
		}
	}

	function GetUserCountry(){
		var userLang = $("#user_logged").data('value')
		if ( userLang === 'en-us' || userLang === 'es-us'){
			return 'usa';
		} else{
			return 'ca';
		}
	}

	function clickHomeCarousel(){
		jQuery(".js-home-recipe").click(function(event){
			var link = jQuery(this).find(".js-recipe-cta").attr("href");
			window.location.href = link;
		});
		jQuery(".js-home-life").click(function (event) {
			var link = jQuery(this).find(".js-life-cta").attr("href");
			window.location.href = link;
		});
	}

	function lazyLoadSocialBlock() {
		// This only works if the socialpollblock_ content is edited.
		// the <img> needs to be removed and the alt and src of the image
		// should be saved as data attribute to the parent <p> element
		// Also, the <p> should have a class hidden ==> <p class="hidden">
		$(document).scroll(function () {
			var top = $(this).scrollTop();
			// This should only be executed on the home page
			var loadAt = $("#social").offset().top - 500;
			if (top > loadAt && !window.hasLoadedSocialBlock) {
				window.hasLoadedSocialBlock = true;
				// load the gif image
				var p_social = $(".social-first div p.hidden");
				var p_social_second = $(".social-second div p.hidden");
				var imgSrc = p_social.data("img");
				if (imgSrc) {
					var imgAlt = p_social.data("alt");
					p_social.removeClass("hidden");
					$(".social-first div p a").html("<img alt='" + imgAlt + "'  src='" + imgSrc + "' />");
				}
				var imgSrcSecond = p_social_second.data("img");
				if (imgSrcSecond) {
					var imgAltSecond = p_social_second.data("alt");
					p_social_second.removeClass("hidden");
					$(".social-second div p a").html("<img alt='" + imgAltSecond + "'  src='" + imgSrcSecond + "' />");
				}

			}
		});
	}

	window.newSocialBlockContent = function generateLazyloadCode(html_string) {
		// html_string = html_string.replace(/\'/g, "");
		var obj_p = jQuery(jQuery.parseHTML(html_string));

		obj_p.addClass("hidden");
		var obj_img = obj_p.find("a img");
		var src = obj_img.attr("src");
		var alt = obj_img.attr("alt");
		obj_p.attr("data-alt", alt);
		obj_p.attr("data-img", src);
		obj_p.find("a img").remove();
		obj_p.find("a").html("&nbsp;")
		return obj_p.prop("outerHTML");


	}

	function silentLoadSignupForm(){
		if (drupalSettings.path.currentPath.substring(0,7) == 'contest' ||
			drupalSettings.path.currentPath.substring(0,7) == 'concour'
		 ){
			var signupButton = document.getElementsByClassName("jsModalSignUp")[0];
			window.hideSignupForm = true;
			signupButton.click();

		}
	}

	function clickHomeCarousel() {
		jQuery(".js-home-recipe").click(function (event) {
			var link = jQuery(this).find(".js-recipe-cta").attr("href");
			window.location.href = link;
		});
		jQuery(".js-home-life").click(function (event) {
			var link = jQuery(this).find(".js-life-cta").attr("href");
			window.location.href = link;
		});
	}

	function redirectFromThankYouPage() {
		var path = window.location.pathname;
		var is_logged_in = jQuery('body').hasClass("user-logged-in");
		var hasSubmittedContest = getUrlVars()['contest']
		// if ?contest=1
		if (is_logged_in && (path.indexOf('thank-you') >= 0 || path.indexOf('merci') >= 0)
			// && (path.indexOf('contest') < 0 && path.indexOf('concour') < 0 )
			&& hasSubmittedContest !== "1"
		) {
			window.location.href = "/" + language;
		}
	}

	function submitFormOnEnter() {
		jQuery('body.path-forgot-password .contest-detail__input').keypress(function (e) {
			if (!e) { var e = window.event; }
			// Enter is pressed
			if (e.keyCode == 13) {
				jQuery('#resetpwdrequest-form .btnregister ').click();
			}

		});
	}

	function signupFromExternalUrl() {
		var url_params = window.location.search;
		if (url_params.includes('?signup=true') || url_params.includes('?inscription=true')) {
			var signupButton = document.getElementsByClassName("jsModalSignUp")[0];
			signupButton.click();
		}

		if (url_params.includes('?signin=true')) {
			var signinButton = document.getElementsByClassName("jsModalSignIn")[0];
			signinButton.click();
		}

	}

	function translateRecipeFilters() {
		if (window.language === 'fr-ca' && ($("body.path-life").length || $("body.path-recipes").length)) {
			jQuery(" a.multiselect-all label input").each(function () {
				jQuery(this).get(0).nextSibling.remove();
				jQuery(this).after("Tout sÃ©lectionner");
			});
		}

		jQuery('body.page-node-type-recipe .recipe-detail__ingredients a').each(function () {
			var target = jQuery(this).attr('target');
			if (!(target != undefined && jQuery(this).attr('target') === '_blank')
				&& jQuery(this).parents('.social-share').length === 0) {

				jQuery(this).attr('target', '_blank')
			}
		});
	}
	//~ $('body').addClass('.modal--is-open-sign-up .modal__container-sign-up');

	//~ $('a.jsModalSignUp').on('click', function(e) {
	//~ $('body').addClass('modal--is-open-sign-up');
	//~ e.stopPropagation();
	//~ });
	$('a.to-signup').on('click', function (e) {
		if ($(".modal__container-sign-up").length > 0) {
			$('body').addClass('modal--is-open-sign-up');
		} else {
			$(".jsModalSignUp").trigger('click');
		}
		e.stopPropagation();
	});

	$('.modal__sign-in-google').on('click', function (e) {
		window.open(this.href);
	});

	$('a.modal__sign-up-link').on('click', function (e) {
		$('body').removeClass('modal--is-open-sign-up');
		e.stopPropagation();
	});

	$('a.to-signup').on('click', function (e) {
		$('#fancy_login_close_button').trigger('click');
		//e.stopPropagation();
	});

	$('#fancy_login_form_contents').on('click', 'a.js-us-signup', function(e) {
		$('#fancy_login_close_button').trigger('click');
		if ($(".modal__container-sign-up").length > 0 ){
			$('body').addClass('modal--is-open-sign-up');
		} else {
			$(".jsModalSignUp").trigger('click');
		}
		e.stopPropagation();
		//e.stopPropagation();
	});

	// function message_wrapperclose(){
	// 	setTimeout(function() {
	// 	  $('.messages__wrapper').fadeOut('slow');
	// 	}, 1000);
	// }


	function removeRedBackgroundLogin() {
		if ($("body.fr").length) {
			$('.modal__social-button-sign-in button.form-submit.btn-primary').removeClass('btn-primary');
		}
	}

	/* custom options for the article and recipe pages */
	//~ $('#edit-field-article-category-target-id option[value="All"]').text('- Categories -');
	//~ $('#edit-field-article-brands-category-target-id option[value="All"]').text('- Brands -');
	//~ $('#edit-field-recipe-category-target-id option[value="All"]').text('- Courses -');
	//~ $('#edit-field-recipe-brands-category-target-id option[value="All"]').text('- Brands -');
	//~ $('#edit-field-recipe-occasions-category-target-id option[value="All"]').text('- Occasions -');

	function article_dropdown_filter() {


		/*  Categories dropdown */
		$('div.form-item-field-article-category-target-id').find(".form-select").multiselect({
			includeSelectAllOption: true,
			nonSelectedText: droplang == 'fr' ? ' - Categories - ' : droplang == 'es-us' ? ' - Categorias - ' : ' - Categories - ',
			numberDisplayed: 1
		});
		/* Brand dropdown */
		$('div.form-item-field-article-brands-category-target-id').find(".form-select").multiselect({
			includeSelectAllOption: true,
			nonSelectedText: droplang == 'fr' ? ' - Nos marques - ' : droplang == 'es-us' ? ' - Marcas - ' : ' - Brands - ',
			numberDisplayed: 1
		});
	}
	function recipe_dropdown_filter() {
		/*  Courses - recipe category dropdown */
		$('div.form-item-field-recipe-category-target-id').find(".form-select").multiselect({
			includeSelectAllOption: true,
			nonSelectedText: droplang == 'fr' ? ' - Moment de la journée - ' : droplang == 'es-us' ? ' - Platos - ' : '- Courses -',
			numberDisplayed: 1
		});
		/* Brand dropdown */
		$('div.form-item-field-recipe-brands-category-target-id').find(".form-select").multiselect({
			includeSelectAllOption: true,
			nonSelectedText: droplang == 'fr' ? ' - Nos Marques - ' : droplang == 'es-us' ? ' - Marcas - ' : ' - Brands - ',
			numberDisplayed: 1
		});
		/* Occasions dropdown */
		$('div.form-item-field-recipe-occasions-category-target-id').find(".form-select").multiselect({
			includeSelectAllOption: true,
			nonSelectedText: droplang == 'fr' ? '- Occasions -' : droplang == 'es-us' ? '- Ocasiones -' : '- Occasions -',
			numberDisplayed: 1
		});
	}
	article_dropdown_filter();
	recipe_dropdown_filter();

	if ($('html').attr('lang') === 'es-us'){
		jQuery("a.multiselect-all").find('.checkbox').html("<input type=\"checkbox\" value=\"multiselect-all\">  Seleccionar todo");
	}

	// Ajax infiniti scroll -  when finishes trigger resize event to adjust the template
	$(document).ajaxComplete(function (event, xhr, settings) {
		if (settings.url.indexOf("ajax?") >= 0 && settings.url.indexOf("_wrapper_format=drupal_ajax") >= 0) {
			article_dropdown_filter();
			recipe_dropdown_filter();
			//        if ( settings.url === "/"+lang+"/views/ajax?_wrapper_format=drupal_ajax" ) {
			$(window).trigger("resize");
		}
		if (settings.url.indexOf("node?") >= 0 && settings.url.indexOf("ajax_form=1") >= 0 && settings.url.indexOf("_wrapper_format=drupal_ajax") >= 0) {
			message_wrapperclose();
		}
		if (settings.url.indexOf("/addbulk/settings?") >= 0 && settings.url.indexOf("ajax_form=1") >= 0 && settings.url.indexOf("_wrapper_format=drupal_ajax") >= 0) {
			window.location.reload();
		}
	});


	/* Header Nav Scroll */
	$('/*header .menu a,*/ .page_top').on('click', function (e) {
		//e.preventDefault();

		$('html, body').animate({
			scrollTop: $($.attr(this, 'href')).offset().top - scrollOffset
		}, 750);

		if ($(window).width() < 981) {
			$('header .menu').slideUp('fast');
			$('.header__mobile-topnav').removeClass('menu-open');
		}
	});

	var menuHasListener = false;

	function handleMenuListener() {
		if ($(window).width() < 981 && !menuHasListener) {
			// Header hamburger menu
			$('.header__mobile-topnav').on('click', function () {
				$('header .menu').toggle('fast');
				// $('header .menu_account').slideToggle('fast');
				$(this).toggleClass('menu-open');
			});
			menuHasListener = true;
		}
	}

	$(window).resize($.debounce(100, function () {
		handleMenuListener();
	}));

	if ($('.field--name-field-choice-image').length > 0) {
		$(".field--name-field-choice-image").append('<div class="poll_spot_left"></div><div class="poll_spot_right"></div>');
		$(".field--name-field-choice-image .img-responsive").css({ 'width': '100%', 'height': '100%' });
	}

	$(".poll_spot_left").on('click', function () {
		SubmitDynamicPoll(0);
	});

	$(".poll_spot_right").on('click', function () {
		SubmitDynamicPoll(1);
	});

	function SubmitDynamicPoll(choicenumber) {
		var formchoice = $(".poll-view-form input[name='choice']").eq(choicenumber);
		formchoice.prop("checked", true).trigger("click");
		$("#edit-vote").mousedown();
		$('.field--name-field-choice-image').fadeOut(100);
	}

	/**** Social buttons ****/

	$('body').on('click', '.social-share .fa.fa-lg', function (event) {


		var face_utm = encodeURI('?utm_source=facebook&utm_medium=referral&utm_campaign=contest-share-button');
		var twit_utm = encodeURI('?utm_source=twitter&utm_medium=referral&utm_campaign=contest-share-button');
		var pint_utm = encodeURI('?utm_source=pinterest&utm_medium=referral&utm_campaign=contest-share-button');

		event.preventDefault();

		var url = $("#node_url").val()?$("#node_url").val():$('meta[property="og:url"]').attr('content');
		var title = $("#node_title").val()?$("#node_url").val():$('meta[property="og:title"]').attr('content');
		var image = $("#node_image").val()?$("#node_image").val():$('meta[property="og:image"]').attr('content');
		var pathname = window.location.origin; // Returns path only

		var contestname = $("#node_name").val();

		var urlfb = url;
		var urltw = url;
		var urlpt = url;
		var theLanguage = $('html').attr('lang');
		if (contestname > '' && $('#socialshareicons').length == 0) {


			if (theLanguage == 'fr') {
				urlfb = location.protocol + "//" + location.host + "/cntfrfb/" + contestname + "/";
				urltw = location.protocol + "//" + location.host + "/cntfrtw/" + contestname + "/";
				urlpt = location.protocol + "//" + location.host + "/cntfrpt/" + contestname + "/";
			}
			if (theLanguage == 'en') {
				urlfb = location.protocol + "//" + location.host + "/cntenfb/" + contestname + "/";
				urltw = location.protocol + "//" + location.host + "/cntentw/" + contestname + "/";
				urlpt = location.protocol + "//" + location.host + "/cntenpt/" + contestname + "/";
			}
			if (theLanguage == 'en-us') {
				urlfb = location.protocol + "//" + location.host + "/cntenusfb/" + contestname + "/";
				urltw = location.protocol + "//" + location.host + "/cntenustw/" + contestname + "/";
				urlpt = location.protocol + "//" + location.host + "/cntenuspt/" + contestname + "/";
			}
			if (theLanguage == 'es-us') {
				urlfb = location.protocol + "//" + location.host + "/cntesusfb/" + contestname + "/";
				urltw = location.protocol + "//" + location.host + "/cntesustw/" + contestname + "/";
				urlpt = location.protocol + "//" + location.host + "/cntesuspt/" + contestname + "/";
			}
		}
		else{
			if ($('#socialshareicons').length > 0 ){
				title = $("#socialshareiconstext").val();
				urlfb = location.protocol + "//" + location.host + "/" + theLanguage + "/fb" + $('#socialshareicons').val();
				urltw = location.protocol + "//" + location.host + "/" + theLanguage + "/tw" + $('#socialshareicons').val();
				urlpt = location.protocol + "//" + location.host + "/" + theLanguage + "/pt" + $('#socialshareicons').val();
				if ($('#socialshareiconscontest').length > 0 ){
					urlfb = location.protocol + "//" + location.host + "/" + theLanguage + "/fb" + $('#socialshareicons').val() + "/" + $('#socialshareiconscontest').val();
					urltw = location.protocol + "//" + location.host + "/" + theLanguage + "/tw" + $('#socialshareicons').val() + "/" + $('#socialshareiconscontest').val();
					urlpt = location.protocol + "//" + location.host + "/" + theLanguage + "/pt" + $('#socialshareicons').val() + "/" + $('#socialshareiconscontest').val();
				}
			}
		}

                if($(this).hasClass('fa-facebook')){
                	safeOpenRedirect('https://www.facebook.com/sharer/sharer.php?u='+urlfb, 'Share Facebook', 'height=300, width=500');
                }

                if($(this).hasClass('fa-twitter')){
                	safeOpenRedirect('http://twitter.com/share?text='+title+' '+urltw, 'Share Twitter', 'height=300, width=500');
                }

                if($(this).hasClass('fa-pinterest')){
                        if (image.indexOf(pathname) >= 0){
                			safeOpenRedirect('http://www.pinterest.com/pin/create/button/?url='+urlpt+'&media='+image+'&description='+title, 'Share Pinterest', 'height=300, width=500');
                        }else{
                			safeOpenRedirect('http://www.pinterest.com/pin/create/button/?url='+urlpt+'&media='+pathname+image+'&description='+title, 'Share Pinterest', 'height=300, width=500');
			}
		}
		if ($(this).hasClass('fa-envelope')) {
			window.location.href = mailto;
		}
		if ($(this).hasClass('fa-print')) {
			window.print();
		}
	});

	/**** Social buttons End****/
	function removeParamLoginFormAction() {
		var current_url = window.location.pathname;
		var loginForm_action = $("form.fancy-login-user-login-form").attr('action');

		if (loginForm_action && loginForm_action.includes("?")) {
			var base_url_action = loginForm_action.split('?')[0]
			$("form.fancy-login-user-login-form").attr('action', base_url_action);
		}
	}

	function submitLoginFormCallback() {

		$(document).on('keypress', function (e) {
			var active_element_id = $(document.activeElement).attr('id');
			if (e.which == 13 && (active_element_id == 'edit-name' || active_element_id == 'edit-pass')) {
				$('.modal__sign-in-select-button button').trigger('mousedown');
			}
		});

		$('#fancy_login_user_login_block_wrapper .js-form-submit').mousedown(function () {
			$(".messages__wrapper .close span").trigger('click');
		});
		/*
$(document).bind('DOMNodeInserted', function(e) {
	if ($('.login-error-msg').length == 0 && $('.messages__wrapper ul.item-list--messages').length) {
		var message = ''
		if (window.language === 'en-ca' || window.language == 'en-us'){
			message = 'Oops, looks like the email and password does not match our records.';
		} else if (window.language == 'fr-ca'){
			message = "Oups, l'adresse courriel ou le mot de passe ne correspondent pas Ã  ceux dans notre base de donnÃ©es";
		}
		$('.messages__wrapper ul.item-list--messages').append('<li class="login-error-msg"> '  +  message +  ' </li>');
	}
	// }
});
		*/
		// fancy_login_form_contents
	}

	function setAjaxPollAttributes() {
		if ($("div.signika").hasClass("animated")) {
			$(".social-first").removeClass("ajax-poll");
		}
		var hidden_option = '_EMPTY_';
		// Hide some poll options translation
		$(".vote-form div.js-form-type-radio:contains('" + hidden_option + "')").addClass("hidden");
	}

	function contestLoginFormAction() {
		var current_url = window.location.pathname;
		if (current_url.includes("/contests/")) {
			var new_action = '/' + current_url.split('/')[1] + '/login-action/' + current_url.split('/')[3]
			$("form.fancy-login-user-login-form").attr('action', new_action)
			$(".fancy-login-user-login-form .js-form-submit").mousedown(function (e) {

				e.preventDefault();
				$(".fancy-login-user-login-form").submit();
			});
		}
	}
	function smallerLogoOnScroll() {
		$(document).scroll(function () {

			let always_small = window.location.pathname.includes('/officialrules') ||
				window.location.pathname.includes('/my-account');

			if (always_small) {
				return
			}

			var top = $(document).scrollTop();
			var shrinkOn = 0; // number of pixels before shrinking

			if (top > shrinkOn) {
				$('.header__logo-container').addClass("smaller");
			} else {
				if ($('.header__logo-container').hasClass("smaller")) {
					$('.header__logo-container').removeClass("smaller");
				}
			}
		});
	}
	function customRecipeLandingpage() {
		$("body.path-life .views-exposed-form label.checkbox, body.path-recipes .views-exposed-form label.checkbox").click(function () {
			$(this).closest(".btn-group").removeClass("open");
		});
		// Make description clickable for mobile devices.
		// $(".recipe-wrapper").click(function(event){
		// 	if (window.innerWidth > 414) {
		// 		event.preventDefault();
		// 	}
		// });
	}

	function getUrlVars() {
		var vars = {};
		var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function (m, key, value) {
			vars[key] = value;
		});
		return vars;
	}

	function setRecipeFilter() {
		if (window.location.pathname.includes("/recipes") || window.location.pathname.includes("/recette")) {
			var occasions = {
				'gameday': {
					'fr-ca': 'Jour de match',
					'en-ca': 'Game Day',
					'en-us': 'Game Day',
					'es-us': 'Día de Juego',
				},

			}


			if (getUrlVars()['filter']) {
				var filter = getUrlVars()['filter'];
				var label = occasions[filter][window.language];
				if ($("label[title='" + label + "']").find("input").length) {
					$("label[title='" + label + "']").find("input").click();
					// $("#edit-submit-recipes").click();
					$(".recipe__filter-wrapper form .js-form-submit").click();
				}
			}

			if (getUrlVars()['brands']) {
				var brands = drupalSettings.customalteration;
				var brand_filter = getUrlVars()['brands'];
				var label = brands[brand_filter];
				if ($("label[title='" + label + "']").find("input").length) {
					$("label[title='" + label + "']").find("input").click();
					$(".recipe__filter-wrapper form .js-form-submit").click();
				}
			}
		}
	}

	/* Popup country selection*/

	$('.countryselected').on("click", function (e) {

		const token = Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15);
		e.preventDefault();
		var selectedlang = $(this).data('value');
		// $.cookie("rememberlang", selectedlang, { expires : 30 , path    : '/'});
		document.location.href = "/" + selectedlang + "?rememberlang=" + selectedlang + "&ui=" + token;
	});


	function setCountryLanguage(lang) {
	}

	if ($(".language_switcher--backdrop").length > 0) {
		$(".language_switcher--backdrop").css("display", "flex");
	}
	/* Popup country selection end*/
	function collapseAccordionFAQ() {
		$(".faq-border  a.faq-plus-minus").click(function (event) {
			event.preventDefault();
			$(this).closest("h4.faq-title").next().css("display:block");
		});
	}

	function makeSignupCTAPopup() {
		let user_logged = false;
		if ($("body.user-logged-in").length > 0) {
			user_logged = true;
		}

		if ($('.slick-current .btn_red').length > 0) {
			$('.slick-current .btn_red').click(function (event) {
				var href = $(this).attr('href');
				if (href.includes('sign-up')) {
					event.preventDefault();
					user_logged || $("body").addClass("modal--is-open-sign-up");
				}
			});
		}

		if ($('.banner_slide .btn_red').length > 0) {
			$('.banner_slide .btn_red').click(function (event) {
				var href = $(this).attr('href');
				if (href.includes('sign-up')) {
					event.preventDefault();
					user_logged || $("body").addClass("modal--is-open-sign-up");
				}
			});
		}

		$('.recipe-signup').click(function (event) {
			user_logged || $("body").addClass("modal--is-open-sign-up");
		});
	}

	function getCookie(name) {
		var value = "; " + document.cookie;
		var parts = value.split("; " + name + "=");
		if (parts.length == 2) return parts.pop().split(";").shift();
	}

	function switchToSavedLanguage() {
		if (window.location.pathname == '/') {
			if (getCookie('rememberlang')) {
				var currentlang = getCookie('rememberlang');
				window.location.href = window.location.pathname + currentlang
			} else {
				$('.language_switcher--backdrop').removeClass('hidden');
			}

		}
	}


	/**********************************************
	*** LANGUAGE SWITCHER FOOTER DROPDOWN START
	**********************************************/

	function get_matching_url(country_lang, current_url) {
		var url_map = {
			'/life' : {
				'en-us' : '/en-us/life',
				'es-us' : '/es-us/modo-de-vida',
				'en-ca' : '/en-ca/life',
				'fr-ca' : '/fr-ca/mode-de-vie',
			},
			'/recipes': {
				'en-us': '/en-us/recipes',
				'es-us': '/es-us/recetas',
				'en-ca': '/en-ca/recipes',
				'fr-ca': '/fr-ca/recettes',
			},
		}
		var english_url = current_url
		// Translate french urls to english
		if (current_url == '/recettes' || current_url == '/recetas') english_url = '/recipes'
		if (current_url == '/mode-de-vie' || current_url == '/modo-de-vida') english_url = '/life'
		// If no matching url just return to homepage of USA/Canada
		if (url_map[english_url] == undefined) return '/' + country_lang


		return url_map[english_url][country_lang]

	}

	$(document).ready(function () {
		$('.footer_country--wrapper .dropdown').append('<div class="button" tabindex="0"></div>');
		$('.footer_country--wrapper .dropdown').append('<ul class="select-list"></ul>');

		$('.footer_country--wrapper .dropdown select option').each(function () {
			if (!$(this).attr('disabled')) {
				var bg = $(this).css('background-image');
				$('.select-list').append('<li tabindex="0" class="clsAnchor"><span value="' + $(this).val() + '" class="' + $(this).attr('class') + '" style=background-image:' + bg + '>' + $(this).text() + '</span></li>');
			}
		});

		$('.footer_country--wrapper .dropdown .button').html('<span style=background-image:' +
			$('.footer_country--wrapper .dropdown select').find(':selected').css('background-image') + '>' +
			$('.footer_country--wrapper .dropdown select').find(':selected').text() +
			'</span>' +
			'<i class="select-list-link arrow-down"></i>'
		);

		$('.footer_country--wrapper .dropdown ul li').each(function () {
			if ($(this).find('span').text() == $('.footer_country--wrapper .dropdown select').find(':selected').text()) {
				$(this).addClass('active');
			}
		});

		/* Clicking on selection in fake dropdown */
		/* Changing hidden select value and adjusting fake dropdown display */
		$('.footer_country--wrapper .dropdown .select-list span').on('click', function () {
			var dd_text = $(this).text();
			var dd_img = $(this).css('background-image');
			var dd_val = $(this).attr('value');

			$('.footer_country--wrapper .dropdown .button').html('<span style=background-image:' + dd_img + '>' + dd_text + '</span>' + '<i class="select-list-link arrow-down"></i>');
			$('.footer_country--wrapper .dropdown .select-list span').parent().removeClass('active');
			$(this).parent().addClass('active');

			// Setting hidden select value
			$('.footer_country--wrapper .dropdown select[name=countries_footer]').val(dd_val);

			$('.footer_country--wrapper .dropdown .select-list li').slideUp();
		});

		/* Open/close the fake dropdown */
		$('.footer_country--wrapper .dropdown .button').on('click', function () {
			$('i.select-list-link').toggleClass('arrow-down arrow-up');
			$('.footer_country--wrapper .dropdown ul li').slideToggle();
		});

		$('.footer_country--wrapper .dropdown .select-list span').on('click', function () {
			var theActiveLang = $('html').attr('lang');
			var theNewLang = $(this)[0].className;

			if (theNewLang != theActiveLang) {
				if ($(this)[0].className == 'en' || $(this)[0].className == 'en-us') {
					theNewLang = theNewLang == 'en' ? 'en-ca' : theNewLang;
					// url_map
					var pathname = window.location.pathname
					var current_page = pathname.split(window.language)[1]

					// document.location.href = "/" + theNewLang;
					document.location.href = get_matching_url(theNewLang, current_page)
				}
			}
		});

		/****************************/
		var langlink = $('#active_link').data("id");
		if (langlink != null) {
			if (langlink == 'en') {
				langlink = 'fr';
			} else if (langlink == 'fr') {
				langlink = 'en';
                    }else if(langlink == 'en-us') {
                        langlink = 'es-us';
                    }else if(langlink == 'es-us') {
                        langlink = 'en-us';
			}

                    var newlink = $(".language-link[hreflang=" + langlink +"]" );
                    if (newlink != null){
                        var lang_link = adjustUrls(newlink.attr("href"));
                        $('#active_link').attr("href",lang_link);
                    }
		}
		/****************************/


		/**********************************************
		*** SHOW LOGIN POPUP IF NOT LOGGED IN START
		**********************************************/
		if (typeof showloginpopup !== 'undefined' && showloginpopup == true ) {
			$('.jsModalSignIn').trigger('click');
		}
		/**********************************************
		*** SHOW LOGIN POPUP IF NOT LOGGED IN END
		**********************************************/

	});

	//adjust the url translation for some links
	function adjustUrls(lang_link) {
		var new_url = lang_link;

            if (lang_link == "/fr-ca/recipes"){
                new_url = lang_link.replace("recipes", "recettes");
		} else if (lang_link == "/en-ca/recettes") {
				new_url = lang_link.replace("recettes", "recipes");


		} else if (lang_link == "/en-ca/mode-de-vie") {
			new_url = lang_link.replace("mode-de-vie", "life");
		} else if (lang_link == "/fr-ca/life") {
				new_url = lang_link.replace("life", "mode-de-vie");

			}else if (lang_link == "/en-us/recetas"){
				new_url = lang_link.replace("recetas", "recipes");
			}else if (lang_link == "/es-us/recipes"){
				new_url = lang_link.replace("recipes", "recetas");

            }else if (lang_link == "/es-us/life"){
				new_url = lang_link.replace("life", "articulos");
            }else if (lang_link == "/en-us/articulos"){
				new_url = lang_link.replace("articulos", "life");

			}
		return new_url;
	}

	/**********************************************
	*** LANGUAGE SWITCHER FOOTER DROPDOWN END
	**********************************************/


	$("#edit-pass").after("<span toggle=\"#password-field\" class=\"field-icon toggle-password\"></span>");


	/**********************************************
	 *** PASSWORD VIEW
	 **********************************************/

	$(document).on('click', '.toggle-password', function () {
		if ($(".toggle-password").hasClass("hide-password")) {
			$(".toggle-password").removeClass("hide-password");
			$("#edit-pass").attr("type", "password");
		} else {
			$(".toggle-password").addClass("hide-password");
			$("#edit-pass").attr("type", "text");
		}
	});

	$(document).on('click', '.sign_in', function () {
		$(".toggle-password").removeClass("hide-password");
		$("#edit-pass").attr("type", "password");
		showOktaSignIn();
	});


	$(document).on('click', '.sign_up', function () {
		$(".toggle-password-signup").removeClass("hide-password");
		$("#password").attr("type", "password");
		$("#contest-password").attr("type", "password");

	});

	$(".toggle-password-signup").click(function () {
		if ($(".toggle-password-signup").hasClass("hide-password")) {
			$(".toggle-password-signup").removeClass("hide-password");
			$("#password").attr("type", "password");
			$("#contest-password").attr("type", "password");
		} else {
			$(".toggle-password-signup").addClass("hide-password");
			$("#password").attr("type", "text");
			$("#contest-password").attr("type", "text");
		}
	});

	function executeDelayed() {
		//show the social block after few secods
		$("#social").fadeIn(2000);
	}
	var delay = 5;
	setTimeout(executeDelayed(), delay * 1000);


	$('#edit-submit-recipes--2').on("click", function (e) {
		console.log("Search....");
		var termtosearch = $("#edit-combine--2").val();
		if (termtosearch.length > 0) {
			console.log("Search Inserted....");
			window.dataLayer = window.dataLayer || [];
			window.dataLayer.push({
				'event': 'search',
				'searchTerm': termtosearch
			});
		}
	});

	function safeOpenRedirect(url_to_open, name, specs = 'height=300, width=500'){
    	if (validateURL(url_to_open)){
            window.open(url_to_open, name, specs);
    	}
	}

	function validateURL(current_url) {
        var url = parseURL(current_url);
        var urlHostname = url.hostname.trim();
        var allowed_domains = ['facebook.com', 'www.facebook.com', 'twitter.com', 'pinterest.com', 'www.pinterest.com'];

        if (urlHostname == '') {
            return true;
        }
        else {
            if (urlHostname.toUpperCase() == location.hostname.trim().toUpperCase()) {
                return true;
            } else if(allowed_domains.includes(urlHostname.toLowerCase()) ){
            	return true;
            }
            else
                return false;
        }
    }

    function parseURL(url) {
        var a = document.createElement('a');
        // a.href = url;
        return {
            source: url,
            protocol: a.protocol.replace(':', ''),
            hostname: a.hostname,
            host: a.host,
            port: a.port,
            query: a.search,
            params: (function () {
                var ret = {},
                    seg = a.search.replace(/^\?/, '').split('&'),
                    len = seg.length, i = 0, s;
                for (; i < len; i++) {
                    if (!seg[i]) { continue; }
                    s = seg[i].split('=');
                    ret[s[0]] = s[1];
                }
                return ret;
            })(),
            file: (a.pathname.match(/\/([^\/?#]+)$/i) || [, ''])[1],
            hash: a.hash.replace('#', ''),
            path: a.pathname.replace(/^([^\/])/, '/$1'),
            // relative: (a.href.match(/tps?:\/\/[^\/]+(.+)/) || [, ''])[1],
            segments: a.pathname.replace(/^\//, '').split('/')
        };
    }

	$('.trigger_sign_in_popup').on("click", function (e) {
		console.log("Triggers");
		e.preventDefault();
		$( "#signinpopup" ).trigger( "click" );
	});

}(jQuery, window));/*end of file*/

