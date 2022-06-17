// (function (b, c) {
//     var $ = b.jQuery || b.Cowboy || (b.Cowboy = {}), a;
//     $.throttle = a = function (e, f, j, i) {
//         var h, d = 0;
//         if (typeof f !== "boolean") {
//             i = j;
//             j = f;
//             f = c
//         }
//
//         function g() {
//             var o = this, m = +new Date() - d, n = arguments;
//
//             function l() {
//                 d = +new Date();
//                 j.apply(o, n)
//             }
//
//             function k() {
//                 h = c
//             }
//
//             if (i && !h) {
//                 l()
//             }
//             h && clearTimeout(h);
//             if (i === c && m > e) {
//                 l()
//             } else {
//                 if (f !== true) {
//                     h = setTimeout(i ? k : l, i === c ? e - m : e)
//                 }
//             }
//         }
//
//         if ($.guid) {
//             g.guid = j.guid = j.guid || $.guid++
//         }
//         return g
//     };
//     $.debounce = function (d, e, f) {
//         return f === c ? a(d, e, false) : a(d, f, e !== false)
//     }
// })(this);

(function ($, window) {

    const carouselFeatureRealJoy = {
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

    const carouselStoryRealJoy = {
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

    const fullWidthOptions = {
        dots: true,
        autoplay: true,
        autoplaySpeed: 5000,
        fade: true,
        pauseOnHover: false
    };

    const brandsOptions = {
        slidesToShow: 5,
        pauseOnHover: false,
        responsive: [
            {
                breakpoint: 1024,
                settings: {
                    slidesToShow: 4,
                }
            },
            {
                breakpoint: 770,
                settings: {
                    slidesToShow: 3,
                }
            },
            {
                breakpoint: 415,
                settings: {
                    slidesToShow: 2,
                }
            }
        ]
    };

    $(document).ready(function () {
        if (typeof window.runSlickBrandCarousel !== 'function' ) {
            window.runSlickBrandCarousel = function runcarousel(){
              if (arguments.length != 2){
                return;
              }

              var selector = arguments[0];
              var slick_options = arguments[1];

              if ($(selector).length > 0 ){
                $(selector).slick(slick_options);
              }
            }
        }
    //Carousel for homepage, recipe and lifestyle page
        var screen_width = $(window).innerWidth()
            || document.documentElement.clientWidth
            || document.body.clientWidth;

        // $('#quaker-featured-products-carousel').slick(carouselStory);
        // $('.carousel_fullwidth.carousel_desktop.js-main-carousel-lays').slick(fullWidthOptions);
        window.runSlickBrandCarousel('.carousel_fullwidth.carousel_desktop.js-main-carousel-lays', fullWidthOptions);

        // load this later when the user scrolls to this part
        // $('.brands_carousel .views-element-container').slick(brandsOptions);
        window.runSlickBrandCarousel('.brands_carousel .views-element-container', brandsOptions);
        // $('#lays-story-carousel').slick(carouselStoryRealJoy);
        window.runSlickBrandCarousel('#lays-story-carousel', carouselStoryRealJoy);

        // $('#lays-feature-product-carousel').slick(carouselFeatureRealJoy);
        window.runSlickBrandCarousel('#lays-feature-product-carousel', carouselFeatureRealJoy);


    });

    $(".js-share-story").click(function(){
        var elmnt = document.getElementById("contestsignup-form");
        elmnt.scrollIntoView({block: "center"});
        // $("#uploadImg").click();
    });

    if (window.form_processed){
        setTimeout(function(){
            //document.getElementById("contestsignup-form").scrollIntoView();
            $('html, body').animate({
                scrollTop: $("#contestsignup-form").offset().top-200
            }, 200);
        }, 700);
    }


    /**
     * ------------------------------------------------------------------------
     * Youtube Placeholder Script
     * ------------------------------------------------------------------------
     */

    // $('#quaker-featured-products-carousel, #lays-featured-products-carousel').carousel({
    //     interval: false
    // });
    //
    //
    // if ($(window).width() >= 768) {
    //
    //
    //     $('#quaker-featured-products-carousel .carousel-item, #lays-featured-products-carousel .carousel-item').each(function () {
    //         let next = $(this).next();
    //         if (!next.length) {
    //             next = $(this).siblings(':first');
    //         }
    //         next.children(':first-child').clone().appendTo($(this));
    //
    //         for (let i = 0; i < 1; i++) {
    //             next = next.next();
    //             if (!next.length) {
    //                 next = $(this).siblings(':first');
    //             }
    //             next.children(':first-child').clone().appendTo($(this));
    //         }
    //     });
    //
    //     var $toActivateVideo = $(".activate-video");
    //
    //     function activateVideos() {
    //         $toActivateVideo.off().on('click', function () {
    //             let key = $(this).data('key');
    //             let iframe = '<div class="lays-video-iframe-container"><iframe src="https://www.youtube.com/embed/' + key + '?autoplay=1&rel=0" frameborder="0" allow="autoplay" allowfullscreen></iframe><div>';
    //             $(this).closest('.lays-video-box').prepend(iframe);
    //             $(this).closest('.lays-video-box').find('.lays-video-preimg').remove();
    //             return false;
    //         });
    //     }
    //
    //     activateVideos();
    //
    //     $('#quaker-featured-products-carousel, #lays-featured-products-carousel').on('slide.bs.carousel', function (event) {
    //         if (event.direction == "right") {
    //             $(event.relatedTarget).children(":last-child").hide();
    //         } else {
    //             let prev = $(event.relatedTarget).prev();
    //             if (!prev.length) {
    //                 prev = $(event.relatedTarget).siblings(':last');
    //             }
    //             prev.children(":last-child").hide();
    //         }
    //     })
    //
    //     $('#quaker-featured-products-carousel, #lays-featured-products-carousel').on('slid.bs.carousel', function (event) {
    //         if (event.direction == "right") {
    //             $(event.relatedTarget).children(":last-child").show();
    //         } else {
    //             let prev = $(event.relatedTarget).prev();
    //             if (!prev.length) {
    //                 prev = $(event.relatedTarget).siblings(':last');
    //             }
    //             prev.children(":last-child").show();
    //         }
    //     })
    //
    // } else if ($(window).width() <= 767) {
    //     var $toActivateVideo = $(".activate-video");
    //
    //     function activateVideos() {
    //         $toActivateVideo.off().on('click', function () {
    //             let key = $(this).data('key');
    //             let iframe = '<div class="quaker-video-iframe-container"><iframe src="https://www.youtube.com/embed/' + key + '?autoplay=1&rel=0" frameborder="0" allow="autoplay" allowfullscreen></iframe><div>';
    //             $(this).closest('.quaker-video-box').prepend(iframe);
    //             $(this).closest('.quaker-video-box').find('.quaker-video-preimg').remove();
    //             return false;
    //         });
    //     }
    //
    //     activateVideos();
    //
    // }

    var $toActivateVideo = $(".activate-video");

    function activateVideos() {
        $toActivateVideo.off().on('click', function () {
            let key = $(this).data('key');
            let iframe = '<div class="lays-video-iframe-container"><iframe src="https://www.youtube.com/embed/' + key + '?autoplay=1&rel=0" frameborder="0" allow="autoplay" allowfullscreen></iframe><div>';
            $(this).closest('.lays-video-box').prepend(iframe);
            $(this).closest('.lays-video-box').find('.lays-video-preimg').remove();
            return false;
        });
    }

    activateVideos();

}(jQuery, window));/*end of file*/

//video click

// var $toActivateVideo = $(".activate-video");
//
// $(function() {
//     activateVideos();
// });
//
// function activateVideos() {
//     $toActivateVideo.off().on('click', function () {
//         var key = $(this).data('key');
//         var iframe = '<div class="lays-video-iframe-container"><iframe src="https://www.youtube.com/embed/' + key + '?autoplay=1&rel=0" frameborder="0" allow="autoplay" allowfullscreen></iframe><div>';
//         $(this).closest('.lays-video-box').prepend(iframe);
//         $(this).closest('.lays-video-box').find('.lays-video-preimg').remove();
//         return false;
//     });
// }


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


$("#lays-buy").click(function(){
  $(".lays-navbar__vertical-align").toggleClass("index");
});

$("#closeModal").click(function(){
  $(".lays-navbar__vertical-align").toggleClass("index");
});
