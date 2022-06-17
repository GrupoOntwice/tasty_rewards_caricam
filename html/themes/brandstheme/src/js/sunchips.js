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


    // Multi item Carousel - Featured - Related recipes etc

    $('#sunchips-related-products-carousel').carousel({
      interval: false
    });

    if ($(window).width() >= 768) {

      $('#sunchips-related-products-carousel .carousel-inner').each(function () {

        if ($(this).children('div').length <= 2) $(this).siblings('#sunchips-related-products-carousel .carousel-indicators, #sunchips-related-products-carousel .carousel-control-prev, #sunchips-related-products-carousel .carousel-control-next').hide();

      });

      $('#sunchips-related-products-carousel .carousel-item').each(function () {
        let next = $(this).next();
        if (!next.length) {
          next = $(this).siblings(':first');
        }
        next.children(':first-child').clone().appendTo($(this));

        for (let i = 0; i < 1; i++) {
          next = next.next();
          if (!next.length) {
            next = $(this).siblings(':first');
          }
          next.children(':first-child').clone().appendTo($(this));
        }
      });


      $('#sunchips-related-products-carousel').on('slide.bs.carousel', function (event) {
        if ( event.direction == "right") {
          $(event.relatedTarget).children(":last-child").hide();
        } else {
          let prev = $(event.relatedTarget).prev();
          if (!prev.length) {
            prev = $(event.relatedTarget).siblings(':last');
          }
          prev.children(":last-child").hide();
        }
      })

      $('#sunchips-related-products-carousel').on('slid.bs.carousel', function (event) {
        if ( event.direction == "right") {
          $(event.relatedTarget).children(":last-child").show();
        } else {
          let prev = $(event.relatedTarget).prev();
          if (!prev.length) {
            prev = $(event.relatedTarget).siblings(':last');
          }
          prev.children(":last-child").show();
        }
      })

    }


    //shrink logo for navbar
    function growShrinkSunChipsLogo() {
      var Logo = document.getElementById("sunchips-logo")
      if (document.body.scrollTop > 5 || document.documentElement.scrollTop > 5) {
        Logo.style.width = '75px';
        Logo.style.height = '59px';
        $('.sunchips-logo').addClass('m-auto');
      } else {
        Logo.style.width = '100px';
        Logo.style.height = '79px';
        $('.sunchips-logo').removeClass('m-auto');
      }
    }

    window.onscroll = function() {
      growShrinkSunChipsLogo()
    };

    // Delete it later on
    $('.alert-danger').hide();

  });

  // BuyNow Modal
  $("#sunchips-buy").click(function(){
    $(".sunchips-navbar__vertical-align").toggleClass("index");
  });

  $("#closeModal").click(function(){
    $(".sunchips-navbar__vertical-align").toggleClass("index");
  });


}(jQuery, window));/*end of file*/
