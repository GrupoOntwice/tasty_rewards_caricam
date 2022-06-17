/* eslint-disable */

/*
 * jQuery throttle / debounce - v1.1 - 3/7/2010
 * http://benalman.com/projects/jquery-throttle-debounce-plugin/
 *
 * Copyright (c) 2010 "Cowboy" Ben Alman
 * Dual licensed under the MIT and GPL licenses.
 * http://benalman.com/about/license/
 */

(function ($, window) {

  $(document).ready(function () {

    // Nutrition Show and Hide Animation

    $(".fa-swap").click(function () {
      $(this).parent().parent().parent().parent().parent().children().find('.products-recipe-image img').toggleClass('scale');
      $(this).parent().parent().children('.nutrition-copy').toggle('.d-none');
      $(this).children('.arrow-up').toggleClass('arrow-down');

    });

    $(".fa-swap-mb").click(function () {
      $(this).parent().children('.nutrition-copy').toggle('.d-none');
      $(this).children('.arrow-up').toggleClass('arrow-down');

      // $(this).children('.arrow-down').toggleClass('hidden');
      // $(this).children('.arrow-up').toggle('.d-none');

    });



    // Ingredients Show and Hide Animation

    $('.ingredients-copy').css('display','none');
    $(".fa-ingredients-swap").click(function () {
      $(this).parent().parent().children('.ingredients-copy').toggle('.d-none');
      $(this).children('.arrow-up').toggleClass('arrow-down');
    });

    $(".fa-ingredients-swap-mb").click(function () {
      $(this).parent().children('.ingredients-copy').toggle('.d-none');
      $(this).children('.arrow-up').toggleClass('arrow-down');
    });

    // $('.carousel_fullwidth.carousel_desktop.js-main-carousel').slick({
    //   arrows:true
    // });

    // Smooth Scroll Effect

    // var firstNavClick = true;
    //
    // $("#otep-about-us-nav, #products-nav").on('click', function(event) {
    //
    //   event.preventDefault();
    //   let navAnimationHeightOffset = $("#brands_navigation").height();
    //   if(firstNavClick) {
    //     firstNavClick = false;
    //     navAnimationHeightOffset += 75;
    //   }
    //
    //   if ($(window).width() < 768) {
    //     navAnimationHeightOffset = 0;
    //   }
    //
    //   const id = this.hash.substring(1);
    //   const yOffset = navAnimationHeightOffset;
    //   const element = document.getElementById(id);
    //   const y = element.getBoundingClientRect().top + window.pageYOffset - yOffset;
    //
    //
    //   window.scrollTo({top: y, behavior: 'smooth'});
    //
    // });


  });

}(jQuery, window));/*end of file*/

