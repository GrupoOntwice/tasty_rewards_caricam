(function($) {
$(document).ready(function(){
        //hide the mobile nav
        $(".mobileNav").hide();
        //carousel has to stop sliding
        //$('.carousel').carousel({interval: false});  
        $('#topCarousel').carousel({interval:5000,cycle: true});
});

// Display hide mobile menu
$(".navbar-toggle").click(function() {
        $(".mobileNav").removeClass("hidden");
        $(".mobileNav").fadeIn('fast');
});
$("#mobileClose").click(function() {
        $(".mobileNav").fadeOut('fast');
});
$(window).on('resize', function(){
    if($(".mobileNav").is(':visible')){
        $(".mobileNav").fadeOut('fast');
    }
});
//display hide brands strip
$("#brandsToggle").click(function(){
    $("#brandsStrip").toggleClass("activeStrip");
    $("#brandsToggle").toggleClass("activeStrip");
    $("#brandsToggle .toggleIcon").toggleClass("glyphicon-plus glyphicon-minus");
});
$("#brandsToggleMobile").click(function(){
    $("#brandsStripMobile").toggleClass("activeStrip"); 
    $("#brandsToggleMobile").toggleClass("activeStrip");
    $("#brandsToggleMobile .toggleIconMobile").toggleClass("glyphicon-plus glyphicon-minus");
});

//display hide share
$("#mobileShareToggle").click(function(){
    $("#mobileShare").toggleClass("activeStrip");
    $("#mobileShareToggle").toggleClass("activeStrip");
    $("#mobileShareToggle .toggleIconMobile").toggleClass("glyphicon-plus glyphicon-minus");
});

// brands carousel on home page desktop
$("#brands .left").click(function () {
    var bodywidth = screen.width;
    if(bodywidth > 1170){
        bodywidth = 1170;
    }
    $(".brandsContainer").animate({"scrollLeft": $(".brandsContainer").scrollLeft() - (Math.floor((bodywidth-100)/280)*280)}, 400);
});
$("#brands .right").click(function () {
    var bodywidth = screen.width;
    if(bodywidth > 1170){
        bodywidth = 1170;
    }
    $(".brandsContainer").animate({"scrollLeft": $(".brandsContainer").scrollLeft() + (Math.floor((bodywidth-100)/280)*280)}, 400);
});

//if input has been filled, change it's color
$("input").on('blur', function () {
    if ($(this).val()) {
        $(this).addClass("filled");
    }
    else {
        $(this).removeClass("filled");
    }
});
        
})(jQuery);









