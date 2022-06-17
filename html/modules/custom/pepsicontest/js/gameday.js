
  window.recipe_offset = 6;
  window.nb_recipes_per_query = 6;

(function ($, window) {


	$(document).ready(function () {

	      if ($(".more-recipes").length) {
	        clickMoreRecipes();
	      }
	  });

	function clickMoreRecipes(){
    $(".more-recipes").click(function(){
       var lang = window.language;
        // var offset = $("ul.tostitos").data('count');
        var offset = window.recipe_offset;
        // var tid = window.active_tid;
        // var prep_time = window.selected_preptime;
        var brand = 'gameday';

    		$.ajax({
            url:"/brands/" + brand + "/load-recipes/" + offset,
            type: "POST",
            data: {"lang": lang},
            success:function(json) {
                const data = JSON.parse(json);
                if (data.status == 'success'){
            	     console.log(data);
            	     loadRecipes(data.recipes, data.count);
                   // $("ul.tostitos").data('count', parseInt(offset) + parseInt(data.count) );
                  window.recipe_offset = window.recipe_offset + parseInt(data.count);
                   // window.recipe_offset = parseInt(data.new_offset);
                   console.log(" DATA count " + data.count);
                   console.log(" Recipe count " + window.recipe_offset);
                }
            }
          });
    });
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
	        $(".gameday-nocarousel-container").append(recipe_html);

	    });

	 }

	function createRecipeItem(recipe){
	    var _html = `
	    <div class="col-md-4 gameday-recipes">
	    	<div class="gameday-nocarousel-box" data-nid="${recipe.nid}">
	    	<a href="${recipe.link}" class="">
	            <div class="gameday-recipe-img" style = "background-image: url('${ recipe.image_url }')" ></div>
		            </a>

		             <div class="gameday-recipe-title">
              <a href="{{ recipe.link }}">
                <h3 class="padding-top padding-bottom yellow somerton-regular ">
                  ${recipe.title}
                </h3>
              </a>
            </div>


	            <div class="gameday-activation-recipe-btn margin-bottom-medium">
		            <a href="${recipe.link}" class="gameday-btn gameday-btn-yellow m-auto text-uppercase opensans-regular">
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

  $(document).ready(function () {

    var $toActivateVideo = $(".activate-video");

    function activateVideos() {
      $toActivateVideo.off().on('click', function () {
        let key = $(this).data('key');
        let iframe = '<div class="gameday-video-iframe-container"><iframe src="https://www.youtube.com/embed/' + key + '?autoplay=1&rel=0" frameborder="0" allow="autoplay" allowfullscreen></iframe><div>';
        $(this).closest('.gameday-video-box').prepend(iframe);
        $(this).closest('.gameday-video-box').find('.gameday-video-preimg').remove();
        return false;
      });
    }

    activateVideos();


  });

  $('.carousel').slick({
    infinite: true,
    slidesToShow: 2,
    centerMode: true,
    slidesToScroll: 1,
    responsive: [
      {
        breakpoint: 1008,
        settings: {
          slidesToShow: 1,
          slidesToScroll: 1
        }
      },
      {
        breakpoint: 800,
        settings: {
          slidesToShow: 1,
          slidesToScroll: 1
        }
      }
    ]
  });
  let count = $(".carousel").slick("getSlick").slideCount;
  console.log(count);

  if(count == 1) {
    console.log("more than 1 slide");
    $('.carousel').slick('slickSetOption', 'slidesToShow', 1);
    $('#video-img').addClass('one-video');
    $('.gameday-video-box').addClass('play-video-iframe');

  }





}(jQuery, window));/*end of file*/
