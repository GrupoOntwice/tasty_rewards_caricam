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
    var iframe = '<div class="doritos-video-iframe-container"><iframe src="https://www.youtube.com/embed/' + key + '?autoplay=1&rel=0" frameborder="0" allow="autoplay" allowfullscreen></iframe><div>';
    $(this).closest('.doritos-video-box').prepend(iframe);
    $(this).closest('.doritos-video-box').find('.doritos-video-preimg').remove();
    return false;
  });
}

//homepage background click to target the button


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

$("#doritos-buy").click(function(){
  $(".doritos-navbar__vertical-align").toggleClass("index");
});

$("#closeModal").click(function(){
  $(".doritos-navbar__vertical-align").toggleClass("index");
});
