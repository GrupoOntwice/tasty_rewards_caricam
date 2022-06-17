(function ($, window) {

  $(document).ready(function () {
     if ($("#snack-shop").length > 0 ){
       document.getElementById('snack-shop').src = document.getElementById('snack-shop').src;
     }

     $(".js-buynow").click(function(){
      if ($("#snack-shop").length > 0 ){
        document.getElementById('snack-shop').src = document.getElementById('snack-shop').src;
      }

     });


     $(".js-shop").click(function(){
        let shoplink = $(this).data('link');
        window.open(shoplink, '_blank');

     });

     load_all_iframes();

  });

  function load_all_iframes(){

    for (let i = 0; i < 10; i++) {
        if ($("#snack-shop-" + i).length > 0 ){
         document.getElementById('snack-shop-' + i).src = document.getElementById('snack-shop-' + i).src;
       }
    }

     $(".js-buynow").click(function(){
      var index = $(this).data('counter');
       if ($("#snack-shop-" +index ).length > 0 ){
          document.getElementById('snack-shop-' + index).src = document.getElementById('snack-shop-' + index).src;
       }
     });

  }

}(jQuery, window));/*end of file*/