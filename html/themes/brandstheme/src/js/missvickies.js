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




    /**
     * ------------------------------------------------------------------------
     * Youtube Placeholder
     * ------------------------------------------------------------------------
     */

    $('.mv-yt-placeholder').click(function(){
      var mvYtVideo ='<iframe width = "560" height = "349" src = "'+ $(this).attr('data-video') +'" frameBorder = "0" allow="autoplay" allowfullscreen> </iframe>'
      $(this).replaceWith(mvYtVideo);
    });

    /**
     * ------------------------------------------------------------------------
     * Homepage background click to target the button
     * ------------------------------------------------------------------------
     */

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


    /**
     * ------------------------------------------------------------------------
     * Signatures Page Selectable div & Hide/Show content
     * ------------------------------------------------------------------------
     */

    /**
     * ------------------------------------------------------------------------
     * Miss Vickies Videos
     * ------------------------------------------------------------------------
     */

    var $toActivateVideoEn1 = $(".activate-video-en-1");
    var $toActivateVideoFr1 = $(".activate-video-fr-1");

    var $toActivateVideoEn2 = $(".activate-video-en-2");
    var $toActivateVideoFr2 = $(".activate-video-fr-2");

    var $toActivateVideoEn3 = $(".activate-video-en-3");
    var $toActivateVideoFr3 = $(".activate-video-fr-3");

    var $toActivateVideoEn4 = $(".activate-video-en-4");
    var $toActivateVideoFr4 = $(".activate-video-fr-4");

    $(function() {
      $toActivateVideo();
    });

    function $toActivateVideo() {

        //Video 1
        $toActivateVideoEn1.off().on('click', function () {
          var iframe = '<div class="missvickies-video-iframe-container"><iframe src="https://www.youtube.com/embed/bWX3bE5RkEs?autoplay=1&rel=0" frameborder="0" allow="autoplay" allowfullscreen></iframe><div>';
          $(this).closest('.missvickies-video-box').prepend(iframe);
          $(this).closest('.missvickies-video-box').find('.missvickies-video-preimg').remove();
          return false;
        });
        $toActivateVideoFr1.off().on('click', function () {
          var iframe = '<div class="missvickies-video-iframe-container"><iframe src="https://www.youtube.com/embed/LcItstbyc3k?autoplay=1&rel=0" frameborder="0" allow="autoplay" allowfullscreen></iframe><div>';
          $(this).closest('.missvickies-video-box').prepend(iframe);
          $(this).closest('.missvickies-video-box').find('.missvickies-video-preimg').remove();
          return false;
        });

        //Video 2
        $toActivateVideoEn2.off().on('click', function () {
          var iframe = '<div class="missvickies-video-iframe-container"><iframe src="https://www.youtube.com/embed/qIY8d0DJWtM?autoplay=1&rel=0" frameborder="0" allow="autoplay" allowfullscreen></iframe><div>';
          $(this).closest('.missvickies-video-box').prepend(iframe);
          $(this).closest('.missvickies-video-box').find('.missvickies-video-preimg').remove();
          return false;
        });
        $toActivateVideoFr2.off().on('click', function () {
          var iframe = '<div class="missvickies-video-iframe-container"><iframe src="https://www.youtube.com/embed/kM6JQr_7eVg?autoplay=1&rel=0" frameborder="0" allow="autoplay" allowfullscreen></iframe><div>';
          $(this).closest('.missvickies-video-box').prepend(iframe);
          $(this).closest('.missvickies-video-box').find('.missvickies-video-preimg').remove();
          return false;
        });

        //Video 3
        $toActivateVideoEn3.off().on('click', function () {
          var iframe = '<div class="missvickies-video-iframe-container"><iframe src="https://www.youtube.com/embed/1aUxK9dzERk?autoplay=1&rel=0" frameborder="0" allow="autoplay" allowfullscreen></iframe><div>';
          $(this).closest('.missvickies-video-box').prepend(iframe);
          $(this).closest('.missvickies-video-box').find('.missvickies-video-preimg').remove();
          return false;
        });
        $toActivateVideoFr3.off().on('click', function () {
          var iframe = '<div class="missvickies-video-iframe-container"><iframe src="https://www.youtube.com/embed/-1Q9KYug81A?autoplay=1&rel=0" frameborder="0" allow="autoplay" allowfullscreen></iframe><div>';
          $(this).closest('.missvickies-video-box').prepend(iframe);
          $(this).closest('.missvickies-video-box').find('.missvickies-video-preimg').remove();
          return false;
        });

        //Video 4
        $toActivateVideoEn4.off().on('click', function () {
          var iframe = '<div class="missvickies-video-iframe-container"><iframe src="https://www.youtube.com/embed/lKrzBWM4eV4?autoplay=1&rel=0" frameborder="0" allow="autoplay" allowfullscreen></iframe><div>';
          $(this).closest('.missvickies-video-box').prepend(iframe);
          $(this).closest('.missvickies-video-box').find('.missvickies-video-preimg').remove();
          return false;
        });
        $toActivateVideoFr4.off().on('click', function () {
          var iframe = '<div class="missvickies-video-iframe-container"><iframe src="https://www.youtube.com/embed/bJxGf5EVJUA?autoplay=1&rel=0" frameborder="0" allow="autoplay" allowfullscreen></iframe><div>';
          $(this).closest('.missvickies-video-box').prepend(iframe);
          $(this).closest('.missvickies-video-box').find('.missvickies-video-preimg').remove();
          return false;
        });

    }







    // var $toActivateVideoEn1 = $(".activate-video-en-1");
    // var $toActivateVideoFr1 = $(".activate-video-fr-1");
    //
    // var $toActivateVideoEn2 = $(".activate-video-en-2");
    // var $toActivateVideoFr2 = $(".activate-video-fr-2");
    //
    // var $toActivateVideoEn3 = $(".activate-video-en-3");
    // var $toActivateVideoFr3 = $(".activate-video-fr-3");
    //
    // var $toActivateVideoEn4 = $(".activate-video-en-4");
    // var $toActivateVideoFr4 = $(".activate-video-fr-4");
    //
    // $(function() {
    //   $toActivateVideo();
    // });
    //
    // function $toActivateVideo() {
    //   if ($(window).width() > 992) {
    //     $('.missvickies-video-thumbnails a').click(function () {
    //       if (!$(this).hasClass('active')) {
    //
    //         $('.missvickies-video-thumbnails a').removeClass('active');
    //         $(this).addClass('active');
    //
    //         $('.missvickies-main-video').html('<iframe type="text/html" src="https://www.youtube.com/embed/'+$(this).attr('href')+'" allowfullscreen frameborder="0"></iframe>');
    //
    //       }
    //       return false;
    //     });
    //
    //     $('.missvickies-video-thumbnails a:first').click();
    //
    //   } else{
    //
    //     //Video 1
    //     $toActivateVideoEn1.off().on('click', function () {
    //       var iframe = '<div class="missvickies-video-iframe-container"><iframe src="https://www.youtube.com/embed/bWX3bE5RkEs?autoplay=1&rel=0" frameborder="0" allow="autoplay" allowfullscreen></iframe><div>';
    //       $(this).closest('.missvickies-video-box').prepend(iframe);
    //       $(this).closest('.missvickies-video-box').find('.missvickies-video-preimg').remove();
    //       return false;
    //     });
    //     $toActivateVideoFr1.off().on('click', function () {
    //       var iframe = '<div class="missvickies-video-iframe-container"><iframe src="https://www.youtube.com/embed/LcItstbyc3k?autoplay=1&rel=0" frameborder="0" allow="autoplay" allowfullscreen></iframe><div>';
    //       $(this).closest('.missvickies-video-box').prepend(iframe);
    //       $(this).closest('.missvickies-video-box').find('.missvickies-video-preimg').remove();
    //       return false;
    //     });
    //
    //     //Video 2
    //     $toActivateVideoEn2.off().on('click', function () {
    //       var iframe = '<div class="missvickies-video-iframe-container"><iframe src="https://www.youtube.com/embed/qIY8d0DJWtM?autoplay=1&rel=0" frameborder="0" allow="autoplay" allowfullscreen></iframe><div>';
    //       $(this).closest('.missvickies-video-box').prepend(iframe);
    //       $(this).closest('.missvickies-video-box').find('.missvickies-video-preimg').remove();
    //       return false;
    //     });
    //     $toActivateVideoFr2.off().on('click', function () {
    //       var iframe = '<div class="missvickies-video-iframe-container"><iframe src="https://www.youtube.com/embed/kM6JQr_7eVg?autoplay=1&rel=0" frameborder="0" allow="autoplay" allowfullscreen></iframe><div>';
    //       $(this).closest('.missvickies-video-box').prepend(iframe);
    //       $(this).closest('.missvickies-video-box').find('.missvickies-video-preimg').remove();
    //       return false;
    //     });
    //
    //     //Video 3
    //     $toActivateVideoEn3.off().on('click', function () {
    //       var iframe = '<div class="missvickies-video-iframe-container"><iframe src="https://www.youtube.com/embed/1aUxK9dzERk?autoplay=1&rel=0" frameborder="0" allow="autoplay" allowfullscreen></iframe><div>';
    //       $(this).closest('.missvickies-video-box').prepend(iframe);
    //       $(this).closest('.missvickies-video-box').find('.missvickies-video-preimg').remove();
    //       return false;
    //     });
    //     $toActivateVideoFr3.off().on('click', function () {
    //       var iframe = '<div class="missvickies-video-iframe-container"><iframe src="https://www.youtube.com/embed/-1Q9KYug81A?autoplay=1&rel=0" frameborder="0" allow="autoplay" allowfullscreen></iframe><div>';
    //       $(this).closest('.missvickies-video-box').prepend(iframe);
    //       $(this).closest('.missvickies-video-box').find('.missvickies-video-preimg').remove();
    //       return false;
    //     });
    //
    //     //Video 4
    //     $toActivateVideoEn4.off().on('click', function () {
    //       var iframe = '<div class="missvickies-video-iframe-container"><iframe src="https://www.youtube.com/embed/lKrzBWM4eV4?autoplay=1&rel=0" frameborder="0" allow="autoplay" allowfullscreen></iframe><div>';
    //       $(this).closest('.missvickies-video-box').prepend(iframe);
    //       $(this).closest('.missvickies-video-box').find('.missvickies-video-preimg').remove();
    //       return false;
    //     });
    //     $toActivateVideoFr4.off().on('click', function () {
    //       var iframe = '<div class="missvickies-video-iframe-container"><iframe src="https://www.youtube.com/embed/bJxGf5EVJUA?autoplay=1&rel=0" frameborder="0" allow="autoplay" allowfullscreen></iframe><div>';
    //       $(this).closest('.missvickies-video-box').prepend(iframe);
    //       $(this).closest('.missvickies-video-box').find('.missvickies-video-preimg').remove();
    //       return false;
    //     });
    //   }
    // }

    /**
     * ------------------------------------------------------------------------
     * Miss Vickies Coupon Animation
     * ------------------------------------------------------------------------
     */

    $(".signatures-select-col").hover(
      function () {
        $(this).addClass('border-signatures-selected-arrow-hover');
      },
      function () {
        $(this).removeClass("border-signatures-selected-arrow-hover");
      }
    );

    $('.signatures-select-block-bg-1 .signatures-select-block-bg').addClass('border-signatures-selected');
    $('.signatures-select-block-bg-1').addClass('border-signatures-selected-arrow');


    $('.mv-signatures-sidebyside-block-0, .mv-signatures-sidebyside-block-2').hide();

    $(".signatures-select-block-bg-0").click(function(){

      $('.signatures-select-block-bg-0 .signatures-select-block-bg').addClass('border-signatures-selected');
      $('.signatures-select-block-bg-1 .signatures-select-block-bg, .signatures-select-block-bg-2 .signatures-select-block-bg').removeClass('border-signatures-selected');

      $('.signatures-select-block-bg-0').addClass('border-signatures-selected-arrow');
      $('.signatures-select-block-bg-1, .signatures-select-block-bg-2').removeClass('border-signatures-selected-arrow');

      $('.mv-signatures-sidebyside-block-0').show();
      $('.mv-signatures-sidebyside-block-1, .mv-signatures-sidebyside-block-2').hide();

    });

    $(".signatures-select-block-bg-1").click(function(){

      $('.signatures-select-block-bg-1 .signatures-select-block-bg').addClass('border-signatures-selected');
      $('.signatures-select-block-bg-0 .signatures-select-block-bg, .signatures-select-block-bg-2 .signatures-select-block-bg').removeClass('border-signatures-selected');

      $('.signatures-select-block-bg-1').addClass('border-signatures-selected-arrow');
      $('.signatures-select-block-bg-0, .signatures-select-block-bg-2').removeClass('border-signatures-selected-arrow');

      $('.mv-signatures-sidebyside-block-1').show();
      $('.mv-signatures-sidebyside-block-0, .mv-signatures-sidebyside-block-2').hide();

    });

    $(".signatures-select-block-bg-2").click(function(){

      $('.signatures-select-block-bg-2 .signatures-select-block-bg').addClass('border-signatures-selected');
      $('.signatures-select-block-bg-0 .signatures-select-block-bg, .signatures-select-block-bg-1 .signatures-select-block-bg').removeClass('border-signatures-selected');

      $('.signatures-select-block-bg-2').addClass('border-signatures-selected-arrow');
      $('.signatures-select-block-bg-0, .signatures-select-block-bg-1').removeClass('border-signatures-selected-arrow');

      $('.mv-signatures-sidebyside-block-2').show();
      $('.mv-signatures-sidebyside-block-0, .mv-signatures-sidebyside-block-1').hide();

    });

    function isMvScrolledIntoView(elem) {
      let docMvViewTop = $(window).scrollTop();
      let docMvViewBottom = docMvViewTop + $(window).height();

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

      return ((elemBottom <= docMvViewBottom) && (elemTop >= docMvViewTop));
    }

    $(".main-coupon-left, .main-coupon-right, .video-title, .video-item").css("visibility", "hidden");


    $(window).on('scroll', function () {

      $(".main-coupon-left").each(function () {
        if (isMvScrolledIntoView($(this))) {
          $(this).css("visibility", "visible").addClass("animate__fadeInLeft");
        }
      });

      $(".main-coupon-right").each(function () {
        if (isMvScrolledIntoView($(this))) {
          $(this).css("visibility", "visible").addClass("animate__fadeInRight");
        }
      });

      $(".video-title, .video-item").each(function () {
        if (isMvScrolledIntoView($(this))) {
          $(this).css("visibility", "visible").addClass("animate__fadeInUp");
        }
      });

    });



  });


  let totalItems = $('.missvickies-featured-carousel-item').length;
  if(totalItems == 1) {
    $('#missvickies-prev').addClass('d-none');
    $('#missvickies-next').addClass('d-none');
  }


  // Buy Now Modal
  $("#missvickies-buy").click(function(){
    $(".missvickies-navbar__vertical-align").toggleClass("index");
  });

  $("#closeModal").click(function(){
    $(".missvickies-navbar__vertical-align").toggleClass("index");
  });

}(jQuery, window));/*end of file*/

