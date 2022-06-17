(function ($, window) {


    const fullWidthOptions = {
        dots: true,
        autoplay: true,
        autoplaySpeed: 5000,
        fade: true,
        pauseOnHover: false
    };

    var brandCarouselOptions = {
        autoplaySpeed: 0,
        slidesToScroll: 1,
        cssEase: 'linear',
        speed: 300,

        slidesToShow: 3,
        responsive: [{
            breakpoint: 769,
            settings: {
                slidesToShow: 3
            }
        }]
    };

    $(document).ready(function () {
        if ($('.carousel_fullwidth.carousel_desktop.js-main-carousel-cheetos').length > 0){
            $('.carousel_fullwidth.carousel_desktop.js-main-carousel-cheetos').slick(fullWidthOptions);
        }

        if ($('.js-recipe-carousel.cheetos-carousel-container').length > 0 ){
            $('.js-recipe-carousel.cheetos-carousel-container').slick(brandCarouselOptions);
        }

    });



    //dropdown cheetos product detail
    $(document).ready(function(){
        $('.nutrition-copy-js').hide('');
        $('.product-ingredients p').hide('');

        $(".fa-swap-cheetos").click(function () {
            $(this).toggleClass('fa-minus');
            if($('.fa-swap.fa.fa-plus').hasClass('fa-minus')){
                $('.nutrition-copy-js').show('');
                $('.product-ingredients p').show('fast');
            }else{
                $('.nutrition-copy-js').hide('');
                $('.product-ingredients p').hide('fast');
            }
        });
    })

}(jQuery, window));/*end of file*/

//shrink logo for navbar
window.onscroll = function() {
    growShrinkLogo()
};

function growShrinkLogo() {
    var Logo = document.getElementById("cheetos-logo")
    if (document.body.scrollTop > 5 || document.documentElement.scrollTop > 5) {
        Logo.style.width = 'auto';
        Logo.style.height = 'auto';
    } else {
        Logo.style.width = '180px';
        Logo.style.height = '90px';
    }
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

$("#cheetos-buy").click(function(){
  $(".cheetos-navbar__vertical-align").toggleClass("index");
});

$("#closeModal").click(function(){
  $(".cheetos-navbar__vertical-align").toggleClass("index");
});


