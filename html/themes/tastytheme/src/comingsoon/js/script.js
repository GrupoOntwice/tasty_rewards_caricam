/* eslint-disable */
(function ($, window) {
	const fullWidthOptions = {};
	const brandsOptions = {
		slidesToShow: 5,

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

	$(document).ready(() => {
		$('.carousel_fullwidth').slick(fullWidthOptions);
		$('.brands_carousel').slick(brandsOptions);
	});


	/* Header Nav Scroll */
	$('/*header .menu a,*/ .page_top').on('click', function (e) {
		e.preventDefault();

		$('html, body').animate({
			scrollTop: $($.attr(this, 'href')).offset().top - scrollOffset
		}, 750);

		if ($(window).width() < 769) {
			$('header .menu').slideUp('fast');
			$('.header__mobile-topnav').removeClass('menu-open');
		}
	});

	if ($(window).width() < 769) {
		// Header hamburger menu
		$('.header__mobile-topnav').on('click', function () {
			$('header .menu').slideToggle('fast');
			$('header .menu_account').slideToggle('fast');
			$(this).toggleClass('menu-open');
		});
	}

	// $(window).resize(function () {
	// 	if ($(window).width() < 769) {
	// 		headerNav.css({ 'display': 'block' });
	// 		headerNav.hide();
	// 	}
	// 	else {
	// 		headerNav.css({ 'display': 'flex' });
	// 	}
	// });

}(jQuery, window));

