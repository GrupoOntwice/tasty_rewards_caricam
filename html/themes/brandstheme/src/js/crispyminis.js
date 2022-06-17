(function ($, window) {

    const fullWidthOptions = {
        dots: true,
        autoplay: true,
        autoplaySpeed: 5000,
        fade: true,
        pauseOnHover: false
    };

    $(document).ready(function () {
        window.runSlickBrandCarousel('.carousel_fullwidth.carousel_desktop.js-main-carousel-crispyminis', fullWidthOptions);
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
        var iframe = '<div class="crispyminis-video-iframe-container"><iframe src="https://www.youtube.com/embed/' + key + '?autoplay=1&rel=0" frameborder="0" allow="autoplay" allowfullscreen></iframe><div>';
        $(this).closest('.crispyminis-video-box').prepend(iframe);
        $(this).closest('.crispyminis-video-box').find('.crispyminis-video-preimg').remove();
        return false;
    });
}

$("#crispyminis-buy").click(function(){
  $(".crispyminis-navbar__vertical-align").toggleClass("index");
});

$("#closeModal").click(function(){
  $(".crispyminis-navbar__vertical-align").toggleClass("index");
});
