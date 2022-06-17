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

  // VR Product Buttons Show & Hide

  $(document).ready(function () {

    $('.products-item-text .btn_fritolay').each(function(){
      // For each "View Product" button, get the value in .fritolay-link (from views)
      // 
      // var link = $(this).closest(".products-item-text").siblings('.fritolay-link').find('a').attr('href');
      if (typeof link === 'undefined'){
        // $(this).addClass('hidden');
        // $(this).closest(".products-item-text").siblings(".products-item-image").addClass("not-clickable");
        // $('.not-clickable a').click(function() { return false; });
      }else{
        // @TODO: the fritolay iframe issue may be coming from here
        // $(this).attr('href', link);
        // $(this).closest(".products-item-text").siblings(".products-item-image").find('a').attr('href', link);
      }
    });
      
      $('.not-clickable a').click(function() { return false; });

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

    $(".product-align-left, .product-align-right, .products-title, .video-title, .video-item, .vr-coupon-left, .vr-coupon-right").css("visibility", "hidden");


    $(window).on('scroll', function () {
      $(".product-align-left, .vr-coupon-left").each(function () {
        if (isScrolledIntoView($(this))) {
          $(this).css("visibility", "visible").addClass("animate__fadeInLeft");
        }
      });
      $(".product-align-right, .vr-coupon-right").each(function () {
        if (isScrolledIntoView($(this))) {
          $(this).css("visibility", "visible").addClass("animate__fadeInRight");
        }
      });
      $(".products-title, .video-title, .video-item").each(function () {
        if (isScrolledIntoView($(this))) {
          $(this).css("visibility", "visible").addClass("animate__fadeInUp");
        }
      });
    });

    // Moves images above the products-item-text when image align on the right
    $('.product-align-right .products-item-text').each(function () {
      $(this).insertAfter($(this).parent().find('.products-item-image'));
    });


    /** Code for Bas Animation **/

    /** INITIALIZE TIMELINE **/
    var tl = new TimelineLite({paused: true});

    /** DEFINE ANIMATIONS **/

    tl.to('.vr-bag-lays', 6, {
        bezier: {
          type: "soft",
          values: [{x: 0, y: 0, rotation: 0}, {x: 240, y: 100, rotation: 6}, {x: 380, y: 900, rotation: 12}],
        },
        ease: Linear.easeNone
      },
      0);

    tl.to('.vr-bag-doritos', 6, {
        bezier: {
          type: "soft",
          values: [{x: 0, y: 0, rotation: 0}, {x: 50, y: 200, rotation: 5}, {x: 200, y: 800, rotation: 10}],
        },
        ease: Linear.easeNone
      },
      0);

    tl.to('.vr-bag-ruffles', 6, {
        bezier: {
          type: "soft",
          values: [{x: 0, y: 0, rotation: 0}, {x: -100, y: 400, rotation: -5}, {x: -150, y: 830, rotation: -10}],
        },
        ease: Linear.easeNone
      },
      0);

    tl.to('.vr-bag-cheetos', 6, {
        bezier: {
          type: "soft",
          values: [{x: 0, y: 0, rotation: 0}, {x: -200, y: 400, rotation: -14}, {x: -250, y: 900, rotation: -25}],
        },
        ease: Linear.easeNone
      },
      0);

    /** ACTIVATE ANIMATION **/
    $(window).scroll(function () {
      let windowScroll;
      let st = $(this).scrollTop();
      let ht = $('.vr-bag-box').height();
      if (st < ht && st > 0) {
        if ($(window).width() > 769) {
          windowScroll = (st / ht) / 1.01;
        }  else if ($(window).width() > 1480) {
          windowScroll = (st / ht);
        } else {
          windowScroll = (st / ht);
        }
        tl.progress(windowScroll);
      }
    });

    // Smooth Scroll Effect

    $("#vr-products").on('click', function(event) {

      if (this.hash === "") {
        event.preventDefault();
        var hash = this.hash;
        $('html, body').animate({
          scrollTop: $(hash).offset().top
        }, 300, function(){
          window.location.hash = hash;
        });
      }
    });

    $("#check-us-out").on('click', function(event) {

      if (this.hash === "") {
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

  $(".fritolay-buy").click(function(){
    $(".fritolay-navbar__vertical-align").toggleClass("index");
  });

  $(".closeModal").click(function(){
    $(".fritolay-navbar__vertical-align").toggleClass("index");
  });

  const backgroundClick = document.querySelectorAll(".banner_slide");

for (let i = 0; i < backgroundClick.length; i++) {
    let el = backgroundClick[i];

    el.addEventListener("click", function () {
        let button = this.querySelector(".btn_fritolay");
        button.click();
    })
    el.addEventListener("click", function () {
        let button = this.querySelector(".btn_transparent");
        button.click();
    })
}

}(jQuery, window));/*end of file*/

