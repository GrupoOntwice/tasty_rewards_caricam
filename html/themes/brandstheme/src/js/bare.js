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

    $('body').addClass('bare');

    // Bare Animation Animation

    function isBareScrolledIntoView(elem) {
      let docBareViewTop = $(window).scrollTop();
      let docBareViewBottom = docBareViewTop + $(window).height();

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

      return ((elemBottom <= docBareViewBottom) && (elemTop >= docBareViewTop));
    }

    $(".bare-align-left, .bare-align-right, .main-coupon-left, .main-coupon-right").css("visibility", "hidden");


    $(window).on('scroll', function () {

      $(".bare-align-left, .main-coupon-left").each(function () {
        if (isBareScrolledIntoView($(this))) {
          $(this).css("visibility", "visible").addClass("animate__fadeInLeft");
        }
      });

      $(".bare-align-right, .main-coupon-right").each(function () {
        if (isBareScrolledIntoView($(this))) {
          $(this).css("visibility", "visible").addClass("animate__fadeInRight");
        }
      });

    });

    // Nutrition Show and Hide Animation

    $(".fa-swap").click(function () {
      $(this).toggleClass('fa-minus');
      $(this).parent().parent().parent().children('.nutrition-copy').toggle('.d-none');
    });

    // Smooth Scroll Effect

    $("#products").on('click', function(event) {

      if (this.hash !== "") {
        event.preventDefault();
        var hash = this.hash;
        $('html, body').animate({
          scrollTop: $(hash).offset().top
        }, 300, function(){
          window.location.hash = hash;
        });
      }
    });

    $("#social-block2").on('click', function(event) {

      if (this.hash !== "") {
        event.preventDefault();
        var hash = this.hash;
        $('html, body').animate({
          scrollTop: $(hash).offset().top
        }, 800, function(){
          window.location.hash = hash;
        });
      }
    });

  });

  $(".bare-buy").click(function(){
    $(".bare-navbar__vertical-align").toggleClass("index");
  });

  $(".closeModal").click(function(){
    $(".bare-navbar__vertical-align").toggleClass("index");
  });


}(jQuery, window));/*end of file*/

