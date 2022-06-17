/* eslint-disable */

/*
 * jQuery throttle / debounce - v1.1 - 3/7/2010
 * http://benalman.com/projects/jquery-throttle-debounce-plugin/
 *
 * Copyright (c) 2010 "Cowboy" Ben Alman
 * Dual licensed under the MIT and GPL licenses.
 * http://benalman.com/about/license/
 */
function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); if (enumerableOnly) symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; }); keys.push.apply(keys, symbols); } return keys; }
function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i] != null ? arguments[i] : {}; if (i % 2) { ownKeys(Object(source), true).forEach(function (key) { _defineProperty(target, key, source[key]); }); } else if (Object.getOwnPropertyDescriptors) { Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)); } else { ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } } return target; }
function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }
(function(b,c){var $=b.jQuery||b.Cowboy||(b.Cowboy={}),a;$.throttle=a=function(e,f,j,i){var h,d=0;if(typeof f!=="boolean"){i=j;j=f;f=c}function g(){var o=this,m=+new Date()-d,n=arguments;function l(){d=+new Date();j.apply(o,n)}function k(){h=c}if(i&&!h){l()}h&&clearTimeout(h);if(i===c&&m>e){l()}else{if(f!==true){h=setTimeout(i?k:l,i===c?e-m:e)}}}if($.guid){g.guid=j.guid=j.guid||$.guid++}return g};$.debounce=function(d,e,f){return f===c?a(d,e,false):a(d,f,e!==false)}})(this);

function closeAllSelect(elmnt) {
  /* A function that will close all select boxes in the document,
  except the current select box: */
  var x,
      y,
      i,
      xl,
      yl,
      arrNo = [];
  x = document.getElementsByClassName("select-items");
  y = document.getElementsByClassName("select-selected");
  xl = x.length;
  yl = y.length;

  for (i = 0; i < yl; i++) {
    if (elmnt == y[i]) {
      arrNo.push(i);
    } else {
      y[i].classList.remove("select-arrow-active");
    }
  }

  for (i = 0; i < xl; i++) {
    if (arrNo.indexOf(i)) {
      x[i].classList.add("select-hide");
    }
  }
}

window.addEventListener('filterSelected', function(e) {
	handleFilterSelectedEvent(e);
});

function handleFilterSelectedEvent(e) {
  console.log(e.detail);
  switch (e.detail) {
    case 'ingredient':
      window.active_filter = "ingredient";
      filterRecipesByIngredients();
      break;
    case 'occasion':
      window.active_filter = "occasion";
      filterRecipesByOccasion();
      break;
    case 'time':
      window.active_filter = "time";
      filterRecipesByTime();
      break;
    case 'video':
      window.active_filter = "video";
      filterRecipesByVideo();
      break;
    case 'chef':
      window.active_filter = "chef";
      filterRecipesByChef();
      break;
    default:
      break;
  }
}

  function searchRecipes(){
      var offset = "0";
      var lang = window.location.pathname.startsWith("/fr-ca/") ? 'fr-ca':'en-ca';

      $(".js-search-recipe").click(function(){
        var search_term = $(".js-recipe-search-input").val();
        window.active_filter = "search";
         deleteRecipes();
         window.recipe_offset = 0;
            $.ajax({
                url:"/brands/" + brand + "/load-recipes/" + window.recipe_offset,
                type: "POST",
                data: {"filter": "search", "search_term": search_term, 'lang': lang},
                success:function(json) {
                    const data = JSON.parse(json);
                    if (data.status == 'success'){

                       console.log(data);
                       loadRecipes(data.recipes, data.count);
                      window.recipe_offset = window.recipe_offset + parseInt(data.count);
                      window.search_term = search_term;
                    }
                }
              });


      });
  }

  function filterRecipesByVideo(){
    $(".filter-videos").click(function(){
        window.active_filter = "video";
        var offset = "0";
        window.recipe_offset = 0;
        deleteRecipes();
        var lang = window.location.pathname.startsWith("/fr-ca/") ? 'fr-ca':'en-ca';
        $(".more-recipes").removeClass("hidden");


        $.ajax({
            url:"/brands/tostitos/load-recipes/" + offset,
            type: "POST",
            data: {"filter": 'video', 'lang': lang},
            success:function(json) {
                const data = JSON.parse(json);
                if (data.status == 'success'){

                   console.log(data);
                   loadRecipes(data.recipes, data.count);
                   $("ul.tostitos").data('count', parseInt(data.count) );
                   window.recipe_offset = window.recipe_offset + parseInt(data.count);
                }
            }
          });
    });
  }

  function filterRecipesByChef(){
    $(".filter-chef").click(function(){
        window.active_filter = "chef";
        var offset = "0";
        window.recipe_offset = 0;
        deleteRecipes();
        var lang = window.location.pathname.startsWith("/fr-ca/") ? 'fr-ca':'en-ca';
        $(".more-recipes").removeClass("hidden");


        $.ajax({
            url:"/brands/tostitos/load-recipes/" + offset,
            type: "POST",
            data: {"filter": 'chef', 'lang': lang},
            success:function(json) {
                const data = JSON.parse(json);
                if (data.status == 'success'){

                   console.log(data);
                   loadRecipes(data.recipes, data.count);
                   $("ul.tostitos").data('count', parseInt(data.count) );
                   window.recipe_offset = window.recipe_offset + parseInt(data.count);
                }
            }
          });
    });
  }

  function filterRecipesByTime(){
    var lang = window.location.pathname.startsWith("/fr-ca/") ? 'fr-ca':'en-ca';
    // $(".filter-time").click(function(){
    //     $(".custom-select-container").not('.hidden').addClass("hidden");
    //     $("#time").removeClass("hidden");
    // });

      $(".more-recipes").removeClass("hidden");
      var prep_time = $("#time select").val();
      window.recipe_offset = 0;

        // $('#time select.filter_dropdown').on('change', function (e) {
            // var optionSelected = $("option:selected", this);
            // var prep_time = this.value;

            var offset = "0";
            deleteRecipes();


            $.ajax({
                url:"/brands/tostitos/load-recipes/" + offset,
                type: "POST",
                data: {"filter": 'time', 'prep_time': prep_time, 'lang': lang},
                success:function(json) {
                    const data = JSON.parse(json);
                    if (data.status == 'success'){

                       console.log(data);
                       loadRecipes(data.recipes, data.count);
                       $("ul.tostitos").data('count', parseInt(data.count) );
                       window.selected_preptime = prep_time;
                      window.recipe_offset = window.recipe_offset + parseInt(data.count);
                    }
                }
              });
        // });

  }

  function filterRecipesByOccasion(){
    var lang = window.location.pathname.startsWith("/fr-ca/") ? 'fr-ca':'en-ca';
   var tid = $("#occasion select").val();
      $(".more-recipes").removeClass("hidden");
   window.recipe_offset = 0;
        // $('#occasion select.filter_dropdown').on('change', function (e) {
            // var optionSelected = $("option:selected", this);
            // var tid = this.value;

            var offset = "0";
            deleteRecipes();


            $.ajax({
                url:"/brands/tostitos/load-recipes/" + offset,
                type: "POST",
                data: {"filter": 'occasion', 'tid': tid, 'lang': lang},
                success:function(json) {
                    const data = JSON.parse(json);
                    if (data.status == 'success'){

                       console.log(data);
                       loadRecipes(data.recipes, data.count);
                       $("ul.tostitos").data('count', parseInt(data.count) );
                       window.active_tid = tid;
                      window.recipe_offset = window.recipe_offset + parseInt(data.count);
                    }
                }
              });
        // });

  }

  function filterRecipesByIngredients(){
    var lang = window.location.pathname.startsWith("/fr-ca/") ? 'fr-ca':'en-ca';
      var tid = $("#ingredient select").val();
      var offset = "0";
      window.recipe_offset = 0;
      $(".more-recipes").removeClass("hidden");
      deleteRecipes();

      $.ajax({
          url:"/brands/tostitos/load-recipes/" + offset,
          type: "POST",
          data: {"filter": 'ingredient', 'tid': tid, 'lang': lang},
          success:function(json) {
              const data = JSON.parse(json);
              if (data.status == 'success'){

                 console.log(data);
                 loadRecipes(data.recipes, data.count);
                 $("ul.tostitos").data('count', parseInt(data.count) );
                 window.recipe_offset = window.recipe_offset + parseInt(data.count);
                 window.active_tid = tid;
              }
          }
        });

  }

  // function sendRating(nid, nb_star){
  //    $.ajax({
  //         url:"/brands/tostitos/rating-recipes",
  //         type: "POST",
  //         data: {"rating": nb_star, 'nid': nid},
  //         success:function(json) {
  //             const data = JSON.parse(json);
  //             if (data.status == 'success'){

  //                console.log(data);
  //             }
  //         }
  //       });
  // }

  function sendRating(){
     $(".tostitos-stars-corner label").click(function(){
      // Get the nid from parent .tostitos-nocarousel-box data-nid
        var nid = $(this).closest('.tostitos-nocarousel-box').data('nid');

        updateVoteInDB(nid, val, 1);
     })
  }

  function updateVoteInDB(nid, value,old_value){
      // var lang = window.language
      var lang = window.location.pathname.startsWith("/fr-ca/") ? 'fr-ca':'en-ca';

      var node_data = { 'node' : nid, 'value' : value , 'old_value': old_value , 'langcode': lang};

      $.ajax({
            url:"/" + lang + "/pepsi/updaterecipevote/ajaxaction",
            type: "POST",
            data:  node_data,
            success:function(data) {
                var averag = data.average;
                var total = data.total;
                // updateStars(averag, total)

            }
          });
  }


function deleteRecipes(){
    $(".tostitos-nocarousel-container").html('');
    $(".tostitos-unevencarousel-container").addClass("hidden");
}


function loadRecipes(json_recipes, count){
   if (count < window.nb_recipes_per_query){
      $(".more-recipes").addClass("hidden")
   }
   if (json_recipes == ""){
      return;
   }

    $.each(json_recipes, function(i, obj) {
        var recipe_html = createRecipeItem(obj);
        $(".tostitos-nocarousel-container").append(recipe_html);

    });

  }





function createRecipeItem(recipe){
    var _html = `
    <div class="tostitos-nocarousel-box" data-nid="${recipe.nid}">
            <div class="img">
            <a href="${recipe.link}">
                <img src="${recipe.image_url}" />
            </a>
            </div>

            <h3>
                ${recipe.title}
            </h3>

            <a href="${recipe.link}" class="tostitos-btn tostitos-btn-yellow">
                ${recipe.cta}
            </a>

            ${recipe.html_rating}
        </div>
    `;
    return _html;
  }

function setCustomSelect() {
  var x, i, j, l, ll, selElmnt, a, b, c;

  /* Look for any elements with the class "custom-select-container": */
  x = document.getElementsByClassName("custom-select-container");
  l = x.length;

  for (i = 0; i < l; i++) {
    selElmnt = x[i].getElementsByTagName("select")[0];
    ll = selElmnt.length;

    /* For each element, create a new DIV that will act as the selected item: */
    a = document.createElement("DIV");
    a.setAttribute("class", "select-selected");
    a.innerHTML = selElmnt.options[selElmnt.selectedIndex].innerHTML;
    x[i].appendChild(a);

    /* For each element, create a new DIV that will contain the option list: */
    b = document.createElement("DIV");
    b.setAttribute("class", "select-items select-hide");

    for (j = 1; j < ll; j++) {

      /* For each option in the original select element,
      create a new DIV that will act as an option item: */
      c = document.createElement("DIV");
      c.innerHTML = selElmnt.options[j].innerHTML;
      c.addEventListener("click", function (e) {

        /* When an item is clicked, update the original select box,
        and the selected item: */
        var y, i, k, s, h, sl, yl;
        s = this.parentNode.parentNode.getElementsByTagName("select")[0];
        sl = s.length;
        h = this.parentNode.previousSibling;

        for (i = 0; i < sl; i++) {
          if (s.options[i].innerHTML == this.innerHTML) {
            s.selectedIndex = i;
            h.innerHTML = this.innerHTML;
            y = this.parentNode.getElementsByClassName("same-as-selected");
            yl = y.length;

            for (k = 0; k < yl; k++) {
              y[k].removeAttribute("class");
            }

            this.setAttribute("class", "same-as-selected");

            /* Custom event dispatched when option is selected,
            sends the data-type attribute as a value to select the proper filter: */
            var fType = this.closest('.custom-select-container').getElementsByTagName('select')[0].dataset.type;
				    window.dispatchEvent(new CustomEvent('filterSelected', { detail: fType }));
            break;
          }
        }

        h.click();
      });
      b.appendChild(c);
    }

    x[i].appendChild(b);
    a.addEventListener("click", function (e) {

      /* When the select box is clicked, close any other select boxes,
      and open/close the current select box: */
      e.stopPropagation();
      closeAllSelect(this);
      this.nextSibling.classList.toggle("select-hide");
      this.classList.toggle("select-arrow-active");
    });
  }
}

(function ($, window) {
  var baseCarouselOptions = {
    autoplay: false,
  };

  var brandCarouselOptions = _objectSpread(_objectSpread({}, baseCarouselOptions), {}, {
    dots: false,
    slidesToShow: 3,
    responsive: [{
      breakpoint: 769,
      settings: {
        slidesToShow: 1
      }
    }]
  });

  var brandSingleCarouselOptions = _objectSpread(_objectSpread({}, baseCarouselOptions), {}, {
    dots: false,
    slidesToShow: 1
  });

  var brandSingleCarouselOptionsDots = _objectSpread(_objectSpread({}, baseCarouselOptions), {}, {
    dots: true,
    slidesToShow: 1
  });

    //carousel for tostitos hint page
  const brandsOptions = {
        slidesToShow: 4,
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

  window.recipe_offset = 12;
  window.nb_recipes_per_query = 12;
  window.active_filter = 0;
  window.active_filter_param = {};

  function getDistance(el1, el2) {
    var el1Rect = el1.getBoundingClientRect();
    var el2Rect = el2.getBoundingClientRect();
    var el1Top = el1Rect.top;
    var el2Bottom = el2Rect.top + el2Rect.height;
    var distance = el2Bottom - el1Top;
    return distance;
  }

  function supportsFlexBox() {
    var test = document.createElement('test');
    test.style.display = 'flex';
    return test.style.display === 'flex';
  }

  $.fn.slideCountIsGreaterThanOne = function(slideSelector) {
    return $(this).filter(function (id, el) {
      var slides = $(el).find(slideSelector).length;
      return slides > 1;
    });
  }

  $(document).ready(function () {
      if ($('.custom-select-container').length) {
        setCustomSelect();
        document.addEventListener("click", closeAllSelect);
      }

      searchRecipes();

      filterRecipesByVideo();
      filterRecipesByChef();
      sendRating();

      if ($(".more-recipes").length) {
        clickMoreRecipes();
      }

      if ($('.tostitos-carousel-container').length) {
        $('.tostitos-carousel-container').slideCountIsGreaterThanOne('.tostitos-carousel-box').slick(brandCarouselOptions);
      }

      if ($('.tostitos-carousel-container-single').length) {
        $('.tostitos-carousel-container-single').slideCountIsGreaterThanOne('.tostitos-product-slide').slick(brandSingleCarouselOptionsDots);
      }

      if ($('.tostitos-unevencarousel-container').length) {
        $('.tostitos-unevencarousel-container').slideCountIsGreaterThanOne('.tostitos-unevencarousel-box').slick(brandSingleCarouselOptions);
      }

      //carousel for tostitos hint page
      $('.carousel_fullwidth.carousel_desktop.js-main-carousel-tostitos-hint').slick(brandsOptions);

      if ($('.tostitos-social-wall').length) {
        socialOptions = {
          selector: '.tostitos-social-box'
        };
        var socialWall = new Freewall(socialWall);
        socialWall.reset(socialOptions);
        var first = $('.tostitos-social-box:first-of-type');
        var last = $('.tostitos-social-box:last-of-type');
        Promise.all(Array.from(document.images).filter(function (img) {
          return !img.complete;
        }).map(function (img) {
          return new Promise(function (resolve) {
            img.onload = img.onerror = resolve;
          });
        })).then(function () {
          $('.tostitos-social-wall').innerHeight(getDistance(first[0], last[0]));
        });
      }

      if (!supportsFlexBox()) {
        flexibility(document.documentElement);
      }

      if ($('.tostitos-unevencarousel-container').length) {
        var heights = [], max = 0;
        $('.tostitos-unevencarousel-box-right').each(function(index, value) {
          heights.push($(value).innerHeight());
        });
        max = Math.max.apply(Math, heights);

        $('.tostitos-unevencarousel-box-left, .tostitos-unevencarousel-box-right').innerHeight(max);
      }

      if ($('.tr-footer-fix-trigger').length) {
        $(window).innerWidth() < 1921 ? $('.arrow-section-footer-white').css('top', '-85px') : $('.arrow-section-footer-white').css('top', '-100px');
      }

      $(window).resize($.debounce(50, function () {
        if (!supportsFlexBox()) {
          flexibility(document.documentElement);
        }

        if ($('.tostitos-unevencarousel-container').length) {
          var heights = [], max = 0;
          $('.tostitos-unevencarousel-box-right').each(function(index, value) {
            heights.push($(value).innerHeight());
          });
          max = Math.max.apply(Math, heights);

          $('.tostitos-unevencarousel-box-left, .tostitos-unevencarousel-box-right').innerHeight(max);
        }

        if ($('.tostitos-social-wall').length) {
          $('.tostitos-social-wall').innerHeight(getDistance(first[0], last[0]));
        }

        if ($('.tr-footer-fix-trigger').length) {
          $(window).innerWidth() < 1921 ? $('.arrow-section-footer-white').css('top', '-85px') : $('.arrow-section-footer-white').css('top', '-100px');
        }
      }));
  });

  function clickMoreRecipes(){
    $(".more-recipes").click(function(){
       var lang = window.location.pathname.startsWith("/fr-ca/") ? 'fr-ca':'en-ca';
        // var offset = $("ul.tostitos").data('count');
        var offset = window.recipe_offset;
        var tid = window.active_tid;
        var prep_time = window.selected_preptime;

    		$.ajax({
            url:"/brands/tostitos/load-recipes/" + offset,
            type: "POST",
            data: {"filter": window.active_filter, "lang": lang, "tid" : tid, "prep_time" : prep_time},
            success:function(json) {
                const data = JSON.parse(json);
                if (data.status == 'success'){
            	     console.log(data);
            	     loadRecipes(data.recipes, data.count);
                   $("ul.tostitos").data('count', parseInt(offset) + parseInt(data.count) );
                   window.recipe_offset = window.recipe_offset + parseInt(data.count);
                   console.log(" DATA count " + data.count);
                   console.log(" Recipe count " + window.recipe_offset);
                }
            }
          });
    });
  }
}(jQuery, window));/*end of file*/

/**
 * ------------------------------------------------------------------------
 * Youtube Placeholder Script
 * ------------------------------------------------------------------------
 */

var $toActivateVideo = $(".activate-video");
var $toActivateVideoEn1 = $(".activate-hint-video-en-1");
var $toActivateVideoFr1 = $(".activate-hint-video-fr-1");
var $toActivateLandingVideo1 = $(".activate-landing-video-1");
var $toActivateLandingVideo2 = $(".activate-landing-video-2");

var $toActivateVideoEn2 = $(".activate-hint-video-en-2");
var $toActivateVideoFr2 = $(".activate-hint-video-fr-2");

var $toActivateVideoEn3 = $(".activate-hint-video-en-3");
var $toActivateVideoFr3 = $(".activate-hint-video-fr-3");

var $toActivateVideoEn4 = $(".activate-hint-video-en-4");
var $toActivateVideoFr4 = $(".activate-hint-video-fr-4");

$(function() {
  activateVideos();
  activateLandingVideo1();
  activateLandingVideo2();
});

function activateVideos() {
  $toActivateVideo.off().on('click', function () {
    var key = $(this).data('key');
    var iframe = '<div class="tostitos-video-iframe-container"><iframe src="https://www.youtube.com/embed/' + key + '?autoplay=1&rel=0" frameborder="0" allow="autoplay" allowfullscreen></iframe><div>';
    $(this).closest('.tostitos-video-box').prepend(iframe);
    $(this).closest('.tostitos-video-box').find('.tostitos-video-preimg').remove();
    return false;
  });

    //Video 1
    $toActivateVideoEn1.off().on('click', function () {
        // var key = $(this).data('key');
        var iframe = '<div class="tostitos-video-iframe-container"><iframe src="https://www.youtube.com/embed/rzzdhIBT1pY?autoplay=1&rel=0" frameborder="0" allow="autoplay" allowfullscreen></iframe><div>';

        $(this).closest('.tostitos-video-box').prepend(iframe);
        $(this).closest('.tostitos-video-box').find('.tostitos-video-preimg').remove();
        return false;
    });

    $toActivateVideoFr1.off().on('click', function () {
        // var key = $(this).data('key');
        var iframe = '<div class="tostitos-video-iframe-container"><iframe src="https://www.youtube.com/embed/JpywoMOOmWM?autoplay=1&rel=0" frameborder="0" allow="autoplay" allowfullscreen></iframe><div>';

        $(this).closest('.tostitos-video-box').prepend(iframe);
        $(this).closest('.tostitos-video-box').find('.tostitos-video-preimg').remove();
        return false;
    });

    //Video 2
    $toActivateVideoEn2.off().on('click', function () {
        // var key = $(this).data('key');
        var iframe = '<div class="tostitos-video-iframe-container"><iframe src="https://www.youtube.com/embed/CKTrTgx0VS8?autoplay=1&rel=0" frameborder="0" allow="autoplay" allowfullscreen></iframe><div>';
        $(this).closest('.tostitos-video-box').prepend(iframe);
        $(this).closest('.tostitos-video-box').find('.tostitos-video-preimg').remove();
        return false;
    });

    $toActivateVideoFr2.off().on('click', function () {
        // var key = $(this).data('key');
        var iframe = '<div class="tostitos-video-iframe-container"><iframe src="https://www.youtube.com/embed/cz7uWtX91nQ?autoplay=1&rel=0" frameborder="0" allow="autoplay" allowfullscreen></iframe><div>';
        $(this).closest('.tostitos-video-box').prepend(iframe);
        $(this).closest('.tostitos-video-box').find('.tostitos-video-preimg').remove();
        return false;
    });

    //Video 3
    $toActivateVideoEn3.off().on('click', function () {
        // var key = $(this).data('key');
        var iframe = '<div class="tostitos-video-iframe-container"><iframe src="https://www.youtube.com/embed/ycax81IKKM0?autoplay=1&rel=0" frameborder="0" allow="autoplay" allowfullscreen></iframe><div>';
        $(this).closest('.tostitos-video-box').prepend(iframe);
        $(this).closest('.tostitos-video-box').find('.tostitos-video-preimg').remove();
        return false;
    });

    $toActivateVideoFr3.off().on('click', function () {
        // var key = $(this).data('key');
        var iframe = '<div class="tostitos-video-iframe-container"><iframe src="https://www.youtube.com/embed/PmYAj-AzHvY?autoplay=1&rel=0" frameborder="0" allow="autoplay" allowfullscreen></iframe><div>';
        $(this).closest('.tostitos-video-box').prepend(iframe);
        $(this).closest('.tostitos-video-box').find('.tostitos-video-preimg').remove();
        return false;
    });

    //Video 4
    $toActivateVideoEn4.off().on('click', function () {
        // var key = $(this).data('key');
        var iframe = '<div class="tostitos-video-iframe-container"><iframe src="https://www.youtube.com/embed/Ir_oRAGF_Mo?autoplay=1&rel=0" frameborder="0" allow="autoplay" allowfullscreen></iframe><div>';
        $(this).closest('.tostitos-video-box').prepend(iframe);
        $(this).closest('.tostitos-video-box').find('.tostitos-video-preimg').remove();
        return false;
    });

    $toActivateVideoFr4.off().on('click', function () {
        // var key = $(this).data('key');
        var iframe = '<div class="tostitos-video-iframe-container"><iframe src="https://www.youtube.com/embed/71x6Bp991S4?autoplay=1&rel=0" frameborder="0" allow="autoplay" allowfullscreen></iframe><div>';
        $(this).closest('.tostitos-video-box').prepend(iframe);
        $(this).closest('.tostitos-video-box').find('.tostitos-video-preimg').remove();
        return false;
    });
}

/**
 * ------------------------------------------------------------------------
 * Landing Page Youtube Placeholder
 * ------------------------------------------------------------------------
 */

var firstVideoValue=jQuery('.tostitos-video-preimg-1').attr("data-landing-video");
var firstSecondValue=jQuery('.tostitos-video-preimg-2').attr("data-landing-video");

// console.log(firstSecondValue);


function activateLandingVideo1() {
  $toActivateLandingVideo1.off().on('click', function () {
    var iframe = '<div class="tostitos-video-iframe-container"><iframe src="https://www.youtube.com/embed/' + firstVideoValue + '?autoplay=1&rel=0" frameborder="0" allow="autoplay" allowfullscreen></iframe><div>';
    // var iframe = '<div class="tostitos-video-iframe-container"><iframe src="https://www.youtube.com/embed/TAZquAzfU0Y?autoplay=1&rel=0" frameborder="0" allow="autoplay" allowfullscreen></iframe><div>';
    $(this).closest('.tostitos-video-box').prepend(iframe);
    $(this).closest('.tostitos-video-box').find('.tostitos-video-preimg-1').remove();
    return false;
  });
}

function activateLandingVideo2() {
  $toActivateLandingVideo2.off().on('click', function () {
    var iframe = '<div class="tostitos-video-iframe-container"><iframe src="https://www.youtube.com/embed/' + firstSecondValue + '?autoplay=1&rel=0" frameborder="0" allow="autoplay" allowfullscreen></iframe><div>';
    // var iframe = '<div class="tostitos-video-iframe-container"><iframe src="https://www.youtube.com/embed/B69e011EHdI?autoplay=1&rel=0" frameborder="0" allow="autoplay" allowfullscreen></iframe><div>';
    $(this).closest('.tostitos-video-box').prepend(iframe);
    $(this).closest('.tostitos-video-box').find('.tostitos-video-preimg-2').remove();
    return false;
  });
}

/**
 * ------------------------------------------------------------------------
 * Landing Page Youtube Placeholder
 * ------------------------------------------------------------------------
 */

var firstVideoValue=jQuery('.tostitos-video-preimg-1').attr("data-landing-video");
var firstSecondValue=jQuery('.tostitos-video-preimg-2').attr("data-landing-video");

// console.log(firstSecondValue);


function activateLandingVideo1() {
  $toActivateLandingVideo1.off().on('click', function () {
    var iframe = '<div class="tostitos-video-iframe-container"><iframe src="https://www.youtube.com/embed/' + firstVideoValue + '?autoplay=1&rel=0" frameborder="0" allow="autoplay" allowfullscreen></iframe><div>';
    // var iframe = '<div class="tostitos-video-iframe-container"><iframe src="https://www.youtube.com/embed/TAZquAzfU0Y?autoplay=1&rel=0" frameborder="0" allow="autoplay" allowfullscreen></iframe><div>';
    $(this).closest('.tostitos-video-box').prepend(iframe);
    $(this).closest('.tostitos-video-box').find('.tostitos-video-preimg-1').remove();
    return false;
  });
}

function activateLandingVideo2() {
  $toActivateLandingVideo2.off().on('click', function () {
    var iframe = '<div class="tostitos-video-iframe-container"><iframe src="https://www.youtube.com/embed/' + firstSecondValue + '?autoplay=1&rel=0" frameborder="0" allow="autoplay" allowfullscreen></iframe><div>';
    // var iframe = '<div class="tostitos-video-iframe-container"><iframe src="https://www.youtube.com/embed/B69e011EHdI?autoplay=1&rel=0" frameborder="0" allow="autoplay" allowfullscreen></iframe><div>';
    $(this).closest('.tostitos-video-box').prepend(iframe);
    $(this).closest('.tostitos-video-box').find('.tostitos-video-preimg-2').remove();
    return false;
  });
}


const backgroundClick = document.querySelectorAll(".banner_slide");

for (let i = 0; i < backgroundClick.length; i++) {
    let el = backgroundClick[i];

    el.addEventListener("click", function () {
        let button = this.querySelector(".btn_red");
        button.click();
    })
    el.addEventListener("click", function () {
        let button = this.querySelector(".btn_transparent");
        button.click();
    })
}

$("#tostitos-buy").click(function(){
  $("#brands_navigation").toggleClass("index");
});

$("#closeModal").click(function(){
  $("#brands_navigation").toggleClass("index");
});
