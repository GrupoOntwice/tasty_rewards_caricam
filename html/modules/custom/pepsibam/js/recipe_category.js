
  // window.recipe_offset = 6;
  // window.nb_recipes_per_query = 6;



  


(function ($, window) {

	$(document).ready(function () {
		window.active_brands_filter = [];

	      if ($(".more-recipes").length) {
	        clickMoreRecipes();
	      }

	      // filterRecipesByBrands();
	      $(".recipe-filters ul.multiselect-container input").change(function(){
	      	 if (this.checked){
	      	 	add_brand($(this).val());
	      	 } else {
	      	 	remove_brand($(this).val());
	      	 }
	      });

	      submitSearch();

		  const params = new URLSearchParams(location.search);
		  var filter = params.get('filter')

		  console.log(filter);
	
		  if (filter.length > 0 && $("#edit-combine--2").length > 0 ){
			  $("#edit-combine--2").val(filter);
			  $(".js-form-submit").trigger("click");
		  }
  			

	  });
	
	function submitSearch(){
		$(".js-form-submit").click(function(event){
			event.preventDefault();
			var tids = window.active_brands_filter;
			var search_term = $(".js-search-input").val();
			var offset = 0;
			var category = window.category_id;

	        pepsibamDeleteRecipes();
			
			$.ajax({
	            url:"/recipe-filters/load-recipes/",
	            type: "POST",
	            data: {
	            	"lang": window.langcode, 
	            	"tids": JSON.stringify(tids),
	            	"search":search_term,
	            	"langprefix": window.langprefix, 
	            	"category":category,
	            },
	            success:function(json) {
	                const data = JSON.parse(json);
	                if (data.status == 'success'){
	            	     console.log(data);
	            	     pepsibamLoadRecipes(data.recipes, data.count);
	            	     add_style_new_recipes();
						 $(".more-recipes").removeClass("hidden");
	            	     
	            	     if (nb_recipes_per_query > parseInt(data.count)){
	            	     	// hide view more button
	            	     	$(".more-recipes").addClass("hidden");
	            	     }
	                }
	            }
	          });

		});
	}

	function remove_brand(val){
		var tid = parseInt(val);
		window.active_brands_filter = window.active_brands_filter.filter(function(elem){
		   return elem != tid; 
		});
	}

	function add_brand(tid){
		window.active_brands_filter.push(parseInt(tid));
	}

	function pepsibamDeleteRecipes(){
	    $(".recipe__menu-detail-wrapper").html('');
	}

	function clickMoreRecipes(){
    $(".more-recipes").click(function(){
       var lang = 'en-us';
        // var offset = $("ul.tostitos").data('count');
        var offset = window.recipe_offset;

        var tids = window.active_brands_filter;
		var category = window.category_id;
		var search_term = $(".js-search-input").val();
        // var tid = window.active_tid;
        // var prep_time = window.selected_preptime;

    		$.ajax({
	            url:"/recipe-filters/load-recipes/",
	            type: "POST",
	            data: {
	            	"lang": window.langcode, 
	            	"langprefix": window.langprefix, 
	            	"tids": JSON.stringify(tids),
	            	"category":category,
	            	"offset": offset,
	            	"search": search_term,
	            },
	            success:function(json) {
	                const data = JSON.parse(json);
	                if (data.status == 'success'){
	            	     console.log(data);
	            	     pepsibamLoadRecipes(data.recipes, data.count);
	            	     window.recipe_offset = window.recipe_offset + parseInt(data.count) ;
	            	     add_style_new_recipes();
	            	     
	            	     if (nb_recipes_per_query > parseInt(data.count)){
	            	     	// hide view more button
	            	     	$(".more-recipes").addClass("hidden");
	            	     }
	                }
	            }
	          });
    });
  }

  	function add_style_new_recipes(){
  		$('.truncate_text').each(function() {
			$(this).html($(this).find('> p:first-of-type'));
			$(this).find('> p:first-of-type').clamp();
		});

		$('.background-zoom__parent .img-recipe').each(function() {
			$(this).css('height', $(this).innerWidth() + 'px');
		});
  	}

  	function filterRecipesByBrands(){
	    var lang = window.location.pathname.startsWith("/fr-ca/") ? 'fr-ca':'en-ca';
	      var tid = $("#ingredient select").val();
	      var offset = "0";
	      window.recipe_offset = 0;
	      $(".more-recipes").removeClass("hidden");
	      pepsibamDeleteRecipes();

	      $.ajax({
	          url:"/brands/tostitos/load-recipes/" + offset,
	          type: "POST",
	          data: {"filter": 'ingredient', 'tid': tid, 'lang': lang},
	          success:function(json) {
	              const data = JSON.parse(json);
	              if (data.status == 'success'){

	                 console.log(data);
	                 pepsibamLoadRecipes(data.recipes, data.count);
	                 $("ul.tostitos").data('count', parseInt(data.count) );
	                 window.recipe_offset = window.recipe_offset + parseInt(data.count);
	                 window.active_tid = tid;
	              }
	          }
	        });

	  }

	function pepsibamLoadRecipes(json_recipes, count){
	   if (count < window.nb_recipes_per_query){
	      $(".more-recipes").addClass("hidden")
	   }
	   if (json_recipes == ""){
	      return;
	   }

	    $.each(json_recipes, function(i, obj) {
	        var recipe_html = createRecipeItem(obj);
	        $(".recipe__menu-detail-wrapper").append(recipe_html);

	    });

	 }

	function createRecipeItem(recipe){

	    var _html = `
	    <div class="recipe__menu-detail-section" data-nid="${recipe.nid}">
        <div class="background-zoom__parent">

            <a href="${recipe.link}" class="img-recipe">
                <img class="background-zoom__child recipe__menu-detail-section-image" src="${recipe.image_url}" />
            </a>
        </div>

        <div class="recipe__menu-detail-sub-wrapper">

            <div class="recipe__menu-detail-sub-wrapper-height">
                <a href="${recipe.link}" class="img-recipe">
                    <h3 class="recipe__menu-detail-wrapper-title" href="${recipe.link}">${recipe.title}</h3>
                </a>

                <div class="recipe__review-wrapper">

                    <div class="recipe__star">
                        <i class="fa fa-star"></i>
                        <span class="recipe__star-rating"> ${recipe.rating}/5</span>
                    </div>

                    <div class="recipe__clock">
                        <i class="far fa-clock"></i>
                        <span class="recipe__time">${recipe.cook_time}</span>
                    </div>

                </div>

                <div class="truncate_text">${recipe.body}</div>
            </div>

            <div class="recipe__btn-wrapper">
                <a href="${recipe.link}" class="recipe__btn_red">
                    ${recipe.cta}
                </a>
            </div>
        </div>

    </div>
	    `;


	    return _html;
	  }

}(jQuery, window));/*end of file*/