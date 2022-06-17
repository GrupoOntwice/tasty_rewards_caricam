window.recipe_offset = 6;
window.nb_recipes_per_query = 6;


(function ($, window) {

  $(document).ready(function () {

    if ($(".more-recipes").length) {
      clickMoreRecipes();
    }
  });

  function clickMoreRecipes() {
    $(".more-recipes").click(function () {
      var lang = 'en-us';
      // var offset = $("ul.tostitos").data('count');
      var offset = window.recipe_offset;
      // var tid = window.active_tid;
      // var prep_time = window.selected_preptime;

      $.ajax({
        url: "/spookysnacklab/load-recipes/" + offset,
        type: "POST",
        data: {"lang": lang},
        success: function (json) {
          const data = JSON.parse(json);
          if (data.status == 'success') {
            console.log(data);
            loadRecipes(data.recipes, data.count);
            // $("ul.tostitos").data('count', parseInt(offset) + parseInt(data.count) );
            // window.recipe_offset = window.recipe_offset + parseInt(data.count);
            window.recipe_offset = parseInt(data.new_offset);
            console.log(" DATA count " + data.count);
            console.log(" Recipe count " + window.recipe_offset);
          }
        }
      });
    });
  }

  function loadRecipes(json_recipes, count) {
    if (count < window.nb_recipes_per_query) {
      $(".more-recipes").addClass("hidden")
    }
    if (json_recipes == "") {
      return;
    }

    $.each(json_recipes, function (i, obj) {
      var recipe_html = createRecipeItem(obj);
      $(".spookysnacklab-nocarousel-container").append(recipe_html);

    });

  }

  if ($("#spookysnacklab-recipe-video").is(":visible")) {
    $(".spookysnacklab-icons").css("padding-left", "0px !important");
    $(".spookysnacklab-recipe-icons").css("margin-left", "0");

  }


  function createRecipeItem(recipe) {
    var _html = `
	    <div class="col-md-4 spookysnacklab-recipes">
	    	<div class="spookysnacklab-nocarousel-box" data-nid="${recipe.nid}">
	    	<a aria-label="recipe-link" href="${recipe.link}" class="">
	            <div class="spookysnacklab-recipe-img" style="background: url('${recipe.image_url}') no-repeat">
	            </div></a>

               <div class="spookysnacklab-recipe-title">
		            <a href="${recipe.link}" class="spookysnacklab_recipetitle yellow">
		            <h3 class="padding-top padding-bottom yellow transat-font">
		                ${recipe.title}
		                </h3>
		            </a>
	            </h3>
	            </div>
	            <div class="spooky-snack-lab-recipe-btn margin-top margin-bottom-medium">
		            <a href="${recipe.link}" class="spookysnacklab-btn spooky-orange-btn opensans-bold btn-textsize m-auto">
		                ${recipe.cta}
		            </a>
	        	</div>
	        </div>
	    </div>
	    `;
    return _html;
  }


  $('body').on('click', '.social-share-trigger', function (event) {
    event.preventDefault();
    var url = $("#node_url").val();
    var title = $("#node_title").val();
    var image = $("#node_image").val();
    var pathname = window.location.origin; // Returns path only

    var urlfb = url;
    var urltw = url;
    var urlpt = url;

    switch ($(this).data('platform')) {
      case 'facebook':
        window.open('https://www.facebook.com/sharer/sharer.php?u=' + urlfb, 'Share Facebook', 'height=300, width=500');
        break;
      case 'twitter':
        // window.open('http://twitter.com/home?status='+title+' '+urltw, 'Share Twitter','height=300, width=500');
        window.open('http://twitter.com/intent/tweet?url=' + urltw + '&text=' + title, 'Share Twitter', 'height=300, width=500');
        break;
      case 'pinterest':
        if (image.indexOf("media.tastyrewards.ca") >= 0) {
          window.open('http://www.pinterest.com/pin/create/button/?url=' + urlpt + '&media=' + image + '&description=' + title, 'Share Pinterest', 'height=300, width=500');
        } else {
          window.open('http://www.pinterest.com/pin/create/button/?url=' + urlpt + '&media=' + pathname + image + '&description=' + title, 'Share Pinterest', 'height=300, width=500');
        }
        break;
    }
  });

}(jQuery, window));/*end of file*/
