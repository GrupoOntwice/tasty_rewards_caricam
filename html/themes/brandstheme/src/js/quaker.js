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

  const carouselCampbell = {
    dots: false,
    infinite: true,
    speed: 1000,
    autoplay:false,
    slidesToShow: 3,
    slidesToScroll: 1,
    responsive: [
      {
        breakpoint: 1024,
        settings: {
          slidesToShow: 3,
          slidesToScroll: 1,
        }
      },
      {
        breakpoint: 600,
        settings: {
          slidesToShow: 2,
          slidesToScroll: 1
        }
      },
      {
        breakpoint: 480,
        settings: {
          slidesToShow: 1,
          slidesToScroll: 1
        }
      }
    ]
  };


  $(document).ready(function () {

    // $('#quaker-campbell-carousel').slick(carouselCampbell);
    window.runSlickBrandCarousel('#quaker-campbell-carousel', carouselCampbell);

    $('.product-categories-filter').change(function () {
      var value = $(this).val();
      var link = $('.product-categories-filter [value="' + value + '"]').data('link');
      window.location.href = link;

    });


    /**
     * ------------------------------------------------------------------------
     * Youtube Placeholder Script
     * ------------------------------------------------------------------------
     */


    $('#quakers-product-categories .carousel-inner, #quaker-carousel-Related-Products .carousel-inner').each(function () {

      if ($(this).children('div').length === 1) $(this).siblings('#quakers-product-categories .carousel-indicators, #quakers-product-categories .carousel-control-prev, .carousel-control-next, #quaker-carousel-Related-Products .carousel-indicators, #quaker-carousel-Related-Products .carousel-control-prev, .carousel-control-next').hide();

    });


    $('#quaker-featured-products-carousel, #quaker-related-products-carousel, #quaker-power-of-oats-carousel').carousel({
      interval: false
    });

    if ($(window).width() >= 768) {

      $('#quaker-videos-carousel .carousel-inner').each(function () {

        if ($(this).children('div').length === 2) $(this).siblings('#quaker-videos-carousel .carousel-indicators, #quaker-videos-carousel .carousel-control-prev, .carousel-control-next').hide();

      });

      $('#quaker-featured-products-carousel .carousel-item, #quaker-related-products-carousel .carousel-item').each(function () {
        let next = $(this).next();
        if (!next.length) {
          next = $(this).siblings(':first');
        }
        next.children(':first-child').clone().appendTo($(this));

        for (let i = 0; i < 2; i++) {
          next = next.next();
          if (!next.length) {
            next = $(this).siblings(':first');
          }
          next.children(':first-child').clone().appendTo($(this));
        }
      });

      $('#quaker-power-of-oats-carousel .carousel-item').each(function () {
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

      $('#quaker-videos-carousel .carousel-item').each(function () {
        let next = $(this).next();
        if (!next.length) {
          next = $(this).siblings(':first');
        }
        next.children(':first-child').clone().appendTo($(this));
      });
      var $toActivateVideo = $(".activate-video");

      function activateVideos() {
        $toActivateVideo.off().on('click', function () {
          let key = $(this).data('key');
          let iframe = '<div class="quaker-video-iframe-container"><iframe src="https://www.youtube.com/embed/' + key + '?autoplay=1&rel=0" frameborder="0" allow="autoplay" allowfullscreen></iframe><div>';
          $(this).closest('.quaker-video-box').prepend(iframe);
          $(this).closest('.quaker-video-box').find('.quaker-video-preimg').remove();
          return false;
        });
      }

      activateVideos();

      $('#quaker-featured-products-carousel, #quaker-related-products-carousel, #quaker-power-of-oats-carousel, #quaker-videos-carousel').on('slide.bs.carousel', function (event) {
        if (event.direction == "right") {
          $(event.relatedTarget).children(":last-child").hide();
        } else {
          let prev = $(event.relatedTarget).prev();
          if (!prev.length) {
            prev = $(event.relatedTarget).siblings(':last');
          }
          prev.children(":last-child").hide();
        }
      })

      $('#quaker-featured-products-carousel, #quaker-related-products-carousel, #quaker-power-of-oats-carousel, #quaker-videos-carousel').on('slid.bs.carousel', function (event) {
        if (event.direction == "right") {
          $(event.relatedTarget).children(":last-child").show();
        } else {
          let prev = $(event.relatedTarget).prev();
          if (!prev.length) {
            prev = $(event.relatedTarget).siblings(':last');
          }
          prev.children(":last-child").show();
        }
      })

    } else if ($(window).width() <= 767) {
      var $toActivateVideo = $(".activate-video");

      function activateVideos() {
        $toActivateVideo.off().on('click', function () {
          let key = $(this).data('key');
          let iframe = '<div class="quaker-video-iframe-container"><iframe src="https://www.youtube.com/embed/' + key + '?autoplay=1&rel=0" frameborder="0" allow="autoplay" allowfullscreen></iframe><div>';
          $(this).closest('.quaker-video-box').prepend(iframe);
          $(this).closest('.quaker-video-box').find('.quaker-video-preimg').remove();
          return false;
        });
      }

      activateVideos();


      $('#quaker-carousel-Related-Products .carousel-inner').each(function () {

        if ($(this).children('div').length > 1) {
          $('.quaker-related-product-block').addClass('quaker-related-product-block-carousel')
        }

      });
    }


    $('#carouselQuakerHistory').on('slide.bs.carousel', function (event) {
      $(".quaker-history-top-date-navigation").children().children().removeClass("active");
      $(".quaker-history-top-date-navigation").children().children().children().removeClass("active");
      $(".history-slider-indicator-" + $(event.relatedTarget).attr("data-indicator")).addClass("active");
      $(".history-slider-indicator-" + $(event.relatedTarget).attr("data-indicator")).children().addClass("active");
    })


    // Delete it later on
    // $('.alert-danger').hide();


  });


  //dropdown quaker product detail
  $(document).ready(function () {
    $('.nutrition-copy-js').hide('');
    $('.ingredient-copy-js').hide('');


    $(".js-quaker-nutrition-swap").click(function () {
      $(this).toggleClass('active');
      if ($('.js-quaker-nutrition-swap').hasClass('active')) {
        $('.nutrition-copy-js').show('fast');
      } else {
        $('.nutrition-copy-js').hide('fast');
      }
    });

    $(".js-quaker-ingredient-swap").click(function () {
      $(this).toggleClass('active');
      if ($('.js-quaker-ingredient-swap').hasClass('active')) {
        $('.ingredient-copy-js').show('fast');
      } else {
        $('.ingredient-copy-js').hide('fast');
      }
    });
  })

  const backgroundClick = document.querySelectorAll(".banner_slide");

  for (let i = 0; i < backgroundClick.length; i++) {
      let el = backgroundClick[i];

      el.addEventListener("click", function () {
          let button = this.querySelector(".btn_transparent");
          button.click();
      })
  }

  // BuyNow Modal

  $("#quaker-buy").click(function(){
    $(".quaker-navbar__vertical-align").toggleClass("index");
  });

  $("#closeModal").click(function(){
    $(".quaker-navbar__vertical-align").toggleClass("index");
  });

}(jQuery, window));/*end of file*/
