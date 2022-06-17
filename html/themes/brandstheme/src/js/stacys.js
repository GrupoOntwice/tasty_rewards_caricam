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

    $('.brands-navbar__mobile-topnav').on('click', function() {
      $('.brands-navbar_accout-options').toggleClass('hide');
      $('.tr-navbar-trigger-mobile').toggleClass('hide');
    })
    //
    // TR Navbar in Mobile
    $('#button-mbl-back').on('click', function (e) {
      $('.navbar-default').hide().css('display' + 'none');
      // $('#brands_navigation').show();
      $('#brands_navigation').removeClass('hidden');
      $('.brands-navbar__mobile-topnav').addClass('ham-active');
      $('.brands-navbar__ul, .brands-navbar_accounts-mbl').css('display', 'block');
    });

    $('.tr-navbar-trigger-mobile').on('click', function () {
      $('#brands_navigation').addClass('hidden');
      // $('.navbar-default').show();
      $('.navbar-default').attr('style','display:block !important');
    });

    setTranslationUrl();

    document.querySelector(".toupload").innerText = "";

    // About Us Video
    var $toActivateVideo = $(".activate-video");

    function activateAboutUsVideo() {
      $toActivateVideo.off().on('click', function () {
        // let key = $(this).data('key');
        var aboutUsVideoValue=jQuery('.stacys-video-preimg').children().attr("data-key");
        let iframe = '<div class="stacys-video-iframe-container"><iframe src="https://www.youtube.com/embed/' + aboutUsVideoValue + '?autoplay=1&rel=0" frameborder="0" allow="autoplay" allowfullscreen></iframe><div>';
        $(this).closest('.stacys-video-box').prepend(iframe);
        $(this).closest('.stacys-video-box').find('.stacys-video-preimg').remove();
        return false;
      });
    }

    activateAboutUsVideo();

    // Multi item Carousel - Featured - Related recipes etc

    $('#stacys-feature-recipe, #stacys-related-recipe').carousel({
      interval: false
    });

    if ($(window).width() >= 768) {

      $('#stacys-feature-recipe .carousel-inner, #stacys-related-recipe .carousel-inner').each(function () {

        if ($(this).children('div').length <= 2) $(this).siblings('#stacys-feature-recipe .carousel-indicators, #stacys-feature-recipe .carousel-control-prev, #stacys-feature-recipe .carousel-control-next, #stacys-related-recipe .carousel-indicators, #stacys-related-recipe .carousel-control-prev, #stacys-related-recipe .carousel-control-next').hide();

      });

      $('#stacys-feature-recipe .carousel-item, #stacys-related-recipe .carousel-item').each(function () {
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


      $('#stacys-feature-recipe, #stacys-related-recipe').on('slide.bs.carousel', function (event) {
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

      $('#stacys-feature-recipe, #stacys-related-recipe').on('slid.bs.carousel', function (event) {
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

    $('.alert-danger').hide();

    // Nutrition Show and Hide Animation

    $(".fa-nutrition-icon .fa-swap").click(function () {
      $(this).toggleClass('stacys-fa-cirle-down');
      $(this).parent().parent().parent().children('.nutrition-copy').toggle('.d-none');
    });

    // Nutrition Show and Hide Animation

    $(".fa-ingredients-icon .fa-swap").click(function () {
      $(this).toggleClass('stacys-fa-cirle-down');
      $(this).parent().parent().parent().children('.ingredients-copy').toggle('.d-none');
    });


  });

  const backgroundClick = document.querySelectorAll(".banner_slide");

  for (let i = 0; i < backgroundClick.length; i++) {
      let el = backgroundClick[i];

      el.addEventListener("click", function () {
          let button = this.querySelector(".btn_red");
          button.click();
      })
      el.addEventListener("click", function () {
          let button = this.querySelector(".btn_transparent");
          button.click();
      })
  }

  $("#stacys-buy").click(function(){
    $(".stacys-navbar__vertical-align").toggleClass("index");
  });

  $("#closeModal").click(function(){
    $(".stacys-navbar__vertical-align").toggleClass("index");
  });


}(jQuery, window));/*end of file*/
