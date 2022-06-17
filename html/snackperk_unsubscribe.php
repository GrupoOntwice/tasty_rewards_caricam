<?php 
// $array_url = explode("/", $_SERVER['REQUEST_URI']);

$uri = "";
if (is_safe_uri($_SERVER['REQUEST_URI'])){
  $uri = $_SERVER['REQUEST_URI'];
}

$language_code = get_language_from_uri($uri);
$sourceID = get_source_from_uri($uri);

function get_language_from_uri($uri){
  $allowed_values = ["en-ca", "fr-ca", "en-us", "sp-us"];
  $lang = explode("/", $uri)[1];
  if (!in_array($lang, $allowed_values))
    return "";
  return $lang;

}


function is_safe_uri($uri){
  // /en-ca/iframe2/subscribe/lays
  if (strpos($uri, "/iframe2/subscribe/") !== false && get_source_from_uri($uri) !== null){
    return true;
  }
  return false;
}

function get_source_from_uri($uri){
   

   $url_source = explode("/", $uri)[4];
   if (!is_valid_source($url_source))
      return null;

    return $url_source;
}

function is_valid_source($source){
  $allowed_values = [
     "lays","tostitos","doritos","cheetos","ruffles","smartfood","sunchips","stacysnacks","fritolayvarietypacks","fritolaysnackperks","crispyminis","quakeroats","missvickies","stacyssnacks","sunchips","twistos","fritolaymajestic","fritolay","offtheeatenpathsnacks","baresnacks","collationsofftheeatenpath","collationsbare","fritos","simplyfritolay",
   ];
   if (!in_array($source, $allowed_values))
      return false;

    return true;
}

?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#de1f26">
    <meta name="google-site-verification" content="U400jgqNbKuaByDdbugbyQoLdkAwH6rJJB06E0q9b2g" />

    <title>Register - SnackPerks</title>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
    <link href='https://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700' rel='stylesheet' type='text/css'>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.1/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-social/5.0.0/bootstrap-social.min.css">
    <link rel="pingback" href="https://www.fritolaysnackperks.com/xmlrpc.php" />
    <link rel="shortcut icon" href="https://www.fritolaysnackperks.com/wp-content/themes/bootstrap-on-wordpress-theme-master/images/nav/favicon.ico"/>
    <link rel="shortcut icon" href="https://www.fritolaysnackperks.com/wp-content/themes/bootstrap-on-wordpress-theme-master/images/nav/favicon.ico" type="image/x-icon" />
    <link rel="icon" href="https://www.fritolaysnackperks.com/wp-content/themes/bootstrap-on-wordpress-theme-master/images/nav/favicon.ico" type="image/x-icon" />
    <link rel="icon" href="https://www.fritolaysnackperks.com/wp-content/themes/bootstrap-on-wordpress-theme-master/images/nav/icon.png" />
    <link rel="apple-touch-icon" href="https://www.fritolaysnackperks.com/wp-content/themes/bootstrap-on-wordpress-theme-master/images/nav/icon.png" />
    <link rel="stylesheet" href="https://www.fritolaysnackperks.com/wp-content/themes/bootstrap-on-wordpress-theme-master/css/main.min.css">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css" rel="stylesheet" />

    <!--[if IE]>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/html5shiv/3.7.3/html5shiv.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

      <script src="https://use.typekit.net/zoo0lob.js"></script>
<script>try{Typekit.load({ async: true });}catch(e){}</script>

				<script type="text/javascript">
					var bhittani_plugin_kksr_js = {"nonce":"4ecf3355aa","grs":false,"ajaxurl":"https:\/\/www.fritolaysnackperks.com\/wp-admin\/admin-ajax.php","func":"kksr_ajax","msg":"","fuelspeed":400,"thankyou":"","error_msg":"","tooltip":"0","tooltips":[{"tip":"","color":"#ffffff"},{"tip":"","color":"#ffffff"},{"tip":"","color":"#ffffff"},{"tip":"","color":"#ffffff"},{"tip":"","color":"#ffffff"}]};
				</script>
				
<!-- This site is optimized with the Yoast SEO plugin v10.1.3 - https://yoast.com/wordpress/plugins/seo/ -->
<meta name="description" content="Sign up for Snack Perks and get the inside scoop on the snacks you love--coupons, recipes, news, exclusives and more."/>
<link rel="canonical" href="https://www.fritolaysnackperks.com/register/" />
<meta property="og:locale" content="en_US" />
<meta property="og:type" content="article" />
<meta property="og:title" content="Register - SnackPerks" />
<meta property="og:description" content="Sign up for Snack Perks and get the inside scoop on the snacks you love--coupons, recipes, news, exclusives and more." />
<meta property="og:url" content="https://www.fritolaysnackperks.com/register/" />
<meta property="og:site_name" content="SnackPerks" />
<meta property="article:publisher" content="https://www.facebook.com/FritoLay/?fref=ts" />
<meta property="article:tag" content="register" />
<meta name="twitter:card" content="summary" />
<meta name="twitter:description" content="Sign up for Snack Perks and get the inside scoop on the snacks you love--coupons, recipes, news, exclusives and more." />
<meta name="twitter:title" content="Register - SnackPerks" />
<meta name="twitter:site" content="@Fritolay" />
<meta name="twitter:creator" content="@Fritolay" />
<!-- / Yoast SEO plugin. -->

<link rel='dns-prefetch' href='//s.w.org' />
		<script type="text/javascript">
			window._wpemojiSettings = {"baseUrl":"https:\/\/s.w.org\/images\/core\/emoji\/11.2.0\/72x72\/","ext":".png","svgUrl":"https:\/\/s.w.org\/images\/core\/emoji\/11.2.0\/svg\/","svgExt":".svg","source":{"concatemoji":"https:\/\/www.fritolaysnackperks.com\/wp-includes\/js\/wp-emoji-release.min.js?ver=5.1.1"}};
			!function(a,b,c){function d(a,b){var c=String.fromCharCode;l.clearRect(0,0,k.width,k.height),l.fillText(c.apply(this,a),0,0);var d=k.toDataURL();l.clearRect(0,0,k.width,k.height),l.fillText(c.apply(this,b),0,0);var e=k.toDataURL();return d===e}function e(a){var b;if(!l||!l.fillText)return!1;switch(l.textBaseline="top",l.font="600 32px Arial",a){case"flag":return!(b=d([55356,56826,55356,56819],[55356,56826,8203,55356,56819]))&&(b=d([55356,57332,56128,56423,56128,56418,56128,56421,56128,56430,56128,56423,56128,56447],[55356,57332,8203,56128,56423,8203,56128,56418,8203,56128,56421,8203,56128,56430,8203,56128,56423,8203,56128,56447]),!b);case"emoji":return b=d([55358,56760,9792,65039],[55358,56760,8203,9792,65039]),!b}return!1}function f(a){var c=b.createElement("script");c.src=a,c.defer=c.type="text/javascript",b.getElementsByTagName("head")[0].appendChild(c)}var g,h,i,j,k=b.createElement("canvas"),l=k.getContext&&k.getContext("2d");for(j=Array("flag","emoji"),c.supports={everything:!0,everythingExceptFlag:!0},i=0;i<j.length;i++)c.supports[j[i]]=e(j[i]),c.supports.everything=c.supports.everything&&c.supports[j[i]],"flag"!==j[i]&&(c.supports.everythingExceptFlag=c.supports.everythingExceptFlag&&c.supports[j[i]]);c.supports.everythingExceptFlag=c.supports.everythingExceptFlag&&!c.supports.flag,c.DOMReady=!1,c.readyCallback=function(){c.DOMReady=!0},c.supports.everything||(h=function(){c.readyCallback()},b.addEventListener?(b.addEventListener("DOMContentLoaded",h,!1),a.addEventListener("load",h,!1)):(a.attachEvent("onload",h),b.attachEvent("onreadystatechange",function(){"complete"===b.readyState&&c.readyCallback()})),g=c.source||{},g.concatemoji?f(g.concatemoji):g.wpemoji&&g.twemoji&&(f(g.twemoji),f(g.wpemoji)))}(window,document,window._wpemojiSettings);
		</script>
		<style type="text/css">
img.wp-smiley,
img.emoji {
	display: inline !important;
	border: none !important;
	box-shadow: none !important;
	height: 1em !important;
	width: 1em !important;
	margin: 0 .07em !important;
	vertical-align: -0.1em !important;
	background: none !important;
	padding: 0 !important;
}
</style>
	<link rel='stylesheet' id='wp-block-library-css'  href='https://www.fritolaysnackperks.com/wp-includes/css/dist/block-library/style.min.css?ver=5.1.1' type='text/css' media='all' />
<link rel='stylesheet' id='bhittani_plugin_kksr-css'  href='https://www.fritolaysnackperks.com/wp-content/plugins/kk-star-ratings/css.css?ver=2.6.3' type='text/css' media='all' />
<script type='text/javascript' src='https://www.fritolaysnackperks.com/wp-includes/js/jquery/jquery.js?ver=1.12.4'></script>
<script type='text/javascript' src='https://www.fritolaysnackperks.com/wp-includes/js/jquery/jquery-migrate.min.js?ver=1.4.1'></script>
<script type='text/javascript' src='https://www.fritolaysnackperks.com/wp-content/plugins/kk-star-ratings/js.min.js?ver=2.6.3'></script>
<link rel='https://api.w.org/' href='https://www.fritolaysnackperks.com/wp-json/' />
<link rel="EditURI" type="application/rsd+xml" title="RSD" href="https://www.fritolaysnackperks.com/xmlrpc.php?rsd" />
<link rel="wlwmanifest" type="application/wlwmanifest+xml" href="https://www.fritolaysnackperks.com/wp-includes/wlwmanifest.xml" /> 

<link rel='shortlink' href='https://www.fritolaysnackperks.com/?p=8' />
<link rel="alternate" type="application/json+oembed" href="https://www.fritolaysnackperks.com/wp-json/oembed/1.0/embed?url=https%3A%2F%2Fwww.fritolaysnackperks.com%2Fregister%2F" />
<link rel="alternate" type="text/xml+oembed" href="https://www.fritolaysnackperks.com/wp-json/oembed/1.0/embed?url=https%3A%2F%2Fwww.fritolaysnackperks.com%2Fregister%2F&#038;format=xml" />
<style>.kk-star-ratings { width:120px; }.kk-star-ratings .kksr-stars a { width:24px; }.kk-star-ratings .kksr-stars, .kk-star-ratings .kksr-stars .kksr-fuel, .kk-star-ratings .kksr-stars a { height:24px; }.kk-star-ratings .kksr-star.gray { background-image: url(https://www.fritolaysnackperks.com/wp-content/plugins/kk-star-ratings/gray.png); }.kk-star-ratings .kksr-star.yellow { background-image: url(https://www.fritolaysnackperks.com/wp-content/plugins/kk-star-ratings/yellow.png); }.kk-star-ratings .kksr-star.orange { background-image: url(https://www.fritolaysnackperks.com/wp-content/plugins/kk-star-ratings/orange.png); }</style><meta name="TagPages" content="1.64"/>
<script>
function NoNumbers(evt) {
  if( !evt ) evt = window.event;
  var charCode = (evt.charCode) ? evt.charCode : ((evt.keyCode) ? evt.keyCode :
        ((evt.which) ? evt.which : 0));
    if (charCode == 8 || charCode == 46 || charCode == 37 || charCode == 39) {
        return true;
    } else if (charCode > 31 && (charCode < 65 || charCode > 90) && (charCode < 97 || charCode > 122)) {
        return false;
    }
    return true;
}
function OnlyNumbers(evt) {
  if( !evt ) evt = window.event;
  var charCode = (evt.which) ? evt.which : event.keyCode;
  if (charCode > 31 && (charCode < 48 || charCode > 57)) {
    return false;
  }
  return true;
}
function birthDayValidate(input) {
    if (input.value <= 0) input.value = '01';
    if (input.value > 31) input.value = 31;
}
function birthMonthValidate(input) {
    if (input.value <= 0) input.value = '01';
    if (input.value > 12) input.value = 12;
}
function birthYearValidate(input) {
  var currentYear = new Date().getFullYear();
  if (input.value < 1900) input.value = 1990;
  if (input.value > currentYear) input.value = currentYear;
}
</script>
<!-- Vulnerability Frame Busting Start -->
<style>
html { visibility: visible;}
</style>
<script>
document.documentElement.style.visibility = 'visible';

</script>
  </head>
<body data-rsssl=1 class="page-template page-template-page-register page-template-page-register-php page page-id-8 register">
  <input id="langcode" name="prodId" type="hidden" value="<?php print $language_code; ?>">
      <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
            <!-- <script type='text/javascript' src='/iframe/js/unsubscribe.js'></script> -->
  <!-- Google Tag Manager -->
  <script>
    (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
    (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
    m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
    })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

    ga('create', 'UA-11401921-85', 'auto');
    ga('send', 'pageview');

  </script>
  <!-- End Google Tag Manager -->
    
    <main>
<div class="container">
    <div class="centerBlock">
        <div class="row reg-wrapper">
            <div class="col-xs-12 reg-form-wrapper">
                <p class="reg-form-tag">UNSUBSCRIBE - REMOVAL REQUEST</p>
                <p class="reg-form-sub"><br/>If you no longer wish to receive e-mail updates from Tasty Rewards, please enter your email address below and click on the "Submit" button</p>
                
                <div class="row form-inline">
                    <div id="simple-msg"></div>
                <form name="snackperksreg" id="snackperksreg"  action="https://www.fritolaysnackperks.com/wp-content/themes/bootstrap-on-wordpress-theme-master/forms/SnackPerksRegSubmit.php" method="POST">
                  <input type="hidden" name='iframe-form' id='iframe-form' value="1"> 
                  <input type="hidden" name='csrfToken' id='csrfToken' value="5caf4d39a04e35caf4d39a04e65caf4d39a04e85caf4d39a04e95caf4d39a04eb5caf4d39a04ec">
                           <div id="simple-error"></div>                    <fieldset>
                        <div class="form-group hidden-form">
                            <input type="text" id="utmString" name="utmString" value="" hidden>
                            <input type="text" id="utmSource" name="utmSource" value="" hidden>
                            <input type="text" id="utmMedium" name="utmMedium" value="" hidden>
                            <input type="text" id="utmContent" name="utmContent" value="" hidden>
                            <input type="text" id="utmCampaign" name="utmCampaign" value="" hidden>
                        </div>
                        <div class="form-group">
                    <input type="email" id="email" name="email" placeholder="EMAIL" value=""  pattern="[a-zA-Z0-9!#$%&amp;'*+\/=?^_`{|}~.-]+@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)*" required/>
                        </div>
                        
                        <div class="form-group">
            <input class="reg-form-submit btnregister" type="button" id="simple-post" value="SUBMIT" />
                        </div>
                    </fieldset>
        </form>
                </div>
                <!-- <div class="reg-form-rules">
                <p>By clicking sign me up, I agree to receive periodic info & offers from Frito-Lay. <p><a href="http://www.fritolay.com/legal/privacy-policy.htm" target="_blank">Privacy Policy</a> Contact Us at (844) 676-2257</p><p></p>
                </div> -->
            </div>
        </div>
        <div id="mysavingsmediatracker"></div>
        <div id="reg-btn-tracker"></div>
        <div class="row snackperksreg-confirm hidden" >
          <div class="col-xs-12">
            <h1>You’ve successfully been unsubscribed.</h1>
          
          </div>
        </div>
        <div class="row snackperksreg-promo-confirm" style="display:none;">
          <div class="col-xs-12">
            <h1>You are officially signed up for SNACKPERKS!</h1>
            <p>Soon you’ll be receiving e-mails with exclusive Frito-Lay updates. Check your inbox and enjoy the snack life.</p>
            <p><a href="https://www.savingstar.com/brands/fritolay?utm_source=FritoLay&utm_medium=SSRegistrationConfirmationPage&utm_campaign=Wave2Jul16" target="_blank">REDEEM YOUR FIRST PERK NOW!</a></p>
          </div>
        </div>
    </div>
</div>

</main>
  
      <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js "></script>
      <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
      <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js"></script>
      <!-- Global site tag (gtag.js) - Google Ads: 798198817 -->
<script async src="https://www.googletagmanager.com/gtag/js?id=AW-798198817"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'AW-798198817');
</script>
      <!-- Facebook Pixel Code -->
<script>
  !function(f,b,e,v,n,t,s)
  {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
  n.callMethod.apply(n,arguments):n.queue.push(arguments)};
  if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
  n.queue=[];t=b.createElement(e);t.async=!0;
  t.src=v;s=b.getElementsByTagName(e)[0];
  s.parentNode.insertBefore(t,s)}(window, document,'script',
  'https://connect.facebook.net/en_US/fbevents.js');
  fbq('init', '250547122293963');
  fbq('track', 'PageView');
</script>
<noscript><img height="1" width="1" style="display:none" 
  src="https://www.facebook.com/tr?id=250547122293963&ev=PageView&noscript=1" 
/></noscript>
<!-- End Facebook Pixel Code -->
      <script>
          $(document).on('click',function(){
              $('.collapse').collapse('hide');
          })
      </script>
      <script>
          jQuery(function ($) {
              function showSearch() {
                      $( ".navbar-search-icon-wrapper" ).click(function() {
                          $(".navbar-search-wrapper").addClass( "navbar-mobile-search" );
                      });
                      $( ".navbar-close-search" ).click(function() {
                          $(".navbar-search-wrapper").removeClass( "navbar-mobile-search" );
                      });
              }
              function validateForm() {
                  var x = document.forms["myForm"]["email-home"].value;
                  if (x == null || x == "") {
                      document.getElementById("simple-error").innerHTML = '<p>Email Required</p>';
                      return false;
                  }
              }
              $(document).ready(function () {
                  showSearch();
              });
              $(window).on('resize', function () {
                  showSearch();
              });
          });
      </script>
          <script>
  fbq('track', 'Lead');
</script>
<!-- Event snippet for Email Submissions conversion page
In your html page, add the snippet and call gtag_report_conversion when someone clicks on the chosen link or button. -->
<script>
function gtag_report_conversion(url) {
  var callback = function () {
    if (typeof(url) != 'undefined') {
      window.location = url;
    }
  };
  gtag('event', 'conversion', {
      'send_to': 'AW-798198817/zZSYCKfsj40BEKGYzvwC',
      'event_callback': callback
  });
  return false;
}
</script>
    <script>
        jQuery(function ($) {

function getAge(birthDateString) {
    var today = new Date();
    var birthDate = new Date(birthDateString);
    var age = today.getFullYear() - birthDate.getFullYear();
    var m = today.getMonth() - birthDate.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
        age--;
    }
    return age;
}
function isValidEmailAddress(emailAddress) {
    var pattern = new RegExp(/^(("[\w-\s]+")|([\w-]+(?:\.[\w-]+)*)|("[\w-\s]+")([\w-]+(?:\.[\w-]+)*))(@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$)|(@\[?((25[0-5]\.|2[0-4][0-9]\.|1[0-9]{2}\.|[0-9]{1,2}\.))((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\.){2}(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\]?$)/i);
    return pattern.test(emailAddress);
}

function formValidate(formToValidate) {
  var error = 0;
  $(formToValidate + ' input, ' + formToValidate +  'select').each(function(){
    if( !$(this).val() && $(this).attr('name') != 'utmString' && $(this).attr('name') != 'utmSource' && $(this).attr('name') != 'utmMedium' && $(this).attr('name') != 'utmContent' && $(this).attr('name') != 'utmCampaign') {
      $(this).addClass('simple-error-highlight');
      error = 1;
    }
    else if($(this).attr('name') == 'email') {
      var email = $("#email").val();
      if(!isValidEmailAddress(email)) {
        error = 2;
        $(this).addClass('simple-error-highlight');
      }
    }
    else {
      $(this).removeClass('simple-error-highlight');
    }
  });
  return error;
}

$(document).ready(function () {



  $("#simple-post").click(function () {
    gtag_report_conversion();
    $("#simple-error").html('<p></p>');
    $("#reg-btn-tracker").html('<img height="1" width="1" style="border-style:none;" alt="" src="//insight.adsrvr.org/track/conv/?adv=pp505sw&ct=0:9fq9r83&fmt=3"/>');
    var bdday = $("#bdday").val();
    var bdmonth = $("#bdmonth").val();
    var bdyear = $("#bdyear").val();
    ga(function() {
      var trackerName = ga.getByName('t0');
      ga(trackerName + '.send', 'event', 'Submit', 'Click', 'SnackPerksSubmit', '0');
    });

    var formToValidate = '#snackperksreg';

    if (formValidate(formToValidate) == 1 ) {
      $("#simple-error").html('<p>All Fields Required</p>');
    }
    else if (formValidate(formToValidate) == 2 ) {
      $("#simple-error").html('<p>Please enter a valid email address.</p>');
    }
    else if(getAge(bdday+"/"+bdmonth+"/"+bdyear) <= 13) {
      $("#simple-error").html('<p>You must be 13 years old to sign up.</p>');
    }
    else if(getAge(bdday+"/"+bdmonth+"/"+bdyear) >= 150) {
      $("#simple-error").html('<p>Please enter valid birthday.</p>');
    }
    else {
                        $("#snackperksreg").submit(function (e) {
                            $("#simple-error").hide();
                            $("#simple-msg").html('<p>Unsubscribing...</p>');
                            var promo = $("#utmString").val();
                            var postData = $(this).serializeArray();
                            var formURL = $(this).attr("action");

                            language = $("#langcode").val();
                             $.ajax({
                                url:"/" + language + "/pepsi/unsubscribe/ajaxaction",
                                type: "POST",
                                data:  $('#snackperksreg').serialize(),
                                success:function(data) {
                                  var response = JSON.parse(data);
                                    if (response.status){
                                      console.log('success')
                                        // window.location.href = data.route;
                                        $(".snackperksreg-confirm").removeClass("hidden");
                                        $(".reg-form-wrapper").addClass("hidden");

                                    }
                                    else {
                                      console.log('NOT success')
                                        // $('.has-error').children('.help-block').html('');
                                        // $('.has-error').removeClass('has-error');
                                                          
                                        
                                    }
                                }
                              });
                            
                            e.preventDefault(); //STOP default action
                            //e.unbind();
                        });

                        $("#snackperksreg").submit(); //SUBMIT FORM

                    }
                });
            });
        });
    </script>
<script type='text/javascript' src='https://www.fritolaysnackperks.com/wp-includes/js/wp-embed.min.js?ver=5.1.1'></script>
  </body>
  </html>
