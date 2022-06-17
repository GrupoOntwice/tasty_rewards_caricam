// Top Bar Scroll shrink thingy

    (function($) {
    $(document).scroll(function(){
        
        var top = $(document).scrollTop();
        var shrinkOn = 0; // number of pixels before shrinking

            if (top > shrinkOn) {
                $('header').addClass("smaller");
                $('#search-bar').addClass("smaller");
                $('#navbar-toggle').addClass("smaller");
            } else {
                if ($('header').hasClass("smaller")) {
                    $('header').removeClass("smaller");
                    $('#search-bar').removeClass("smaller");
                    $('#navbar-toggle').removeClass("smaller");
                }
            }
    });        
})(jQuery);