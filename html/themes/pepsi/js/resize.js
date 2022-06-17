//INITIALISING VARS FOR HEIGHTS AND BROWSERS
(function($) {
    

var windowHeight = function(){return $(window).innerHeight(); };
var	windowWidth = function(){return verge.viewportW(); };
var	bodyHeight = function(){return $('body').innerHeight(); };
var	bodyWidth = function(){return $('body').innerWidth(); };

var isMobile = {
    Android: function() {
        return navigator.userAgent.match(/Android/i);
    },
    BlackBerry: function() {
        return navigator.userAgent.match(/BlackBerry/i);
    },
    iOS: function() {
        return navigator.userAgent.match(/iPhone|iPad|iPod/i);
    },
    Opera: function() {
        return navigator.userAgent.match(/Opera Mini/i);
    },
    Windows: function() {
        return navigator.userAgent.match(/IEMobile/i);
    },
    any: function() {
        return (isMobile.Android() || isMobile.BlackBerry() || isMobile.iOS() || isMobile.Opera() || isMobile.Windows());
    }
};
function ifIE8(){
    if(navigator.userAgent.indexOf("MSIE 8")!=-1){
        //alert('MSIE 8');
        return true;
    }else{
        //alert('!MSIE 8');
        return false;   
    }
}
var isMacLike = navigator.platform.match(/(Mac|iPhone|iPod|iPad)/i)?true:false;

var screenWidth = {
    phone480: function(){
        if($('body').hasClass('phone480')){return true;}    
    },
    tablet768: function(){
        if($('body').hasClass('tablet768')){return true;}   
    },
    tablet1024: function(){
        if($('body').hasClass('tablet1024')){return true;}  
    },
    desktop: function(){
        if($('body').hasClass('desktop')){return true;} 
    }
};


function setBodyClass(){
    if(windowWidth()>=1024){
        $('body').removeClass('desktop tablet1024 tablet768 phone480');
        $('body').addClass('desktop');
    }else if(windowWidth()>=768 && windowWidth()<=1023 ){
        $('body').removeClass('desktop tablet1024 tablet768 phone480');
        $('body').addClass('tablet1024');
    }else if(windowWidth()>=480 && windowWidth()<=767 ){
        $('body').removeClass('desktop tablet1024 tablet768 phone480');
        $('body').addClass('tablet768');
    }else if(windowWidth()>=320 && windowWidth()<=479 ){
        $('body').removeClass('desktop tablet1024 tablet768 phone480');
        $('body').addClass('phone480');
    }
}


/*!
 * verge 1.9.1+201402130803
 * https://github.com/ryanve/verge
 * MIT License 2013 Ryan Van Etten
 */
!function(a,b,c){"undefined"!=typeof module&&module.exports?module.exports=c():a[b]=c()}(this,"verge",function(){function a(){return{width:k(),height:l()}}function b(a,b){var c={};return b=+b||0,c.width=(c.right=a.right+b)-(c.left=a.left-b),c.height=(c.bottom=a.bottom+b)-(c.top=a.top-b),c}function c(a,c){return a=a&&!a.nodeType?a[0]:a,a&&1===a.nodeType?b(a.getBoundingClientRect(),c):!1}function d(b){b=null==b?a():1===b.nodeType?c(b):b;var d=b.height,e=b.width;return d="function"==typeof d?d.call(b):d,e="function"==typeof e?e.call(b):e,e/d}var e={},f="undefined"!=typeof window&&window,g="undefined"!=typeof document&&document,h=g&&g.documentElement,i=f.matchMedia||f.msMatchMedia,j=i?function(a){return!!i.call(f,a).matches}:function(){return!1},k=e.viewportW=function(){var a=h.clientWidth,b=f.innerWidth;return b>a?b:a},l=e.viewportH=function(){var a=h.clientHeight,b=f.innerHeight;return b>a?b:a};return e.mq=j,e.matchMedia=i?function(){return i.apply(f,arguments)}:function(){return{}},e.viewport=a,e.scrollX=function(){return f.pageXOffset||h.scrollLeft},e.scrollY=function(){return f.pageYOffset||h.scrollTop},e.rectangle=c,e.aspect=d,e.inX=function(a,b){var d=c(a,b);return!!d&&d.right>=0&&d.left<=k()},e.inY=function(a,b){var d=c(a,b);return!!d&&d.bottom>=0&&d.top<=l()},e.inViewport=function(a,b){var d=c(a,b);return!!d&&d.bottom>=0&&d.right>=0&&d.top<=l()&&d.left<=k()},e});


    
    $(window).on('resize', function(){

        
            setBodyClass();
            var box2x1 = $('.size-2x1');
            var box1x1 = $('.size-1x1');
            var box1x1big = $('.size-1x1big');
            var box1x1huge = $('.size-1x1huge');

            if(windowWidth()>=1024){
                
                //DESKTOP
                
                
                //small box takes a quarter of the body width 
                box1x1Width = bodyWidth()/4;
                //big box takes half the body width
                box1x1bigWidth = bodyWidth()/2;
                
                
                box2x1.css('height', box1x1Width+'px');
                box2x1.css('width', (box1x1Width*2)+'px');

                box1x1.css('height', box1x1Width+'px');
                box1x1.css('width', box1x1Width+'px');

                box1x1big.css('height', box1x1bigWidth+'px');
                box1x1big.css('width', box1x1bigWidth+'px');

                box1x1huge.css('height', (box1x1bigWidth+40)+'px');
                box1x1huge.css('width', box1x1bigWidth+'px');

                //if feed box then less wide because of padding
                $('.size-2x1.box').css('height', (box1x1Width+40)+'px');
            

            }else 
            if(windowWidth()>=768 && windowWidth()<1024){

                //TABLET

                //small box takes a quarter of the body width                
                box1x1Width = Math.floor(bodyWidth()/4);
                //big box takes half the body width
                box1x1bigWidth = Math.floor(bodyWidth()/2);
                
                box2x1.css('height', box1x1Width+'px');
                box2x1.css('width', (box1x1Width*2)+'px');

                box1x1.css('height', box1x1Width+'px');
                box1x1.css('width', box1x1Width+'px');

                box1x1big.css('height', box1x1bigWidth+'px');
                box1x1big.css('width', box1x1bigWidth+'px');

                box1x1huge.css('height', (box1x1bigWidth+40)+'px');
                box1x1huge.css('width', box1x1bigWidth+'px');

                //if feed box then less wide because of padding
                $('.size-2x1.box').css('height', (box1x1Width+40)+'px');
                


            }
            else 
            if(windowWidth()>=400 && windowWidth()<768){ 
                
                //PHONE
                
                //small box takes half the body width
                box1x1Width = Math.round(bodyWidth());
                //bix box takes full width
                box1x1bigWidth = Math.floor(bodyWidth());
                
                box2x1.css('height', (box1x1Width/2)+'px');
                box2x1.css('width', box1x1Width+'px');

                box1x1.css('height', box1x1Width+'px');
                box1x1.css('width', (box1x1Width-1)+'px');

                box1x1big.css('height', box1x1bigWidth+'px');
                box1x1big.css('width', box1x1bigWidth+'px');

                box1x1huge.css('height', (box1x1bigWidth+40)+'px');
                box1x1huge.css('width', box1x1bigWidth+'px');

                //if feed box then less wide because of padding
                $('.size-2x1.box').css('height', ((box1x1Width/2)+40)+'px');

                box1x1huge.css('width', '100%');
                box1x1huge.css('height', '100%');
                //$('.size-2x1.box').css('height', '100%');
            }

            else 
            if(windowWidth()>=300 && windowWidth()<400){ 
                
                //PHONE
                //small box takes half the body width
                box1x1Width = Math.round(bodyWidth());
                //bix box takes full width
                box1x1bigWidth = Math.floor(bodyWidth());
                
                box2x1.css('height', (box1x1Width/2)+'px');
                box2x1.css('width', box1x1Width+'px');

                box1x1.css('height', box1x1Width+'px');
                box1x1.css('width', (box1x1Width-1)+'px');

                box1x1big.css('height', box1x1bigWidth+'px');
                box1x1big.css('width', box1x1bigWidth+'px');

                box1x1huge.css('height', box1x1bigWidth+'px');
                box1x1huge.css('width', box1x1bigWidth+'px');

                //if feed box then less wide because of padding
                $('.size-2x1.box').css('height', ((box1x1Width))+'px');
                box1x1huge.css('width', '100%');
                box1x1huge.css('height', '100%');

                if ($('.path-contests').length > 0) {
                    box2x1.css('height', (box1x1Width/2)+'px');
                    box2x1.css('width', box1x1Width+'px');
                    $('#morecomingleft').find('.size-2x1.box').css('height', ((box1x1Width))+'px');
                    $('#morecomingleft').find('.size-2x1.box').css('width', 'auto');
                }

            }

    }).resize();

    // resize on load to make sure the display is correct! 
    $(document).ready(function(){
        $(window).resize();
    });
    })(jQuery);