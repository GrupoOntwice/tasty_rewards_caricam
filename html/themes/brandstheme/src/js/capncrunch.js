
(function ($, window) {

  $(document).ready(function () {
  		$(".js-scroll-to-recipe").click(function(){
            if (! window.is_recipe_page){
  			   var elmnt = document.getElementById("recipes");
			     elmnt.scrollIntoView();
            }
  		});

      $(".js-scroll-to-about").click(function(){
          var elmntAbout = document.getElementById("about");
          if (elmntAbout){
            elmntAbout.scrollIntoView();
          }
      });

      if (window.is_recipe_page){
        $(".js-home").click(function(e){
            e.preventDefault();
            window.location.href = $(this).data('link');
        });
      }

  });

// Nutrition Show and Hide Animation

        // $(".fa-nutrition-icon .fa-swap").click(function () {
        //     $(this).toggleClass('capncrunch-fa-cirle-down');
        //     $(this).parent().parent().parent().children('.nutrition-copy').toggle('.d-none');
        // });
        //
        // // Nutrition Show and Hide Animation
        //
        // $(".fa-ingredients-icon .fa-swap").click(function () {
        //     $(this).toggleClass('capncrunch-fa-cirle-down');
        //     $(this).parent().parent().parent().children('.ingredients-copy').toggle('.d-none');
        // });

//dropdown quaker product detail
//     $(document).ready(function () {
//         $('.nutrition-copy-js').hide('');
//         $('.ingredient-copy-js').hide('');
//
//
//         $(".js-quaker-nutrition-swap").click(function () {
//             $(this).toggleClass('active');
//             if ($('.js-quaker-nutrition-swap').hasClass('active')) {
//                 $('.nutrition-copy-js').show('fast');
//             } else {
//                 $('.nutrition-copy-js').hide('fast');
//             }
//         });
//
//         $(".js-quaker-ingredient-swap").click(function () {
//             $(this).toggleClass('active');
//             if ($('.js-quaker-ingredient-swap').hasClass('active')) {
//                 $('.ingredient-copy-js').show('fast');
//             } else {
//                 $('.ingredient-copy-js').hide('fast');
//             }
//         });
//     })





    $(document).ready(function () {
        $('.nutrition-copy-js').hide('');
        $('.ingredient-copy-js').hide('');


        $(".js-quaker-nutrition-swap").click(function () {
            $(this).toggleClass('active');
            if ($(this).hasClass('active')) {
                $(this).parent().next(".nutrition-copy-js").show('fast');
            } else {
                $(this).parent().next(".nutrition-copy-js").hide('fast');
            }
        });

        $(".js-quaker-ingredient-swap").click(function () {
            $(this).toggleClass('active');
            if ($(this).hasClass('active')) {
                $(this).parent().next(".ingredient-copy-js").show('fast');
            } else {
                $(this).parent().next(".ingredient-copy-js").hide('fast');
            }
        });
    })


  $(".capncrunch-buy").click(function(){
    $(".capncrunch-navbar__vertical-align").toggleClass("index");
  });

  $(".closeModal").click(function(){
    $(".capncrunch-navbar__vertical-align").toggleClass("index");
  });

}(jQuery, window));/*end of file*/

