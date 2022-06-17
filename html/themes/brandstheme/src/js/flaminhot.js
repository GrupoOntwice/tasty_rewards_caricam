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

    // flaminhot Youtube Video

    var $toActivateVideo = $(".activate-video");

    function activateVideos() {
      $toActivateVideo.off().on('click', function () {
        let key = $(this).data('key');
        let iframe = '<div class="flaminhot-video-iframe-container"><iframe src="https://www.youtube.com/embed/' + key + '?autoplay=1&rel=0" frameborder="0" allow="autoplay" allowfullscreen></iframe><div>';
        $(this).closest('.flaminhot-video-box').prepend(iframe);
        $(this).closest('.flaminhot-video-box').find('.flaminhot-video-preimg').remove();
        return false;
      });
    }

    activateVideos();

    // flaminhot Animation Animation

    function isFlaminhotScrolledIntoView(elem) {
      let docFlaminHotViewTop = $(window).scrollTop();
      let docFlaminHotViewBottom = docFlaminHotViewTop + $(window).height();

      let AnimationHeightOffset = 90;
      if ($(window).width() < 769) {
        AnimationHeightOffset = 65;
      } else if ($(window).width() < 480) {
        AnimationHeightOffset = 40;
      } else if ($(window).width() < 380) {
        AnimationHeightOffset = 16;
      }

      let elemTop = $(elem).offset().top - AnimationHeightOffset;
      let elemBottom = elemTop + $(elem).height() - AnimationHeightOffset;

      return ((elemBottom <= docFlaminHotViewBottom) && (elemTop >= docFlaminHotViewTop));
    }

    // $(".flaminhot-align-left, .flaminhot-align-right, .main-coupon-left, .main-coupon-right").css("visibility", "hidden");
    $(".product-copy-col, .flaminhot-align-left, .flaminhot-align-right, .main-coupon-left, .main-coupon-right").css("visibility", "hidden");


    $(window).on('scroll', function () {

      $(".main-coupon-left").each(function () {
        if (isFlaminhotScrolledIntoView($(this))) {
          $(this).css("visibility", "visible").addClass("animate__fadeInLeft");
        }
      });

      $(".main-coupon-right").each(function () {
        if (isFlaminhotScrolledIntoView($(this))) {
          $(this).css("visibility", "visible").addClass("animate__fadeInRight");
        }
      });

      $(".flaminhot-align-left").each(function () {
        if (isFlaminhotScrolledIntoView($(this))) {
          $(this).css("visibility", "visible").addClass("animate__zoomInLeft");
        }
      });

      $(".flaminhot-align-right").each(function () {
        if (isFlaminhotScrolledIntoView($(this))) {
          $(this).css("visibility", "visible").addClass("animate__zoomInRight");
        }
      });

      $(".product-copy-col").each(function () {
        if (isFlaminhotScrolledIntoView($(this))) {
          $(this).css("visibility", "visible").addClass("animate__fadeIn");
        }
      });


    });

    // Nutrition Show and Hide Animation

    $(".fa-swap").click(function () {
      $(this).toggleClass('fa-minus');
      $(this).parent().parent().parent().children('.nutrition-copy').toggle('.d-none');
    });


    //homepage background click to target the button


    const backgroundClick = document.querySelectorAll(".banner_slide");

    for (let i = 0; i < backgroundClick.length; i++) {
      let el = backgroundClick[i];

      el.addEventListener("click", function () {
        let button = this.querySelector(".btn_carousel");
        button.click();
      })
      el.addEventListener("click", function () {
        let button = this.querySelector(".btn_transparent");
        button.click();
      })
    }


// Smooth Scroll Effect

    var firstNavClick = true;

    $("#flaminhot-nav-products").on('click', function (event) {

      event.preventDefault();
      let navAnimationHeightOffset = $("#brands_navigation").height();
      if (firstNavClick) {
        firstNavClick = false;
        navAnimationHeightOffset += 75;
      }

      var navScrollPadding
      if ($(window).width() < 768) {
        navAnimationHeightOffset = 0;
        navScrollPadding = 0;
      } else {
        navScrollPadding = 30;
      }

      const id = this.hash.substring(1);
      const yOffset = navAnimationHeightOffset;
      const element = document.getElementById(id);
      const y = element.getBoundingClientRect().top + window.pageYOffset - yOffset - navScrollPadding;


      window.scrollTo({top: y, behavior: 'smooth'});

    });

    $('#flaminhot-feature-recipe, #flaminhot-related-recipe').carousel({
      interval: false
    });

    if ($(window).width() >= 768) {

      $('#flaminhot-feature-recipe .carousel-inner, #flaminhot-related-recipe .carousel-inner').each(function () {

        if ($(this).children('div').length <= 2) $(this).siblings('#flaminhot-feature-recipe .carousel-indicators, #flaminhot-feature-recipe .carousel-control-prev, #flaminhot-feature-recipe .carousel-control-next, #flaminhot-related-recipe .carousel-indicators, #flaminhot-related-recipe .carousel-control-prev, #flaminhot-related-recipe .carousel-control-next').hide();

      });

      $('#flaminhot-feature-recipe .carousel-item, #flaminhot-related-recipe .carousel-item').each(function () {
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


      $('#flaminhot-feature-recipe, #flaminhot-related-recipe').on('slide.bs.carousel', function (event) {
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

      $('#flaminhot-feature-recipe, #flaminhot-related-recipe').on('slid.bs.carousel', function (event) {
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

    // Multiple products carousels

    $('#flaminhot-made-with-carousel .carousel-inner').each(function () {

      if ($(this).children('.carousel-item').length === 1) $(this).siblings('#flaminhot-made-with-carousel .carousel-indicators, #flaminhot-made-with-carousel .carousel-control-prev, .carousel-control-next').hide();

    });


    $('.made-with-image-control-next').on('click', function(e) {
      e.preventDefault()
      $('.flaminhot-recipe-made-with-row .carousel').carousel('next')
    })

    $('.made-with-image-control-prev').on('click', function(e) {
      e.preventDefault()
      $('.flaminhot-recipe-made-with-row .carousel').carousel('prev')
    })

    // Delete this later

    $('.alert-danger').hide();

  });


  $(".flaminhot-buy").click(function(){
    $(".flaminhot-navbar__vertical-align").toggleClass("index");
    $(".product-copy-col").toggleClass('btn-index')
  });

  $(".closeModal").click(function(){
    $(".flaminhot-navbar__vertical-align").toggleClass("index");
    $(".product-copy-col").toggleClass("btn-index");
  });

}(jQuery, window));/*end of file*/

