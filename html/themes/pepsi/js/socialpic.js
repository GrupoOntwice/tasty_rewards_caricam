/*****************************************************
**
** SOCIALPIC JS
**
** Jquery for the socialpic jquery plugin
**
** Author: Andrew Hartnett
**   Date: November 2014
**
*****************************************************/

(function($){

function overlay($img, opts){
    
        
        /* fixes for contest*/
        isContest = false;
        btntarget='';
        if ($img.hasClass('contestpic')){
            isContest = true;
        }
        
        neww = $img.data('newwindow');
        if (neww == true){
            btntarget = "_blank";
        }
        textabove = $img.data('textabove');
        textaboveflg = false;
        if (textabove > ''){
            textaboveflg = true;
        }
        
        
	var floating = opts.float || 'left';
	var rgba = opts.rgba || '244,193,20,0.8';
	var color = opts.color || '#ffffff';

	$img.wrap('<div class="wrap"></div>');

	$wrap = $img.parent();

	$wrap.css('float',floating);


	$overlay = $('<div>');
	$overlay.addClass('overlay');
	$overlay.css('background','rgba('+rgba+')');
         

	// $links = $('<ul>');
    //
	// $overlay.append($links);
	// $links.addClass('links')
    //
	// $facebook = $('<li>')
	// $facebook.html('<span class="socialclick facebook-share"><i class="fa fa-lg fa-facebook"></i></span>');
	// $twitter = $('<li>')
	// $twitter.html('<span class="socialclick twitter-share"><i class="fa fa-lg fa-twitter"></span>');
	// //$google = $('<li>')
	// //$google.html('<span class="socialclick google-share"><i class="fa fa-lg fa-google-plus"></i></span>');
	// $pinterest = $('<li>')
	// $pinterest.html('<span class="socialclick pinterest-share"><i class="fa fa-lg fa-pinterest"></i></span>');
    //
    //
	// $('span.socialclick').css('color',color);
    //
	// $links.append($facebook);
	// $links.append($twitter);
	// //$links.append($google);
	// $links.append($pinterest);


        if (isContest){
            $overlay.html('<a title="' + $img.data("title") + '"onclick="promotionClickTracking(this);" style="display:inline-block;height:100%;width:100%;" target="' + btntarget + '" href="' + $img.data("contesturl") + '"></a>');

            $contest = $('<ul>');
            $overlay.append($contest);
            $entercontest = $('<li>');
            $entercontest.addClass('btncontest');
            $entercontest.html('<div style="pointer-events:none;background-color: #337ab7 !important;position:absolute;top:62%;left:50%;-webkit-transform: translate(-50%,-50%);" class="btn btn-primary btn-contest btn-contest-tracking">' + $img.data("btntext") + '</div>');
            $contest.append($entercontest);
        }

        if (textaboveflg) {
            $textaboveul = $('<ul>');
            $overlay.append($textaboveul);
            $textabove = $('<li>')
            $textabove.html('<span style="pointer-events:none;position:absolute;top:50%;left:50%;-webkit-transform:translate(-50%,-50%);" class="signika white uppercase text-center bold">'+textabove+'</span>');
            $textaboveul.append($textabove);
            $textaboveul.css('margin-top', (parseFloat($img.height())/2.5)+'px');
        }

	$overlay.css('height',$img.height());
	$overlay.css('width',$img.width());

        if (!textaboveflg) {
            if (typeof $links != 'undefined') {
                $links.css('margin-top', (parseFloat($img.height())/2.5)+'px');
            }
        }


	$overlay.css('margin-top',$img.css('marginTop'));
	$overlay.css('margin-bottom',$img.css('marginBottom'));
	$overlay.css('margin-left',$img.css('marginLeft'));
	$overlay.css('margin-right',$img.css('marginRight'));

	$overlay.css('padding-top',$img.css('paddingTop'));
	$overlay.css('padding-bottom',$img.css('paddingBottom'));
	$overlay.css('padding-left',$img.css('paddingLeft'));
	$overlay.css('padding-right',$img.css('paddingRight'));

	//Add links
	$img.before($overlay);
}

$.fn.socialpic = function(opts){


	var opts = opts || [];
	$(this).each(function(){
		overlay($(this), opts);
	});

	return this;
}



}(jQuery));

(function($){
    
    
    
	$('body').on('click', 'span.socialclick', function(){

//		var url = $(this).closest('div.wrap').find('img').attr('src');
                var url = $("#node_url").val();
                var title = $("#node_title").val();
                var image = $("#node_image").val();
                var pathname = window.location.origin; // Returns path only

                if($(this).hasClass('facebook-share')){
                        // window.open('https://www.facebook.com/sharer/sharer.php?u='+url, 'Share Facebook', config='height=300, width=500');
                }

                if($(this).hasClass('twitter-share')){
                        // window.open('http://twitter.com/home?status='+title+' '+url, 'Share Twitter', config='height=300, width=500');
                }

//                if($(this).hasClass('google-share')){
//                        window.open('https://plus.google.com/share?url='+url, 'Share Google +', config='height=300, width=500');
//                }

                if($(this).hasClass('pinterest-share')){
                        if (image.indexOf("media.tastyrewards.ca") >= 0){
                            // window.open('http://www.pinterest.com/pin/create/button/?url='+url+'&media='+image+'&description='+title, 'Share Pinterest', config='height=300, width=500');
                        }else{
                            // window.open('http://www.pinterest.com/pin/create/button/?url='+url+'&media='+pathname+image+'&description='+title, 'Share Pinterest', config='height=300, width=500');
                        }
                        
                }

	});
        $('body').on('click', '.social-share .fa.fa-lg', function(event){
                
                
                
                var face_utm = encodeURI('?utm_source=facebook&utm_medium=referral&utm_campaign=contest-share-button');
                var twit_utm = encodeURI('?utm_source=twitter&utm_medium=referral&utm_campaign=contest-share-button');
                var pint_utm = encodeURI('?utm_source=pinterest&utm_medium=referral&utm_campaign=contest-share-button');

                event.preventDefault();
                var url = $("#node_url").val();
                var title = $("#node_title").val();
                var image = $("#node_image").val();
                var pathname = window.location.origin; // Returns path only
                var mailto = $("#node_mailto").val();
                
                var contestname = $("#node_name").val();
                
                var urlfb = url;
                var urltw = url;
                var urlpt = url;
                if (contestname>'') {
                    
                    var theLanguage = $('html').attr('lang');
                    if (theLanguage == 'fr') {
                        urlfb = location.protocol + "//" + location.host+"/cntfrfb/"+contestname+"/";
                        urltw = location.protocol + "//" + location.host+"/cntfrtw/"+contestname+"/";
                        urlpt = location.protocol + "//" + location.host+"/cntfrpt/"+contestname+"/";
                    }
                    else {
                        urlfb = location.protocol + "//" + location.host+"/cntenfb/"+contestname+"/";
                        urltw = location.protocol + "//" + location.host+"/cntentw/"+contestname+"/";
                        urlpt = location.protocol + "//" + location.host+"/cntenpt/"+contestname+"/";
                    }
                }

                if($(this).hasClass('fa-facebook')){
                        // window.open('https://www.facebook.com/sharer/sharer.php?u='+urlfb, 'Share Facebook', config='height=300, width=500');
                }

                if($(this).hasClass('fa-twitter')){
                        // window.open('http://twitter.com/home?status='+title+' '+urltw, 'Share Twitter', config='height=300, width=500');
                }

                if($(this).hasClass('fa-pinterest')){
                        if (image.indexOf("media.tastyrewards.ca") >= 0){
                            // window.open('http://www.pinterest.com/pin/create/button/?url='+urlpt+'&media='+image+'&description='+title, 'Share Pinterest', config='height=300, width=500');
                        }else{
                            // window.open('http://www.pinterest.com/pin/create/button/?url='+urlpt+'&media='+pathname+image+'&description='+title, 'Share Pinterest', config='height=300, width=500');
                        }                 
                }
                if($(this).hasClass('fa-envelope')){
                        //window.location.href = mailto;
                }
                if($(this).hasClass('fa-print')){
                       // window.print();
                }
                
        });

}(jQuery));
