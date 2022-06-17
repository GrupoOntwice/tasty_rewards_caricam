/* eslint-disable */

/*
 * jQuery throttle / debounce - v1.1 - 3/7/2010
 * http://benalman.com/projects/jquery-throttle-debounce-plugin/
 *
 * Copyright (c) 2010 "Cowboy" Ben Alman
 * Dual licensed under the MIT and GPL licenses.
 * http://benalman.com/about/license/
 */
(function (b, c) {
  var $ = b.jQuery || b.Cowboy || (b.Cowboy = {}), a;
  $.throttle = a = function (e, f, j, i) {
    var h, d = 0;
    if (typeof f !== "boolean") {
      i = j;
      j = f;
      f = c
    }

    function g() {
      var o = this, m = +new Date() - d, n = arguments;

      function l() {
        d = +new Date();
        j.apply(o, n)
      }

      function k() {
        h = c
      }

      if (i && !h) {
        l()
      }
      h && clearTimeout(h);
      if (i === c && m > e) {
        l()
      } else {
        if (f !== true) {
          h = setTimeout(i ? k : l, i === c ? e - m : e)
        }
      }
    }

    if ($.guid) {
      g.guid = j.guid = j.guid || $.guid++
    }
    return g
  };
  $.debounce = function (d, e, f) {
    return f === c ? a(d, e, false) : a(d, f, e !== false)
  }
})(this);

window.runSlickBrandCarousel = function runcarousel(){
  if (arguments.length != 2){
    return;
  }

  var selector = arguments[0];
  var slick_options = arguments[1];

  if ($(selector).length > 0 ){
    $(selector).slick(slick_options);
  }
}


$(document).ready(function () {

	 /* Nav white border fix on login */
  $(window).scroll(function () {
    const scroll = $(window).scrollTop();
    if (scroll > 0) {
      $('#toolbar-item-administration-tray .toolbar-lining').hide();
    } else {
      $('#toolbar-item-administration-tray .toolbar-lining').show();
    }
  })
    var ad_name = ("-" + $(".ads-name").text() ) || "";

    if (!localStorage.getItem("ads_banner_cookie" + ad_name)){
      $("#ads-unit").show("slow");
    }

    $(".modal__close-ads-banner").click(function(){
      $("#ads-unit").addClass("hidden");
      localStorage.setItem("ads_banner_cookie" + ad_name, 1);
    });



  /* Remove disabled class on sign in button after the page load */
  $('.jsModalSignIn').removeClass('disabled');

  /* Header Nav Scroll */
  $('/*header .menu a,*/ .page_top').on('click', function (e) {
    //e.preventDefault();
    $('html, body').animate({
      scrollTop: $($.attr(this, 'href')).offset().top - scrollOffset
    }, 750);

    if ($(window).width() < 992) {
      $('header .menu').slideUp('fast');
      $('.header__mobile-topnav').removeClass('menu-open');
    }
  });

  var menuHasListener = false;

  function handleMenuListener() {
    if ($(window).width() < 992 && !menuHasListener) {
      // Header hamburger menu
      $('.header__mobile-topnav').on('click', function () {
        $('header .menu').toggle('fast');
        // $('header .menu_account').slideToggle('fast');
        $(this).toggleClass('menu-open');
      });
      menuHasListener = true;
    }
  }

  handleMenuListener();

  $(window).resize($.debounce(100, function () {
    handleMenuListener();
  }));


  /* Brands Header Nav Scroll */


  $('.top-logo').on('click', function (e) {

    //e.preventDefault();
    $('html, body').animate({
      scrollTop: $($.attr(this, 'href')).offset().top - scrollOffset
    }, 750);

    if ($(window).width() < 992) {
      $('nav .brands-navbar__ul, nav .brands-navbar__social, nav .brands-navbar_accounts-mbl').slideUp('fast');
      $('.brands-navbar__mobile-topnav').removeClass('ham-active');
    }
  });

  var menuBrandHasListener = false;

  function handleBrandMenuListener() {
    if ($(window).width() < 992 && !menuBrandHasListener) {
      // Header hamburger menu
      $('.brands-navbar__mobile-topnav').on('click', function () {
        $('nav .brands-navbar__ul, nav .brands-navbar__social, nav .brands-navbar_accounts-mbl').toggle('fast');
        $(this).toggleClass('ham-active');
        $('#brands_navigation hr').toggleClass('d-none');
      });
      menuBrandHasListener = true;
    }
  }

  handleBrandMenuListener();

  $(window).resize($.debounce(100, function () {
    handleBrandMenuListener();
  }));

  /* Fixed Nav Scroll Effect*/

  $(window).scroll(function () {
    const scroll = $(window).scrollTop();

    if (scroll >= $("#brands_navigation").height()) {
      $("#brands_navigation").addClass("brand-navbar__move-top brands-navbar__fixed");
    } else {
      $(".brands-navbar__fixed").removeClass("brand-navbar__move-top brands-navbar__fixed");
    }
  });

  /* Hide TR navbar in mobile view*/

  $(window).resize(function () {
    if ($(window).width() < 992) {
      // $('#tasty-theme').addClass('hidden');
      $('.navbar-default').css("display", "none");
    } else {
      // $('#tasty-theme').removeClass('hidden');
      $('.navbar-default').css("display", "flex");
    }
  }).resize();

  // TR Navbar in Mobile
  $('#button-mbl-back').on('click', function (e) {
    $('.navbar-default').hide().css('display' + 'none');
    // $('#brands_navigation').show();
    $('#brands_navigation').removeClass('hidden');
    $('.brands-navbar__mobile-topnav').addClass('ham-active');
    $('.brands-navbar__ul, .brands-navbar_accounts-mbl').css('display', 'block');
  });

    $('.product-align-right .products-item-text').each(function () {
    $(this).insertAfter($(this).parent().find('.products-item-image'));
  });

  $('.tr-navbar-trigger-mobile').on('click', function () {
    $('#brands_navigation').addClass('hidden');
    $('.navbar-default').show();
  });

});


(function ($, window) {
  'use strict';

  const fullWidthOptions = {
    dots: true,
    autoplay: true,
    autoplaySpeed: 5000,
    fade: true,
    pauseOnHover: false
  };
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


  const recipeOptions = {
    infinite: true,
    slidesToShow: 3,
    slidesToScroll: 1
  };

  const quakerProductsOptions = {
    infinite: true,
    slidesToShow: 1,
    slidesToScroll: 1
  };

  Drupal.behaviors.myBehavior = {
    attach: function (context, settings) {
      // window.setCardImgSizes();
    }
  };

  $(document).ready(function () {

    // $('#button-mbl-back').on('click', function (e) {
    //   $('.navbar-default').hide().css('display' + 'none');
    //   $('.brands_navigation').show();
    //   $('.brands-navbar__mobile-topnav').addClass('ham-active');
    // });

    $(".jsModalSignUpClose").click(function () {
      $("body").removeClass('modal--is-open-sign-up');
    });
    //
    // $('.carousel_fullwidth.carousel_desktop.js-main-carousel').slick(fullWidthOptions);
    // $('.js-recipe-carousel').slick(recipeOptions);
    // $('.js-occasion-carousel').slick(recipeOptions);
    // $('.js-product-carousel').slick(recipeOptions);

      //Carousel for homepage, recipe and lifestyle page
    var screen_width = $(window).innerWidth()
      || document.documentElement.clientWidth
      || document.body.clientWidth;

    const urlParams = new URLSearchParams(window.location.search);
    const slider_state = urlParams.get('slider');

    if (screen_width > 768) {
      if ( $(".carousel_fullwidth.carousel_desktop").length > 0 ){
        if (slider_state !== 'off'){
          $('.carousel_fullwidth.carousel_desktop.js-main-carousel').slick(fullWidthOptions);
        }
        $('.carousel_fullwidth.carousel_desktop.js-recipe-carousel').slick(fullWidthOptions);
        $('.carousel_fullwidth.carousel_desktop.js-article-carousel').slick(fullWidthOptions);
      }
    } else {
      if ( $(".carousel_fullwidth.carousel_mobile").length > 0 ){
        if (slider_state !== 'off'){
          $('.carousel_fullwidth.carousel_mobile.js-main-carousel').slick(fullWidthOptions);
        }
        $('.carousel_fullwidth.carousel_mobile.js-recipe-carousel').slick(fullWidthOptions);
        $('.carousel_fullwidth.carousel_mobile.js-article-carousel').slick(fullWidthOptions);
      }
    }

    if ($('.brands_carousel .views-element-container').length > 0 ){
      $('.brands_carousel .views-element-container').slick(brandsOptions);
    }
    // load this later when the user scrolls to this part
    // $('.quaker-products .js-product-carousel').slick(quakerProductsOptions);

    $('#lifestyle .slick-dots').appendTo('.lifestyle_slide--text');
    $('#recipes .slick-dots').appendTo('.recipe_slide--text');


    // window.hasLoadedBrandsCarousel = false;
    // window.hasLoadedArticleCarousel = false;
    // window.hasLoadedRecipeCarousel = false;

    // window.hasLoadedSocialBlock = false;
    // if (drupalSettings.path.isFront) {
    //   lazyLoadSocialBlock();
    // }
    /* ScrollMagic End   */


    lazyloadBrandsCarousel();
    setTranslationUrl();
    // silentLoadSignupForm();

  });
  // $(window).resize($.debounce(100, function () {
  //   let carousel_class_desktop = 'carousel_desktop';
  //   let carousel_class_mobile = 'carousel_mobile';
  //
  //   // if ($(window).width() < 768) {
  //   //   if ( )
  //   // } else {
  //   //
  //   // }
  // }));

  /* Brands carousel */
  if ($('section#brands').length && $(".quaker-feed-canada").length == 0 ) {
    var slideUpBrands = TweenMax.fromTo('.brands_carousel--container', 0.75, STANDARD_FROM, STANDARD_TO);

    var slideUpBrandsScene = new ScrollMagic.Scene(setStandardScene('section#brands', -300))
      .setTween(slideUpBrands)
      .addTo(controller);
  }


  function lazyloadBrandsCarousel() {
    $(document).resize(function () {
      var top = $(this).scrollTop();
      var width = $(window).innerWidth()
        || document.documentElement.clientWidth
        || document.body.clientWidth;
      // load the carousel when a little above wrapping div
      var lang_prefix = "/" + drupalSettings.path.pathPrefix; // lang_prefix =  /en-ca/
      var loadAt = $("#brands_navigation").offset().top - 500;
      if (top > loadAt && !window.hasLoadedBrandsCarousel) {
        window.hasLoadedBrandsCarousel = true;
        $(".brands_carousel").load(lang_prefix + "brands  .views-element-container", function () {
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
        if (top > articleTriggerHeight && !window.hasLoadedArticleCarousel) {
          window.hasLoadedArticleCarousel = true;
          $("#lifestyle").load("/views/carousel2/article  .carousel_desktop", function () {
            $('.js-article-carousel').slick(fullWidthOptions);
          });
        }
      }
    });
  }

  function setTranslationUrl(){
      var current_url = window.location.pathname;
      if (drupalSettings.path && drupalSettings.path.currentLanguage){
        var current_lang = drupalSettings.path.currentLanguage;
        var translated_langcode = current_lang == 'en'? 'fr-ca' : 'en-ca';
        var translated_url = current_url.replace(current_lang + "-ca", translated_langcode);
        $(".js-translation").attr('href', translated_url);
        if ($(".node-url-translation").length){
          $(".js-translation").attr('href', $(".node-url-translation").data('url'));
        }
      } else if (current_url.indexOf("tostitos/products-categories") !== -1  
                || current_url.indexOf("tostitos/produits-categories") !== -1){
          var translated_langcode = window.lang_prefix == 'en-ca'? 'fr-ca' : 'en-ca';
          var translated_url = current_url.replace(window.lang_prefix, translated_langcode);
          $(".js-translation").attr('href', translated_url);
      }

      if ($('.js-no-translation').length ){
        $(".js-translation").attr('href', 'javascript:void(0);');
      }

  }

  // function loadSocial(){
  //   console.log("Load Social");
  //   $.ajax({
  //     'url': Drupal.url('brands/lays/socialfeeds'),
  //     //'async': false,
  //     'success': function (response)  {
  //       console.log(response);
  //       $('.lays-social-wall').html('');
  //       $('.lays-social-wall').append(response);
        /*
        if (response[3] !== undefined) {
          var viewHtml = response[3].data;
          // Remove previous articles and add the new ones.
          console.log(viewHtml);
          $('.lays-social-content-wrapper').html('');
          $('.lays-social-content-wrapper').append(viewHtml);

          // Attach Latest settings to the behaviours and settings.
          // it will prevent the ajax pager issue
          Drupal.settings = response[0].settings;
          drupalSettings.views = response[0].settings.views;
          Drupal.attachBehaviors($('.lays-social-content-wrapper')[0], Drupal.settings);
        }
        */
  //       }
  //     }
  //   );

  // }
  // loadSocial();

  $('body').on('click', '.social-share-trigger', function(event) {
		event.preventDefault();
		var url   = $("#node_url").val();
		var title = $("#node_title").val();
		var image = $("#node_image").val();
		var pathname = window.location.origin; // Returns path only

		var urlfb = url;
		var urltw = url;
		var urlpt = url;

		switch($(this).data('platform')) {
		  case 'facebook':
			  safeOpenRedirect('https://www.facebook.com/sharer/sharer.php?u='+urlfb, 'Share Facebook','height=300, width=500');
        
			break;
		  case 'twitter':
      // window.open('http://twitter.com/home?status='+title+' '+urltw, 'Share Twitter','height=300, width=500');
        safeOpenRedirect('http://twitter.com/intent/tweet?url='+urltw+'&text='+title, 'Share Twitter','height=300, width=500');
			break;
		  case 'pinterest':
			if (image.indexOf("media.tastyrewards.ca") >= 0){
				safeOpenRedirect('http://www.pinterest.com/pin/create/button/?url='+urlpt+'&media='+image+'&description='+title, 'Share Pinterest', 'height=300, width=500');
			}else{
				safeOpenRedirect('http://www.pinterest.com/pin/create/button/?url='+urlpt+'&media='+pathname+image+'&description='+title, 'Share Pinterest', 'height=300, width=500');
			}
			break;
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



}(jQuery, window));/*end of file*/

