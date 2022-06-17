function facebook_iframe(){
    // This is called with the results from from FB.getLoginStatus().
    var access_token;
    $(".fbconnect").prop("type", "button");
    
   fbappid = '302045100670924'; //drupalSettings.pepsibam.pepsilibrary.fbappid;

    function statusChangeCallback(response) {
  //    console.log('statusChangeCallback');
  //    console.log(response);
      if (response.authResponse) { 
          access_token = FB.getAuthResponse()['accessToken']; 
      }
      // The response object is returned with a status field that lets the
      // app know the current login status of the person.
      // Full docs on the response object can be found in the documentation
      // for FB.getLoginStatus().
      if (response.status === 'connected') {
        // Logged into your app and Facebook.
        fetchFBinfo();
      } else if (response.status === 'not_authorized') {
        // The person is logged into Facebook, but not your app.
        //document.getElementById('status').innerHTML = 'Please log ' +  'into this app.';
        console.log("please log into this");
      } else {
        // The person is not logged into Facebook, so we're not sure if
        // they are logged into this app or not.
        //document.getElementById('status').innerHTML = 'Please log ' + 'into Facebook.';
        console.log ("no logged");
      }
    }

    // This function is called when someone finishes with the Login
    // Button.  See the onlogin handler attached to it in the sample
    // code below.
    function checkLoginState() {
        //console.log("check login status");
      FB.getLoginStatus(function(response) {
        statusChangeCallback(response);
      });
    }

    window.fbAsyncInit = function() {
    FB.init({
      appId      : fbappid, 
      cookie     : true,  // enable cookies to allow the server to access 
                          // the session
      xfbml      : true,  // parse social plugins on this page
      version    : 'v2.5' // use graph api version 2.5
    });

    // Now that we've initialized the JavaScript SDK, we call 
    // FB.getLoginStatus().  This function gets the state of the
    // person visiting this page and can return one of three states to
    // the callback you provide.  They can be:
    //
    // 1. Logged into your app ('connected')
    // 2. Logged into Facebook, but not your app ('not_authorized')
    // 3. Not logged into Facebook and can't tell if they are logged into
    //    your app or not.
    //
    // These three cases are handled in the callback function.

    /*FB.getLoginStatus(function(response) {
       console.log("check Login FB Status");
      statusChangeCallback(response);
    });
  */
    };

    // Load the SDK asynchronously
    (function(d, s, id) {
      var js, fjs = d.getElementsByTagName(s)[0];
      if (d.getElementById(id)) return;
      js = d.createElement(s); js.id = id;
      js.src = "//connect.facebook.net/en_US/sdk.js";
      fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));

    // Here we run a very simple test of the Graph API after login is
    // successful.  See statusChangeCallback() for when this call is made.
    function fetchFBinfo() {
      //console.log('Welcome!  Fetching your information.... ');
      /*FB.api('/me?fields=id,first_name,last_name,email', function(response) {
        console.log('Successful login for: ' + response.name);
        console.log(response);
        setFBlogin(response);
        //document.getElementById('status').innerHTML ='Thanks for logging in, ' + response.name + '!';
      });
      */
      
      FB.api('/me?fields=id,first_name,last_name,email', 'get', { access_token : access_token },function(response) {
          //Handle Data Here it will arrive in a Json object in the response 
          //console.log('Successful login for: ' + response.name);
          //console.log(response);
          setFBlogin(response);
      } );

      
      
      
      
    }


  function fbLogin() {
      //console.log("call FB");
      FB.login(function(response) {
          //console.log("after call FB");
          //console.log(response);
        //if (response.session) {
        if (response.status === 'connected'){
          //user is logged in, reload page
          fetchFBinfo()
          //window.location.reload(true);
        } else {
          // user is not logged in
        }
      }, {scope:'public_profile,email'});
  }

    $( ".fbconnect" ).on( "click", function() {
      fbLogin();
    });

    // $( ".contest-detail__sign-up-fb" ).on( "click", function() {
    //   fbLogin();
    // });



    function setFBlogin(response) {
       var referer = $(location).attr('href'); 
       var fbLoginElem = document.getElementById('fancy_login_form_contents');
       var formData = { id:response.id, email:response.email, firstname:response.first_name, lastname:response.last_name, referer:referer }; //Array  
       $.ajax({
          url:"/pepsibam/fblogin",
          type: "POST",
          data: formData,
          success:function(data) {
              // if (data.status && !data.user_exist){
              if (data.status){
                  // window.location.href = data.route;
                  if (!jQuery("#contest").length){
                    // if (fbLoginElem.offsetParent === null){ 
                     
                    // } else {
                    //   var close = document.getElementById('fancy_login_close_button');
                    //   var signupButton = document.getElementsByClassName("jsModalSignUp")[0];
                    //   close.click();
                    //   signupButton.click();
                    // }
                      jQuery('#register-form input#email').val(formData.email)
                      jQuery('#register-form input#firstname').val(formData.firstname)
                      jQuery('#register-form input#lastname').val(formData.lastname)

                  } else {
                    // For contest page
                    jQuery('#contest-email').val(formData.email)
                      jQuery('#contest-firstname').val(formData.firstname)
                      jQuery('#contest-lastname').val(formData.lastname)

                  }
              }
              // if (data.status && data.user_exist){
              //     if (jQuery("#contest").length){
              //       window.location.reload();
              //     } else {
              //       window.location.href = data.route;
              //     }
              // }
          }
        });
      }

}