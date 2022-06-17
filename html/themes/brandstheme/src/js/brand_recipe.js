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
    default:
      break;
  }
}

  function filterBrandRecipes(brand){

      var offset = "0";
      var lang = window.location.pathname.startsWith("/fr-ca/") ? 'fr-ca':'en-ca';

      $("#recipesFilterDropdowns button, .js-clear-filter").click(function(){
         var category = $(this).data("filter");
         deleteRecipes();
         window.recipe_offset = 0;
         window.selected_filter = category;
         if (!$(this).hasClass('js-clear-filter')){
           $(".js-clear-filter").removeClass('hidden');
            $.ajax({
                url:"/brands/" + brand + "/load-recipes/" + window.recipe_offset,
                type: "POST",
                data: {"filter": "category", "recipe_type": category, 'lang': lang},
                success:function(json) {
                    const data = JSON.parse(json);
                    if (data.status == 'success'){

                       console.log(data);
                       loadRecipes(data.recipes, data.count);
                      window.recipe_offset = window.recipe_offset + parseInt(data.count);
                    }
                }
              });

         } else {
           $(".js-clear-filter").addClass('hidden');
           window.recipe_offset = 12;
           var main_recipes = $(".js-recipe-container-backup").html();
           $(".js-recipe-container").html(main_recipes);
           $(".more-recipes").removeClass("hidden");
           window.selected_filter = '';

         }

      });

  }

  function searchRecipes(){
      var offset = "0";
      var lang = window.location.pathname.startsWith("/fr-ca/") ? 'fr-ca':'en-ca';

      $(".js-search-recipe").click(function(){
        var search_term = $("#quaker-recipe-search").val();
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


  function filterRecipesByTime(){
      var lang = window.location.pathname.startsWith("/fr-ca/") ? 'fr-ca':'en-ca';

      $(".more-recipes").removeClass("hidden");
      var prep_time = $("#time select").val();
      window.recipe_offset = 0;
      var brand = window.brand;

      var offset = "0";
      deleteRecipes();

      $.ajax({
          url:"/brands/" + brand + "/load-recipes/" + offset,
          type: "POST",
          data: {"filter": 'time', 'prep_time': prep_time, 'lang': lang},
          success:function(json) {
              const data = JSON.parse(json);
              if (data.status == 'success'){

                 console.log(data);
                 loadRecipes(data.recipes, data.count);
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


function deleteRecipes(){
    // $(".tostitos-nocarousel-container").html('');
    $(".js-recipe-container").html('');
    // $(".tostitos-unevencarousel-container").addClass("hidden");
    $(".js-unevencarousel-container").addClass("hidden");
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
        // $(".tostitos-nocarousel-container").append(recipe_html);
        $(".js-recipe-container").append(recipe_html);

    });

  }




function format_recipe_rating(recipe){

  var i;
  var _html = '';
  var _checked = "";
  for (i = 1; i <= 5; i++) {
    _checked = "";
    if (i <= parseInt(recipe.rating)){
      _checked = 'class="checked"';
    }
    _html = _html + '<span ' + _checked+ '></span>';
  }
  return _html;
}

function createRecipeItem(recipe){
    var brand = window.brand;
    var _html = getBrandRecipeRow(recipe, brand);
    return _html;
  }


function getBrandRecipeRow(recipe, brand){
  const img_position =recipe.img_position;
  let class_name = "img-align-center";
  if ( img_position ){
    class_name = `img-align-${img_position}` ;
  }
  switch(brand){
        case "quaker":
            var rating = format_recipe_rating(recipe);

            var _html = `
              <div class="col-md-4 quaker-recipes-col text-center">

              <div class="row">
                <div class="col-md-12 quaker-recipes-img-block">
                  <a href="${recipe.link}">
                    <div class="quaker-recipes-img ${class_name}" style="background: url('${recipe.image_url}')" aria-label="Quaker Recipe">
                    </div>
                  </a>
                </div>
              </div>

              <div class="quaker-stars quaker-stars-corner">
                  ${rating}
              </div>

              <div class="quakers-recipes-description text-center">

                <a href="${recipe.link}">
                  <h4>
                    ${recipe.title}
                  </h4>
                </a>

              </div>

              <div class="quaker-recipes-btn">
                <a href="${recipe.link}" class="btn_quaker">
                  ${recipe.cta}
                </a>
              </div>

            </div>
              `;
        break;
    case "stacys":
      var rating = format_recipe_rating(recipe);

      var _html = `
              <div class="col-md-4 stacys-recipes-col text-center">

              <div class="row">
                <div class="col-md-12 stacys-recipes-img-block">
                  <a href="${recipe.link}">
                    <div class="stacys-recipes-img ${class_name}" style="background: url('${recipe.image_url}')" aria-label="Stacys Recipe">
                    </div>
                  </a>
                </div>
              </div>

              <div class="stacys-stars stacys-stars-corner">
                  ${rating}
              </div>

              <div class="stacys-recipes-description text-center">

                <a href="${recipe.link}">
                  <h4>
                    ${recipe.title}
                  </h4>
                </a>

              </div>

              <div class="stacys-recipes-btn">
                <a href="${recipe.link}" class="btn_stacys">
                  ${recipe.cta}
                </a>
              </div>

            </div>
              `;
      break;
        default:
             var _html = `
              <div class="${brand}-nocarousel-box" data-nid="${recipe.nid}">
                      <div class="img">
                      <a href="${recipe.link}">
                          <img src="${recipe.image_url}" />
                      </a>
                      </div>

                      <h3>
                      <a href="${recipe.link}">
                          ${recipe.title}
                      </a>
                      </h3>
                      <div class="${brand}-btn ${brand}-btn-white">
                        <a href="${recipe.link}" class="${brand}-btn ${brand}-btn-yellow">
                            ${recipe.cta}
                        </a>
                      </div>

                      ${recipe.html_rating}
                  </div>
              `;
        break;
    }




    if (brand == 'flaminhot'){
      var _html = `
      <div class="col-md-4 flaminhot-recipe-col" data-nid="${recipe.nid}">

                <a href="${recipe.link}" class="flaminhot-recipe-img-link">
                  <div class="flaminhot-recipe-thumbnail" style="background: url('${recipe.image_url}') center center no-repeat, transparent; content: attr(${recipe.title});"></div>
                </a>

                <div class="flaminhot-recipe-copy">

                  <h3>
                    ${recipe.title}
                  </h3>
                </div>

                <div class="flaminhot-recipe-btn">
                  <a class="btn_flaminhot" href="${recipe.link}">
                    ${recipe.cta}
                  </a>
                </div>

              </div>
      `;
    }
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

  window.recipe_offset = 12;
  window.nb_recipes_per_query = 12;
  // window.active_filter = 0;
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

      if ($(".js-recipe-container-backup".length)){
        var main_recipes = $(".js-recipe-container").html();
        $(".js-recipe-container-backup").html(main_recipes);
      }

      filterRecipesByVideo();
      if (window.brand == "quaker" || window.brand == 'stacys'){
        filterBrandRecipes(window.brand);
      }

      if (window.brand == 'quaker'){
        searchRecipes();
      }

      // sendRating();

      if ($(".more-recipes").length) {
        clickMoreRecipes();
      }


  });

  function clickMoreRecipes(){
    $(".more-recipes").click(function(){
       var lang = window.location.pathname.startsWith("/fr-ca/") ? 'fr-ca':'en-ca';
        // var offset = $("ul.tostitos").data('count');
        var offset = window.recipe_offset;
        var tid = window.active_tid;
        var prep_time = window.selected_preptime;
        var brand = window.brand;
        var recipe_type = window.selected_filter;
        var search_term = window.search_term ;



        $.ajax({
            url:"/brands/" + brand + "/load-recipes/" + offset,
            type: "POST",
            data: {"filter": window.active_filter, "lang": lang, "tid" : tid, 
                    "search_term": search_term,
                    "prep_time" : prep_time, "recipe_type" : recipe_type},
            success:function(json) {
                const data = JSON.parse(json);
                if (data.status == 'success'){
                   console.log(data);
                   loadRecipes(data.recipes, data.count);
                   // $("ul.tostitos").data('count', parseInt(offset) + parseInt(data.count) );
                   window.recipe_offset = window.recipe_offset + parseInt(data.count);
                   console.log(" DATA count " + data.count);
                   console.log(" Recipe count " + window.recipe_offset);
                }
            }
          });
    });
  }
}(jQuery, window));/*end of file*/

