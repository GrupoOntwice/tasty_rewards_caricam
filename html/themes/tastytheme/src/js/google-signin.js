
	var clicked=false;//Global Variable
	var is_iframe = false
	function ClickLogin(iframe_form)
	{
            
            if (iframe_form === undefined) {
                iframe_form = false
            }
	    clicked=true;
	    is_iframe = iframe_form;
	}

	function googleSignUp(){
		var gSignUp = document.querySelector('.modal__social-button .abcRioButtonContents');
		gSignUp.click();

	}
	function googleLogin(){
		var gSignIn = document.querySelector('.modal__social-button-sign-in .abcRioButtonContents');
		gSignIn.click();
	}
		

	function onSignIn(googleUser) {
	    // Useful data for your client-side scripts:
	    if (jQuery('body').hasClass('user-logged-in') || !clicked){
	    	return;
	    }
	    var profile = googleUser.getBasicProfile();
	    console.log(profile);
	    console.log("ID: " + profile.getId()); // Don't send this directly to your server!
	    console.log('Full Name: ' + profile.getName());
	    console.log('Given Name: ' + profile.getGivenName());
	    console.log('Family Name: ' + profile.getFamilyName());
	    console.log("Image URL: " + profile.getImageUrl());
	    console.log("Email: " + profile.getEmail());
	    var id_token = googleUser.getAuthResponse().id_token;
	    var user_data = {
	    	"firstname": profile.getGivenName(), 
	    	"lastname": profile.getFamilyName(), 
	    	"email": profile.getEmail(),
	    	"token": id_token,
	    	"account": "google",
	    }
	    // The ID token you need to pass to your backend:
	    // console.log("ID Token: " + id_token);
	    language = 'en';
	    ///pepsibam/fblogin
	    jQuery.ajax({
          url:"/pepsibam/google-login",
          type: "POST",
          data: user_data,
          success:function(data) {
          	  console.log(data);
          	  var result = JSON.parse(data);
              if (result.exist && !is_iframe){
                  window.location.href = result.route;
                  // location.reload();
              }
              else {
              	

                  // console.log('not exist');
                  jQuery('#register-form input#email').val(profile.getEmail())
                  jQuery('#register-form input#firstname').val(profile.getGivenName())
                  jQuery('#register-form input#lastname').val(profile.getFamilyName())

              }
          }
        });



  }

  function onLogin(googleUser) {
  		if (jQuery('body').hasClass('user-logged-in') || !clicked){
	    	return;
	    }
	    // Useful data for your client-side scripts:
	    var profile = googleUser.getBasicProfile();
	    var id_token = googleUser.getAuthResponse().id_token;
	    var user_data = {
	    	"firstname": profile.getGivenName(), 
	    	"lastname": profile.getFamilyName(), 
	    	"email": profile.getEmail(),
	    	"token": id_token,
	    	"account": "google",
	    }
	    // The ID token you need to pass to your backend:
	    console.log("ID Token: " + id_token);
	    // logg
	    ///pepsibam/fblogin
	    jQuery.ajax({
          url:"/pepsibam/google-login",
          type: "POST",
          data: user_data,
          success:function(data) {
          	  console.log(data);
          	  var result = JSON.parse(data);
              if (result.exist){
              	  if (jQuery('#contest').length){
                  	location.reload();
              	  } else {
                	  window.location.href = result.route;
              		}
                  // Don't redirect for contest page?
              }
              else {

              	 if (jQuery('#contest').length){
              	  	// For contest registration form 
              	  	  jQuery('#contest-email').val(profile.getEmail())
	                  jQuery('#contest-firstname').val(profile.getGivenName())
	                  jQuery('#contest-lastname').val(profile.getFamilyName())
              	  } else {

              		// console.log('Success ajax complete')
              		var close = document.getElementById('fancy_login_close_button');
              		var signupButton = document.getElementsByClassName("jsModalSignUp")[0];
              		close.click();
              		signupButton.click();

		
	              		// jQuery('#fancy_login_close_button').trigger('click');
	              		// jQuery(".jsModalSignUp").trigger("click");

	                  jQuery('#register-form input#email').val(profile.getEmail())
	                  jQuery('#register-form input#firstname').val(profile.getGivenName())
	                  jQuery('#register-form input#lastname').val(profile.getFamilyName())
              	  }

              }
          }

     

        });

        


  }

  		
       
