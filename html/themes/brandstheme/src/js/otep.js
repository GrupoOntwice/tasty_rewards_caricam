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

(function ($, window) {

  $(document).ready(function () {

    // Temporary fix for Hero button link
    $(".banner_slide--text .btn_carousel").attr("href", "#products")

    // Otep Animation Animation

    function isOtepScrolledIntoView(elem) {
      let docOtepViewTop = $(window).scrollTop();
      let docOtepViewBottom = docOtepViewTop + $(window).height();

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

      return ((elemBottom <= docOtepViewBottom) && (elemTop >= docOtepViewTop));
    }

    $(".otep-align-left, .otep-align-right, .main-coupon-left, .main-coupon-right").css("visibility", "hidden");


    $(window).on('scroll', function () {

      $(".otep-align-left, .main-coupon-left").each(function () {
        if (isOtepScrolledIntoView($(this))) {
          $(this).css("visibility", "visible").addClass("animate__fadeInLeft");
        }
      });

      $(".otep-align-right, .main-coupon-right").each(function () {
        if (isOtepScrolledIntoView($(this))) {
          $(this).css("visibility", "visible").addClass("animate__fadeInRight");
        }
      });

    });

    // Nutrition Show and Hide Animation

    $(".fa-swap").click(function () {
      $(this).toggleClass('fa-minus');
      $(this).parent().parent().parent().children('.nutrition-copy').toggle('.d-none');
    });


    // Ingredients Show and Hide Animation

    $('.ingredients-copy').css('display','none');
    $(".fa-ingredients-swap").click(function () {
      $(this).toggleClass('fa-minus');
      $(this).parent().parent().parent().children('.ingredients-copy').toggle('.d-none');
    });

    // Smooth Scroll Effect

    var firstNavClick = true;

    $("#otep-about-us-nav, #products-nav").on('click', function(event) {

      event.preventDefault();
      let navAnimationHeightOffset = $("#brands_navigation").height();
      if(firstNavClick) {
        firstNavClick = false;
        navAnimationHeightOffset += 75;
      }

      if ($(window).width() < 768) {
        navAnimationHeightOffset = 0;
      }

      const id = this.hash.substring(1);
      const yOffset = navAnimationHeightOffset;
      const element = document.getElementById(id);
      const y = element.getBoundingClientRect().top + window.pageYOffset - yOffset;


      window.scrollTo({top: y, behavior: 'smooth'});

    });


  });

  $(".otep-buy").click(function(){
    $(".otep-navbar__vertical-align").toggleClass("index");
  });

  $(".closeModal").click(function(){
    $(".otep-navbar__vertical-align").toggleClass("index");
  });

  // $("#otep-buy-left").click(function(){
  //   $(".otep-navbar__vertical-align").toggleClass("index");
  // });
  //
  // $("#closeModal").click(function(){
  //   $(".otep-navbar__vertical-align").toggleClass("index");
  // });

}(jQuery, window));/*end of file*/

