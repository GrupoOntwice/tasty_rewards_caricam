/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */





(function($) {
 
$(document).ready(function() {
   setTimeout(function(){ 
      $(".ui-loader").hide(); 
   },10);
});

$(document).ready(function(){ 
    window.addEventListener("message", receiveMessage, false);
    function receiveMessage(event)
    {
        if (event.data==="bamLogOut=true"){
             // ACTION TO OPEN THE LOGIN
             $("#mk-login").click();
        }
        else
            return;
    }   
    
    if ($('form[id*="poll-view-form-"]').length > 0 ) {
        var pollform = $('form[id*="poll-view-form-"]').first().attr('id');
        var poll_id = pollform.replace('poll-view-form-','');
        getInfo(poll_id);
    }
    
    $('#edit-vote').attr('disabled','disabled');
    
    $('input[id*="edit-choice-"]').on('touchstart click',function(){
        $('#edit-vote').removeAttr('disabled');
    });
    
    
    
    var lang = $('html').attr('lang');
    var page = '';
    if ($('body.path-frontpage').length > 0 ) {
        page = 'home';
    } 
    if ($('body.path-life').length > 0 || $('body.path-mode-de-vie').length > 0 ) {
        page = 'life';
    } 
    if ($('body.path-recipes').length > 0 || $('body.path-recettes').length > 0 ) {
        page = 'recipe';
    } 
    
    
    language_page_link();   // adjust language page link
    adjustLoginPopup();     // adjust login popup   
    adjustBrandsMobile();     // adjust the brands callout for mobile 
    adjustBrandsWithoutLink();     // adjust the brands callout without links
    adjustViewMoreLink();       //hide the button if the view doesn't have results
    if ($('#CouponFrame').length > 0){
        $('#CouponFrame').iFrameResize();
    }
    
    
    // Ajax infiniti scroll -  when finishes trigger resize event to adjust the template
    $( document ).ajaxComplete(function(event, xhr, settings) {
        if (settings.url.indexOf("ajax?") >= 0 && settings.url.indexOf("_wrapper_format=drupal_ajax") >= 0){
//        if ( settings.url === "/"+lang+"/views/ajax?_wrapper_format=drupal_ajax" ) {
           $(window).trigger("resize");
        }      
    });
    
    //recipe rating
    $('#star1').starrr({
        emptyClass: 'yellow glyphicon glyphicon-star-empty',
        fullClass: 'yellow glyphicon glyphicon-star',  
        rating: $("#node_rating_average").val(),    //initial value
        change: function(e, value){
          if (value) {
            processValue(value);    // process the vote and save in DB and the cookie
            $('.your-choice-was').show();
            $('.choice').text(value);
          } else {
            $('.your-choice-was').hide();
          }
        }
    });
    
    
    //search box - when press enter on header input  go to search page
    $('#search-terms').keypress(function (e) {
        var key = e.which;
        if(key == 13){  // the enter key code
        
            goSearchPage();
            return false;  
        }
    });
    
    //articles and recipe page - category filters
    
    
    $("select[name=category-filters]").on("change", function(){
            var clicked_select = $(this).index('select[name=category-filters]');
            var url = $(this).val().toLowerCase().replace(/ /g,"-");
            var path = $("#view_path").val() + "/" ;
            $("select[name=category-filters]").each(function( index ) {
                if (clicked_select != index){
                    $(this).val("");
                }
            });
            $("#search_filter").attr("href",path + url);
    });
    
    //articles and recipe page - category filters
    
    $(".brand a.brand-icon").click(function(event) {
            
        if ( $(this).attr("href") == "#" ) {
            event.stopPropagation();
            return false;
        }
        
        
    });
    
    adjustPollInputFirefox();

    if(window.location.href.indexOf("www.tastyrewards.ca")!=-1){
        setTimeout(promotionViewTracking,2000);
    }

});


//adjust the radio input on the for 
function  adjustPollInputFirefox(){
    var FF = !(window.mozInnerScreenX == null); //determine if the browser is Firefoxx
    if (FF && $('.poll #edit-choice').length > 0){
        $('.poll #edit-choice input:radio').prop("checked", false);
    }
    
}
// adjust the brands callout without links
function adjustBrandsWithoutLink(){
    
    
    $("a.brand-icon").each(function () {
        var link = $(this).attr("href");
        if (link == "/en/node" || link == "/fr/node"){
            $(this).attr("href", "#");
            $(this).removeAttr('target');
        } 
    });
}

// adjust the brands callout for mobile 
function adjustBrandsMobile(){
    $('#brandsStripMobile .brandsMobileWrapper').children('.brand').each(function () {
        if (!$(this).hasClass("col-xs-3 ")){
            $(this).addClass("col-xs-3 text-center");
            $(this).removeClass("brand");
            $(this).children("a").children("img").addClass("img-responsive brand-icon-mobile");
        }
    });
}

//move the FB button to the top of the form
function adjustLoginPopup(){
    
    $("#fancy-login-user-login-block .fbconnect").detach().prependTo('#fancy-login-user-login-block');
}

//hide the View More button if the view doesn't have results
function adjustViewMoreLink()  {
    
    if ($("#notfound").length > 0){
        $(".views-element-container .view .pager").hide();
    }
}     


//after press enter on the seach box, go to search page 
function goSearchPage(){
   //
    var lang = $('html').attr('lang');
    var search_text = $('#search-terms').val();
    
    if (lang == "en"){
        var path = "/en/search?search_api_fulltext=" + search_text;
    }else{
        var path = "/fr/recherche?search_api_fulltext=" + search_text;
    }

    // window.location.href = path;
}

 //Homepage
$(function(){
    $('.socialpic').socialpic();
//    $('#topCarousel').carousel({interval:true});
    
    $( window ).resize(function() {
        /* removing and adding socialpic each resize*/
        if ($('.socialpic').parent().has('.wrap')){
            $('.wrap').children('.overlay').remove();
        }
        $('.wrap').children('.socialpic').unwrap();
        $('.socialpic').socialpic();
        centercontesttitle();
    
    });
    
    centercontesttitle();
});

function centercontesttitle(){
        $('.topBlockContest').each(function( index ) {
            if ($(this).find('#topLeft').length && ($(this).find('#topLeft').offset().top == $(this).find('#topRight').offset().top)
                &&   $(this).find('#topLeft').height() > $(this).find('#topRight').height()){
                $(this).find('.contestdescription').css('margin-top',$(this).find('#topLeft').height() * 0.4);
            } else if ($(this).find('.contestdescription').length) {
                $(this).find('.contestdescription').css('margin-top',0);
            }
        });
    
}

// adjust language page link
function language_page_link(){
    
    var lang_link = jQuery('.language-link:not(.is-active)').attr('href');
    var url = adjustUrls(lang_link);
    jQuery('.lang-switcher').attr('href',url);
    
   // console.log(drupalSettings.path.currentLanguage);
}

//adjust the url translation for some links
function adjustUrls(lang_link){
    var new_url = lang_link;
    if (lang_link == "/fr/recipes"){
        new_url = lang_link.replace("recipes", "recettes");
        
    }else if (lang_link == "/en/recettes"){
        new_url = lang_link.replace("recettes", "recipes");
        
    }else if (lang_link == "/en/mode-de-vie"){
        new_url = lang_link.replace("mode-de-vie", "life");
        
    }else if (lang_link == "/fr/life"){
        new_url = lang_link.replace("life", "mode-de-vie");       
    }
   return new_url; 
}


// process the vote and save in DB and the cookie
function processValue(vote_value){
    
    var node_id = $("#node_id").val();

    if (node_id != ""){
               
            var exists = existCookies();    // verify if the vote cookie exists
            
            if (exists){
                processVoteCookies(node_id, vote_value);    // modify the node's vote in the cookies and update it on the DB
            }else{
                createVoteCookies(node_id, vote_value);     // create the node's vote in the cookies and save it on the DB
            }
            
    }else{
        console.log("node id empty");
    }

}

// verify if the cookie exists
function existCookies(){

    if (getCookie('vote') === ''){
        return false;   //no cookie
    } else {
        return true;    //have cookie
    }
    
}

// create the node's vote in the cookies and save it on the DB
function createVoteCookies(node_id,value){
    
    var vote = [
        { 'node' : node_id, 'value' : value }
    ];
    setCookie("vote", JSON.stringify(vote), 10); //save the value in the cookie
    saveVoteInDB(node_id, value);              //save the value in the DB
}

// modify the node's vote in the cookies and update it on the DB
function processVoteCookies(id, value){
    
    var node_value = getVoteValueInCookies(id);     //get the vote value saved in the cookie
   
    if (node_value == value){       //if the value is the same, do nothing
        
        return false; //do nothing
    }else{
        //update vote value
        var updated = updateVoteValueInCookies(id, value);
        if (updated){
           updateVoteInDB(id, value, node_value);  
        }else{
           saveVoteInDB(id, value); 
        }
        
    }
    
}

//get the vote value saved in the cookie
function getVoteValueInCookies(id){
    
    var vote_arr = $.parseJSON(getCookie("vote"));
    var result = $.grep(vote_arr, function(e){return e.node == id; });   //search the elem on the array 
    
    var value = 0;
    if (result.length > 0) {
        value = result[0].value;    
    }
    return value;
}

//update the vote in the cookie
function updateVoteValueInCookies(id, value){
//    var vote = [
//                { 'node' : node_id, 'value' : value },
//             ];
    var updated = false;
    var vote_arr = $.parseJSON(getCookie("vote"));
    var i = 0;
    for ( i; i < vote_arr.length; ++i) {
        // search the node and update the value
        if (vote_arr[i].node == id){
            vote_arr[i].value = value;
            updated = true;
        }
    }
    // if doesn't find the node, insert the value on the array
    if (!updated ){
       vote_arr.push({ 'node' : id, 'value' : value }); 
    }
    
    setCookie("vote", JSON.stringify(vote_arr), 10)
    return updated;
     
}

// save the vote in the DB
function saveVoteInDB(id, value){
    var lang = $('html').attr('lang');
    var node_data = { 'node' : id, 'value' : value };
    
    $.ajax({
          url:"/" + lang + "/pepsi/saverecipevote/ajaxaction",
          type: "POST",
          data:  node_data,
          success:function(data) {
              
              var averag = data.average;
              var total = data.total;
              updateStars(averag, total)
          }
        });
}

// save the vote in the DB
function updateVoteInDB(id, value,old_value){
    var lang = $('html').attr('lang');
    var node_data = { 'node' : id, 'value' : value , 'old_value': old_value};
    
    $.ajax({
          url:"/" + lang + "/pepsi/updaterecipevote/ajaxaction",
          type: "POST",
          data:  node_data,
          success:function(data) {
              var averag = data.average;
              var total = data.total;
              updateStars(averag, total)

          }
        });
}

function updateStars(averag, total){
    $("#reviewsNumber").html(total);
    printStars(averag);
}


//print the stars according to the average
function printStars(averag){
    //remove the fullClass 
    $('#star1 a').removeClass("glyphicon-star-empty");
    $('#star1 a').removeClass("glyphicon-star");
    $('#star1').children('a').each(function () {
        //console.log(this.value); // "this" is the current element in the loop
        if (averag !=0 ){
            $(this).addClass("glyphicon-star");
            averag --;
        }else{
            $(this).addClass("glyphicon-star-empty");
        }
    });
    
}

function setCookie(cname, cvalue, exdays) {
    var d = new Date();
    d.setTime(d.getTime() + (exdays*24*60*60*1000));
    var expires = "expires="+d.toUTCString();
    // document.cookie = cname + "=" + cvalue + "; " + expires;
}

function getCookie(cname) {
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for(var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
}

function getInfo(poll_id) {
  $.ajax({
    url: '/pepsi/verifypollvote/ajaxaction',
    type: 'post',
    data: {
      pollid: poll_id,
    },
    dataType: 'json',
    success: function (response) {
      if (response.status == '1') {
        location.reload(true);
      }
    }
  });
}


    // Track banners display
    function promotionViewTracking() {
        console.log("promotionView start");

        var internalPromotions = {
            promos: [],
            count: 0
        };

        Array.prototype.slice.call(document.getElementsByTagName("div")).forEach(function (element) {

            var promo_name = '';
            var promo_name_arr = new Array();

            var current_url = window.location.href;
            if(current_url.substring(current_url.length-8,current_url.length) == "/en/life"
                || current_url.substring(current_url.length-9,current_url.length) == "/en/life/"
                || current_url.substring(current_url.length-15,current_url.length) == "/fr/mode-de-vie"
                || current_url.substring(current_url.length-16,current_url.length) == "/fr/mode-de-vie/"){
                promotion_current_position = "life_promotion_RTP";
                coupon_current_position = "life_coupon_RTP";
            }else if(current_url.substring(current_url.length-11,current_url.length) == "/en/recipes"
                || current_url.substring(current_url.length-12,current_url.length) == "/en/recipes/"
                || current_url.substring(current_url.length-12,current_url.length) == "/fr/recettes"
                || current_url.substring(current_url.length-13,current_url.length) == "/fr/recettes/"){
                promotion_current_position = "recipes_promotion_RTP";
                coupon_current_position = "recipes_coupon_RTP";
            }else{
                promotion_current_position = "homepage_promotion";
                coupon_current_position = "homepage_coupon_RTP";
            }

            if (element.id == "middle-banner"
                || element.id == "banniere-milieu"
                || element.id == "top-banner"
                || element.id == "bottom-banner"
                || element.id == "banniere-haut"
                || element.id == "banniere-bas"
            ) {
                internalPromotions.promos[internalPromotions.count]={};
                if(element.getElementsByTagName("iframe").length > 0){
                    if(element.id == "top-banner" ||  element.id == "banniere-haut"){
                        promo_name = document.getElementById("topAd_active_id").value;
                    }else if(element.id == "bottom-banner" ||  element.id == "banniere-bas"){
                        promo_name = document.getElementById("bottomAd_active_id").value;
                    }else{
                        promo_name = element.getElementsByTagName("iframe")[0].id;
                    }
                }else if(element.getElementsByTagName("a").length > 0){
                    promo_name = element.getElementsByTagName("a")[0].id;
                }else{
                    promo_name = "site_undefined";
                }
                promo_name_arr = promo_name.split('-');
                internalPromotions.promos[internalPromotions.count].name = promo_name_arr[0];
                internalPromotions.promos[internalPromotions.count].id = promo_name.replace(/ /g,"_");
                if(element.id == "middle-banner" || element.id == "banniere-milieu"){
                    internalPromotions.promos[internalPromotions.count].position = element.id;
                }else{
                    internalPromotions.promos[internalPromotions.count].position = element.id+"_RTP";
                }
                internalPromotions.promos[internalPromotions.count].creative = "";
                internalPromotions.count+=1;
            }else if(element.id == "topPromotion" || element.id == "topSnacktivity"){
                element_img = element.getElementsByTagName("img");
                if(element_img.length > 0 && (element_img[0].id == "homepage_promotion" || element_img[0].id == "homepage_snacktivity") ){
                    internalPromotions.promos[internalPromotions.count]={};
                    promo_name = element_img[0].alt;
                    promo_name_arr = promo_name.split('-');
                    internalPromotions.promos[internalPromotions.count].name = promo_name_arr[0];
                    internalPromotions.promos[internalPromotions.count].id = promo_name.replace(/ /g,"_");

                    if(element_img[0].id == "homepage_promotion"){
                        internalPromotions.promos[internalPromotions.count].position = promotion_current_position;
                    }else{
                        internalPromotions.promos[internalPromotions.count].position = element_img[0].id;
                    }

                    internalPromotions.promos[internalPromotions.count].creative = "";
                    internalPromotions.count+=1;
                }
            }else if(element.id == "topCarousel"){
                element_img = element.getElementsByTagName("img");
                if(element_img.length > 0){
                    for(var i=0;i<element_img.length;i++){
                        if($(element_img[i]).hasClass("homepage_carousel")){
                            internalPromotions.promos[internalPromotions.count]={};
                            promo_name = element_img[i].alt;
                            promo_name_arr = promo_name.split('-');
                            internalPromotions.promos[internalPromotions.count].name = promo_name_arr[0];
                            internalPromotions.promos[internalPromotions.count].id = promo_name.replace(/ /g,"_");
                            internalPromotions.promos[internalPromotions.count].position = "homepage_carousel_RTP";
                            internalPromotions.promos[internalPromotions.count].creative = "";
                            internalPromotions.count+=1;
                        }
                    }
                }
            }else if(element.id == "coupons"){
                if($(element).hasClass("related_content_coupon")){
                    coupon_current_position = "related_content_coupon";
                }

                element_img = element.getElementsByTagName("img");
                if(element_img.length > 0){
                    for(var i=0;i<element_img.length;i++){
                        if($(element_img[i]).hasClass("coupon_tracking")){
                            internalPromotions.promos[internalPromotions.count]={};
                            promo_name = element_img[i].alt;
                            promo_name_arr = promo_name.split('-');
                            internalPromotions.promos[internalPromotions.count].name = promo_name_arr[0];
                            internalPromotions.promos[internalPromotions.count].id = promo_name.replace(/ /g,"_");
                            internalPromotions.promos[internalPromotions.count].position = coupon_current_position;
                            internalPromotions.promos[internalPromotions.count].creative = "";
                            internalPromotions.count+=1;
                        }
                    }
                }
            }else if($(element).hasClass("contest_tracking")){

                internalPromotions.promos[internalPromotions.count]={};
                element_a = element.getElementsByTagName("a");
                if(element_a.length > 0){
                    for(var i=0;i<element_a.length;i++){
                        promo_name = element_a[i].title;
                        promo_name_arr = promo_name.split('-');
                        internalPromotions.promos[internalPromotions.count].name = promo_name_arr[0];
                        internalPromotions.promos[internalPromotions.count].id = promo_name.replace(/ /g,"_");
                    }
                }
                internalPromotions.promos[internalPromotions.count].position = "contest";
                internalPromotions.promos[internalPromotions.count].creative = "";
                internalPromotions.count+=1;
            }else if($(element).hasClass("related_featured_article_tracking")){
                element_hidden = element.getElementsByTagName("input");
                if(element_hidden.length > 0){
                    for(var i=0;i<element_hidden.length;i++){
                        if(element_hidden[i].name == "hidden_brand_title"){
                            internalPromotions.promos[internalPromotions.count]={};
                            promo_name = element_hidden[i].value;
                            promo_name_arr = promo_name.split('-');
                            internalPromotions.promos[internalPromotions.count].name = promo_name_arr[0];
                            internalPromotions.promos[internalPromotions.count].id = promo_name.replace(/ /g,"_");
                            internalPromotions.promos[internalPromotions.count].position = "related_featured_article";
                            internalPromotions.promos[internalPromotions.count].creative = "";
                            internalPromotions.count+=1;
                        }
                    }
                }
            }else if($(element).hasClass("related_featured_recipe_tracking")){
                element_hidden = element.getElementsByTagName("input");
                if(element_hidden.length > 0){
                    for(var i=0;i<element_hidden.length;i++){
                        if(element_hidden[i].name == "hidden_brand_title"){
                            internalPromotions.promos[internalPromotions.count]={};
                            promo_name = element_hidden[i].value;
                            promo_name_arr = promo_name.split('-');
                            internalPromotions.promos[internalPromotions.count].name = promo_name_arr[0];
                            internalPromotions.promos[internalPromotions.count].id = promo_name.replace(/ /g,"_");
                            if(internalPromotions.promos[internalPromotions.count].name == ""){
                                internalPromotions.promos[internalPromotions.count].name = "Tastyrewards";
                                internalPromotions.promos[internalPromotions.count].id = "Tastyrewards"+internalPromotions.promos[internalPromotions.count].id;
                            }
                            internalPromotions.promos[internalPromotions.count].position = "related_featured_recipe";
                            internalPromotions.promos[internalPromotions.count].creative = "";
                            internalPromotions.count+=1;
                        }
                    }
                }
            }else if($(element).hasClass("related_articles_tracking")){
                element_hidden = element.getElementsByTagName("input");
                if(element_hidden.length > 0){
                    for(var i=0;i<element_hidden.length;i++){
                        if(element_hidden[i].name == "hidden_brand_title"){
                            internalPromotions.promos[internalPromotions.count]={};
                            promo_name = element_hidden[i].value;
                            promo_name_arr = promo_name.split('-');
                            internalPromotions.promos[internalPromotions.count].name = promo_name_arr[0];
                            internalPromotions.promos[internalPromotions.count].id = promo_name.replace(/ /g,"_");
                            internalPromotions.promos[internalPromotions.count].position = "related_article";
                            internalPromotions.promos[internalPromotions.count].creative = "";
                            internalPromotions.count+=1;
                        }
                    }
                }

            }else if($(element).hasClass("related_recipes_tracking")){
                element_hidden = element.getElementsByTagName("input");
                if(element_hidden.length > 0){
                    for(var i=0;i<element_hidden.length;i++){
                        if(element_hidden[i].name == "hidden_brand_title"){
                            internalPromotions.promos[internalPromotions.count]={};
                            promo_name = element_hidden[i].value;
                            promo_name_arr = promo_name.split('-');
                            internalPromotions.promos[internalPromotions.count].name = promo_name_arr[0];
                            internalPromotions.promos[internalPromotions.count].id = promo_name.replace(/ /g,"_");
                            if(internalPromotions.promos[internalPromotions.count].name == ""){
                                internalPromotions.promos[internalPromotions.count].name = "Tastyrewards";
                                internalPromotions.promos[internalPromotions.count].id = "Tastyrewards"+internalPromotions.promos[internalPromotions.count].id;
                            }
                            internalPromotions.promos[internalPromotions.count].position = "related_recipe";
                            internalPromotions.promos[internalPromotions.count].creative = "";
                            internalPromotions.count+=1;
                        }
                    }
                }
            }
        });

        if (internalPromotions.count > 0) {
            window.dataLayer = window.dataLayer || [];
            window.dataLayer.push({
                'event': 'promotionView',
                'ecommerce': {
                    'promoView': {
                        'promotions': internalPromotions.promos
                    }
                }
            });
        }

        console.log('promotionView is sent');
        console.log(internalPromotions);
    }




$('.ajax_loader_poll').fadeOut(3000);

/* poll animated ini*/
if ($('.field--name-field-choice-image').length > 0) {
    console.log("Exist animated");
    
    $(".field--name-field-choice-image").append('<div class="poll_spot_left"></div><div class="poll_spot_right"></div>');
    
    $(".field--name-field-choice-image .img-responsive").css({'width' : '100%','height' : '100%'});
    
}

/*$(".field--name-field-choice-image").on('click', function () {
        alert('Click');
});*/

$(".poll_spot_left").on('click', function () {
        SubmitDynamicPoll(0);
});

$(".poll_spot_right").on('click', function () {
        SubmitDynamicPoll(1);
});

function SubmitDynamicPoll(choicenumber){
    formchoice = $(".poll-view-form input[name='choice']").eq(choicenumber);
    formchoice.prop("checked", true).trigger("click");
    $("#edit-vote").mousedown();
    $('.field--name-field-choice-image').fadeOut(3000);
}


/* poll animated end*/

})(jQuery);