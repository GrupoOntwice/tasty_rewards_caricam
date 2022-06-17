/*!
 * bpopup_newsletter v1.0.0
 * Create a bpopup for newsletter subcribe  or member sign up
 */


( function( window ) {console.log('nl_popup_added');

  function newsletterPopupClick(){
      var newsletterPopupClickData = {};
      if(jQuery('#nl_memeber_flag').val()== "yes"){
          newsletterPopupClickData.name = "member_nl_popup";
          newsletterPopupClickData.id = "member_nl_popup";
      }else{
          newsletterPopupClickData.name = "no_member_nl_popup";
          newsletterPopupClickData.id = "no_member_nl_popup";
      }

      newsletterPopupClickData.creative = "";
      newsletterPopupClickData.position = "homepage_newsletter_popup_RTP";

      window.dataLayer = window.dataLayer || [];
      window.dataLayer.push({
          'event': 'promotionClick',
          'ecommerce': {
              'promoClick': {
                  'promotions': [newsletterPopupClickData]
              }
          }
      });

      console.log('Newsletter Popup Click is sent');
      console.log(newsletterPopupClickData);
  }

  function popup_newsletter(){

    if(jQuery('#bpopup_newsletter_container').html() != '' && jQuery('#user_optin_status').val() == ""){

      // var random_id = Math.floor(Math.random()*2);
      //
      // var color_array = new Array();
      // color_array[0] = 'pink';
      // color_array[1] = 'blue';
      //
      // var lang = jQuery('html').attr('lang');
      //
      // jQuery('#nl_memebers').css("background-image","url('/themes/pepsi/img/nl_popup/Tasty_rewards_popup_"+color_array[random_id]+"_"+lang+".jpg')");
      // jQuery('#nl_memebers').css("background-size","cover");
      // jQuery('#nl_non_memebers').css("background-image","url('/themes/pepsi/img/nl_popup/Tasty-Rewards-Pop-up_sign-up_"+color_array[random_id]+"_"+lang+".jpg");
      // jQuery('#nl_non_memebers').css("background-size","cover");
      //
      if(jQuery('#nl_memeber_flag').val()== "yes"){
        jQuery('#nl_memebers').css('display','block');
        jQuery('#nl_non_memebers').css('display','none');
      }else if(jQuery('#nl_memeber_flag').val()== "no"){
        jQuery('#nl_memebers').css('display','none');
        jQuery('#nl_non_memebers').css('display','block');
      }else{

      }

      var x = document.documentElement.clientWidth/2; //browser viewport width
      var div_w = jQuery('#nl_memebers').width()/2 == 0 ? jQuery('#nl_non_memebers').width()/2 : jQuery('#nl_memebers').width()/2; //popup box width
      var popup_width = x-div_w;

      var y = document.documentElement.clientHeight/2; //browser viewport height
      var div_h = jQuery('#nl_memebers').height()/2 == 0 ? jQuery('#nl_non_memebers').height()/2 : jQuery('#nl_memebers').height()/2; //popup box height
      var popup_height = y-div_h;

      jQuery('#bpopup_newsletter_container').bPopup({
        modalColor: "#444",
        modalClose: true,
        opacity: 0.7,
        follow: [true, true],
        positionStyle: 'fixed',
        position: [popup_width,popup_height],
        closeClass:'nl_close',
      });

      // GTM impression tracking
        var newsletterPopupImpression = {};
        if(jQuery('#nl_memeber_flag').val()== "yes"){
            newsletterPopupImpression.name = "member_nl_popup";
            newsletterPopupImpression.id = "member_nl_popup";
        }else{
            newsletterPopupImpression.name = "no_member_nl_popup";
            newsletterPopupImpression.id = "no_member_nl_popup";
        }
        newsletterPopupImpression.creative = "";
        newsletterPopupImpression.position = "homepage_newsletter_popup_RTP";

        window.dataLayer = window.dataLayer || [];
        window.dataLayer.push({
            'event': 'promotionView',
            'ecommerce': {
                'promoView': {
                    'promotions': [newsletterPopupImpression]
                }
            }
        });

        console.log('Newsletter Popup Impression is sent');
        console.log(newsletterPopupImpression);


        // GTM click tracking
        jQuery('#nl_memebers_en_btn').click(function(){
            newsletterPopupClick();
            // window.location.href = 'https://www.tastyrewards.ca/en/my-account';
        });
        jQuery('#nl_non_memebers_en_btn').click(function(){
            newsletterPopupClick();
            // window.location.href = 'https://www.tastyrewards.ca/en/subscribe';
        });

        jQuery('#nl_memebers_fr_btn').click(function(){
            newsletterPopupClick();
            // window.location.href = 'https://www.tastyrewards.ca/fr/mon-compte';
        });
        jQuery('#nl_non_memebers_fr_btn').click(function(){
            newsletterPopupClick();
            // window.location.href = 'https://www.tastyrewards.ca/fr/subscribe';
        });


      jQuery.cookie('nl_popup', 'true', { expires: 3650, path: '/' });
    }


  }


  jQuery(document).ready(function(){

    if(jQuery.cookie('nl_popup') != 'true'){
      if(jQuery('#nl_memeber_flag').val()== "yes"){
        setTimeout(popup_newsletter, 2000);
      }else{
        setTimeout(popup_newsletter, 20000);
      }

    }

  });


})( window );

