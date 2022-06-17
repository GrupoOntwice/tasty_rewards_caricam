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


    });

}(jQuery, window));/*end of file*/



//video click

var $toActivateVideo = $(".activate-video");

$(function() {
    activateVideos();
});

function activateVideos() {
    $toActivateVideo.off().on('click', function () {
        var key = $(this).data('key');
        var iframe = '<div class="smartfood-video-iframe-container"><iframe src="https://www.youtube.com/embed/' + key + '?autoplay=1&rel=0" frameborder="0" allow="autoplay" allowfullscreen></iframe><div>';
        $(this).closest('.smartfood-video-box').prepend(iframe);
        $(this).closest('.smartfood-video-box').find('.smartfood-video-preimg').remove();
        return false;
    });
}

// Multi item Carousel - Featured - Related recipes etc

$('#smartfood-related-products-carousel').carousel({
  interval: false
});

if ($(window).width() >= 768) {

  $('#smartfood-related-products-carousel .carousel-inner').each(function () {

    if ($(this).children('div').length <= 2) $(this).siblings('#smartfood-related-products-carousel .carousel-indicators, #smartfood-related-products-carousel .carousel-control-prev, #smartfood-related-products-carousel .carousel-control-next').hide();

  });

  $('#smartfood-related-products-carousel .carousel-item').each(function () {
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


  $('#smartfood-related-products-carousel').on('slide.bs.carousel', function (event) {
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

  $('#smartfood-related-products-carousel').on('slid.bs.carousel', function (event) {
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


$("#smartfood-buy").click(function(){
  $(".smartfood-navbar__vertical-align").toggleClass("index");
});

$("#closeModal").click(function(){
  $(".smartfood-navbar__vertical-align").toggleClass("index");
});
