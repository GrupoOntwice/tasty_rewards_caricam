//SIGN UP

const toggleSignUp = document.querySelector('.jsModalSignUp') || document.querySelector('.sign-up-link')
const togglefooterSignUp = document.querySelector('.jsModalFooterSignUp')
const closeButton = document.querySelector('.jsModalSignUpClose');

/* Header Section */

if(toggleSignUp){
    // jQuery(document).ready(function () {

        jQuery(".jsModalFooterSignUp,.jsModalSignUp,.sign-up-link").click( function(event) {
        document.body.classList.add('modal--is-open-sign-up');
        if (! window.hasLoadedSignupForm){
            window.hasLoadedSignupForm = true;
            var source = window.popup_source || 'tastyrewards';
            var langcode = drupalSettings.path.pathPrefix; // en-ca/ or fr-ca/ or en-us/
            if (window.hideSignupForm){
                jQuery("body").removeClass('modal--is-open-sign-up');
            } else{
                jQuery("body").addClass("popup-dark");
            }
            //console.log("LANG");
            //console.log(langcode);

            jQuery('#okta-signup').show();

            jQuery("#email").val("");
            jQuery("#firstname").val("");
            jQuery("#lastname").val("");
            jQuery("#socialsource").val("");
            jQuery('#email').prop('readonly', false);
            jQuery("#register-form .modal__user-password").show();
            jQuery("#register-form .modal__container-or").show();
            //console.log("SIGNUP AJAX")
            jQuery.ajax({
                type: "GET",
                url: "/" + langcode + "subscribe/" + source,
                data: {},
                success: function(data){
                    window.hideSignupForm = false;
                    // jQuery(".js-registration-form").html()
                    jQuery(data).find(".wrapper-registration").appendTo(".js-registration-form");
                    jQuery(".modal__close-button.jsModalSignUpClose").click(function(){
                        jQuery("body").removeClass("modal--is-open-sign-up");
                        if (jQuery('#okta-contest-container').length > 0){
                            showOktaSignUpContest(); //Render again the widget in contest form
                        }                        
                    });

                    if (window.popupEmail){
                        jQuery("#email").val(window.popupEmail);
                    }
                    if (window.popupSocial){
                        if (window.popupFName){
                            jQuery("#firstname").val(window.popupFName);
                        }
                        if (window.popupLName){
                            jQuery("#lastname").val(window.popupLName);
                        }
                        //console.log(jQuery("#socialsource"));
                        jQuery("#socialsource").val(window.popupSocial);
                        jQuery("#password").val("");
                        
                        jQuery("#register-form .modal__social-button").hide();
                        jQuery("#register-form .modal__container-or").hide();
                        jQuery('#email').prop('readonly', true);
                        jQuery("#register-form .modal__user-password").hide();
                    }
                    else{
                        showOktaSignUp();
                    }


                    jQuery(document).on('click', '.sign_up', function() { 
                        jQuery(".toggle-password-signup").removeClass("hide-password");
                        jQuery("#password").attr("type", "password");
                        jQuery("#contest-password").attr("type", "password");
                        
                    });
                    
                    jQuery(".toggle-password-signup").click(function(){
                        if (jQuery(".toggle-password-signup").hasClass("hide-password")){
                            jQuery(".toggle-password-signup").removeClass("hide-password");
                            jQuery("#password").attr("type", "password");
                            jQuery("#contest-password").attr("type", "password");
                        }else{
                            jQuery(".toggle-password-signup").addClass("hide-password");
                            jQuery("#password").attr("type", "text");
                            jQuery("#contest-password").attr("type", "text");
                        }
                    });
                    document.getElementById('optin').checked = window.popup_optin || false;
                    if (jQuery('#optin3').length ){
                        document.getElementById('optin3').checked = window.popup_optin3 || false;
                    }

                }
            }).always(function(){
                    jQuery("body").removeClass('popup-dark');
            });
        }
        else{
            jQuery("#email").val("");
            jQuery("#firstname").val("");
            jQuery("#lastname").val("");
            jQuery("#socialsource").val("");
            jQuery('#email').prop('readonly', false);
            jQuery("#register-form .modal__user-password").show();
            jQuery("#register-form .modal__container-or").show();
            jQuery("#okta-signup").show();
            if (jQuery('#okta-contest-container').length > 0){
                showOktaSignUp();
            }
        }
        if (!window.fbPixel) return false;
        if ( isFunction(fbPixel)) {
            fbPixel('InitiateCheckout',{content_name: 'General Registration'});
        }
            event.stopPropagation();
        })
    
        jQuery(".jsModalSignIn").click( function(event) {
            showOktaSignIn();
            event.stopPropagation();
        })

}

if(togglefooterSignUp){
    togglefooterSignUp.addEventListener('click', function(event) {
        document.body.classList.add('modal--is-open-sign-up')
        if (!window.fbPixel) return false;
        if (isFunction(fbPixel)) {
            fbPixel('InitiateCheckout',{content_name: 'General Registration'});
        }
        
        event.stopPropagation();
    })

}

// if(closeButton){
//     closeButton.addEventListener('click', function(event) {
//         document.body.classList.remove('modal--is-open-sign-up')
//         event.stopPropagation();
//     })
// }


//SIGN IN

//~ const toggleSignIn = document.querySelector('.jsModalSignIn');
//~ const closeSignInButton = document.querySelector('.jsModalSignInClose')

//~ toggleSignIn.addEventListener('click', _ => {
//~ document.body.classList.add('modal--is-open-sign-in')
//~ _.stopPropagation();
//~ })

//~ closeSignInButton.addEventListener('click', _ => {
//~ document.body.classList.remove('modal--is-open-sign-in')
//~ _.stopPropagation();
//~ })

//propagation to prevent the click behind the black background

const modal = document.querySelector('.modal')
if(modal){
    modal.addEventListener('click', function(e){  e.stopPropagation() })
}
//console.log("VAR CONFIG");
//console.log(oktacnf);
const oktaSignIn = new OktaSignIn(oktacnf);
//console.log("username");
//console.log(username);
var browser=get_browser(); 
//console.log(browser.name);

    if( (browser.name == 'Safari' && browser.version == '14') ) {
        // Don't ask for session.
        //console.log("don't ask for session");
    }
    else{
        oktaSignIn.authClient.session.exists()
        .then(function (exists) {
            if (exists) {
                console.log("SESSION EXISTS")
                oktaSignIn.authClient.token.getWithoutPrompt(
                    {
                        responseType: "code", // or array of types
                        scopes: ['openid', 'email', 'profile', drupalSettings.okta.signin_scope]
                    }
                )
                    .then(function (res) {
                        var tokens = res.tokens;
                        console.log(tokens);
                        // Do something with tokens, such as
                        oktaSignIn.authClient.tokenManager.setTokens(tokens);
                        if (uid == 0 ) {
                            SSOLogin();
                        }
                        else {
                            //If OKTA session doesn't match with drupal session, then logout
                            //console.log("claim")
                            //console.log(tokens.idToken.claims.email)
                            //console.log(username)
                            //if (tokens.idToken.claims.email != username){
                              //  document.location.href = okta_LogoutRedirectUri;
                            //}
                        }

                    })
                    .catch(function (err) {
                        //console.log(err)
                        // handle OAuthError or AuthSdkError (AuthSdkError will be thrown if app is in OAuthCallback state)
                    });
            } else {
               console.log('NO SESSION');
                //if (uid > 0) {
                    //document.location.href = okta_LogoutRedirectUri;
                //}
            }
        });
    }


function showOktaSignIn() {
    //console.log("CALLING SIGN INNNNN")
    oktaSignIn.remove();
    oktaSignIn.showSignInToGetTokens({
        el: '#okta-login-container'
    }).then(function (tokens) {
        console.log(tokens);
        //console.log(tokens.accessToken.claims.accountId);
        //cheking if Exists the AccountID 
        if (tokens.accessToken.claims.accountId == undefined) {
            //console.log("GOOGLE SIGN IN , but new member");
            //Checking if it's coming from google.
            const _idToken = tokens.idToken;
            //console.log(_idToken);
            window.popupEmail = _idToken.claims.email;
            window.popupFName = _idToken.claims.field_firstname;
            window.popupLName = _idToken.claims.field_lastname;
            window.popupSocial = _idToken.claims.social;

            var _signupButton = document.getElementsByClassName("jsModalSignUp")[0];
            _signupButton.click();

            oktaSignIn.authClient.tokenManager.clear();
            oktaSignIn.authClient.revokeAccessToken(); // strongly recommended
            oktaSignIn.authClient.closeSession()
                .then(() => {
                    //console.log("session removed");
                })
                .catch(e => {
                    if (e.xhr && e.xhr.status === 429) {
                        // Too many requests
                    }
                })
        }
        else {
            oktaSignIn.authClient.tokenManager.setTokens(tokens);
            SSOLogin();
        }
    }).catch(function (err) {
        console.log("err");
        console.log(err);
    });
    window.setTimeout(signin_oktacustom, 500); // 3 seconds			
}

function showOktaSignUp() {
    //console.log("CALLING SIGNUP")
    oktaSignIn.remove();
    oktaSignIn.showSignInToGetTokens({
        el: '#okta-signup-container'
    }).then(function (tokens) {
        //console.log("GET TOKEN");
        //console.log(tokens.accessToken.claims.accountId);
        //cheking if Exists the AccountID 
        if (tokens.accessToken.claims.accountId == undefined) {
            //console.log("GOOGLE SIGN IN , but new member");
            //Checking if it's coming from google.
            const _idToken = tokens.idToken;
            //console.log(_idToken);

            /*
            window.popupEmail = _idToken.claims.email;
            window.popupFName = _idToken.claims.field_firstname;
            window.popupLName = _idToken.claims.field_lastname;
            window.popupSocial = _idToken.claims.social;

            var _signupButton = document.getElementsByClassName("jsModalSignUp")[0];
            _signupButton.click();
            */
            jQuery('#okta-signup').hide();
            jQuery("#email").val(_idToken.claims.email);
            jQuery("#firstname").val(_idToken.claims.field_firstname);
            jQuery("#lastname").val(_idToken.claims.field_lastname);
            jQuery("#socialsource").val(_idToken.claims.social);
            jQuery("#password").val("");
            jQuery('#email').prop('readonly', true);
            jQuery("#register-form .modal__user-password").hide();
            jQuery("#register-form .modal__container-or").hide();
            
            oktaSignIn.authClient.tokenManager.clear();
            oktaSignIn.authClient.revokeAccessToken(); // strongly recommended
            oktaSignIn.authClient.closeSession()
                .then(() => {
                    //console.log("session removed");
                })
                .catch(e => {
                    if (e.xhr && e.xhr.status === 429) {
                        // Too many requests
                    }
                })
        }
        else {
            oktaSignIn.authClient.tokenManager.setTokens(tokens);
            SSOLogin();
        }
    }).catch(function (err) {
        console.log(err);
    });
}

function showOktaSignUpContest() {
    //console.log("CALLING CONTEST")
    oktaSignIn.remove();
    oktaSignIn.showSignInToGetTokens({
        el: '#okta-contest-container'
    }).then(function (tokens) {
        //console.log("GET TOKEN");
        //console.log(tokens.accessToken.claims.accountId);
        //cheking if Exists the AccountID 
        if (tokens.accessToken.claims.accountId == undefined) {
            //console.log("GOOGLE SIGN IN , but new member");
            //Checking if it's coming from google.
            const _idToken = tokens.idToken;
            //console.log(_idToken);

            /*
            window.popupEmail = _idToken.claims.email;
            window.popupFName = _idToken.claims.field_firstname;
            window.popupLName = _idToken.claims.field_lastname;
            window.popupSocial = _idToken.claims.social;

            var _signupButton = document.getElementsByClassName("jsModalSignUp")[0];
            _signupButton.click();
            */
            jQuery('#okta-contest').hide();
            jQuery("#contest-email").val(_idToken.claims.email);
            jQuery("#contest-firstname").val(_idToken.claims.field_firstname);
            jQuery("#contest-lastname").val(_idToken.claims.field_lastname);
            jQuery("#contest-socialsource").val(_idToken.claims.social);
            jQuery('#contest-email').prop('readonly', true);
            jQuery(".contest-detail__user-password").hide();
            jQuery("#contest-password").val("");
            jQuery(".contest-detail__or").hide();
            
            oktaSignIn.authClient.tokenManager.clear();
            oktaSignIn.authClient.revokeAccessToken(); // strongly recommended
            oktaSignIn.authClient.closeSession()
                .then(() => {
                    //console.log("session removed");
                })
                .catch(e => {
                    if (e.xhr && e.xhr.status === 429) {
                        // Too many requests
                    }
                })
        }
        else {
            oktaSignIn.authClient.tokenManager.setTokens(tokens);
            SSOLogin();
        }
    }).catch(function (err) {
        //console.log(err);
    });
}


function SSOLogin() {
    langprefix =  !drupalSettings.path.pathPrefix?'en-ca/':drupalSettings.path.pathPrefix;
    
    var url = window.location.protocol + '//' + window.location.hostname + '/' + langprefix + 'sso/login';
    oktaSignIn.authClient.tokenManager.get('accessToken').then(function (accessToken) {
        oktaSignIn.authClient.tokenManager.get('idToken').then(function (idtoken) {
            //console.log("ACCESSTOKEN & IDTOKEN")
            //console.log(accessToken);
            //console.log(idtoken);
            if (accessToken && !oktaSignIn.authClient.tokenManager.hasExpired(accessToken)) {
                // Token is valid then call the long in in the backend
                //console.log("TOKEN NOT EXPIRED")
                var mydata = JSON.stringify({ "accessToken": accessToken.value, 'idtoken': idtoken.value })
                console.log(mydata)
                showSpinner();
                jQuery.ajax({
                    url: url,
                    type: 'POST',
                    data: mydata,
                    success: function (data) {
                        //console.log(data);
                        if (data.status) {
                            location.reload();
                        }
                        else{
                            hideSpinner();
                        }
                    },
                    error: function (error) {
                        hideSpinner();
                        //console.log(error);
                    }
                });
            } else {
                // Token has been removed due to expiration or error while renewing
                //console.log("Expired");
            }
        }).catch(function (err) {
            // handle OAuthError or AuthSdkError (AuthSdkError will be thrown if app is in OAuthCallback state)
            console.error(err);
        });

    }).catch(function (err) {
        // handle OAuthError or AuthSdkError (AuthSdkError will be thrown if app is in OAuthCallback state)
        console.error(err);
    });

}


jQuery(document).on('click', '.jslogoutca', function (event) {
    oktaSignIn.authClient.tokenManager.clear();
    oktaSignIn.authClient.signOut(
        {
            postLogoutRedirectUri: okta_LogoutRedirectUri
        }
    );
    event.stopPropagation();
    return false;
});


function signin_oktacustom() {
    var addforgot = '<div class="oktaforgotpassword"><a href="' + snack_url + '/forgotpassword/en-ca">Forgot Your Password?</a></div>';
    var addsignup = 'Don\'t have an account yet? <a id="jsoctasignup" href="#">Sign up</a>';

    if (jQuery('html').attr('lang') == 'fr') {
        addforgot = '<div class="oktaforgotpassword"><a href="' + snack_url + '/forgotpassword/fr-ca">Mot de passe oublié ?</a></div>';
        addsignup = 'Vous n’avez pas de compte ? <a id="jsoktasignup" href="#">S\'inscrire</a>';
    }

    if (jQuery('html').attr('lang') == 'en-us') {
        addforgot = '<div class="oktaforgotpassword"><a href="' + snack_url + '/forgotpassword/en-us">Forgot Your Password?</a></div>';
        addsignup = 'Vous n’avez pas de compte ? <a id="jsoktasignup" href="#">Sign up</a>';
    }

    if (jQuery('html').attr('lang') == 'es-us') {
        addforgot = '<div class="oktaforgotpassword"><a href="' + snack_url + '/forgotpassword/es-us">¿Olvidaste tu contraseña?</a></div>';
        addsignup = 'Vous n’avez pas de compte ? <a id="jsoktasignup" href="#">Inscribirse</a>';
    }

    jQuery('#okta-signin-username').attr('placeholder', jQuery("label[for='okta-signin-username']").text());
    jQuery('#okta-signin-password').attr('placeholder', jQuery("label[for='okta-signin-password']").text());
    jQuery('.o-form-input-name-remember').parent().css( "display", "flex" ); 
    jQuery('.o-form-input-name-remember').after(addforgot);
    jQuery('.auth-footer').html("");
};

if (jQuery('#okta-contest-container').length > 0){
    showOktaSignUpContest();
}

jQuery(document).on('click', '#fancy_login_close_button', function (event) {
    if (jQuery('#okta-contest-container').length > 0){
        window.setTimeout(showOktaSignUpContest, 500)
    }     
    event.stopPropagation();
    return false;
});

function showSpinner(){
    jQuery(".lds-ring").attr("style", "visibility: visible;");
}
function hideSpinner(){
    jQuery(".lds-ring").attr("style", "visibility: hidden;");
}
//console.log("MODAL");

function get_browser() {
    var ua=navigator.userAgent,tem,M=ua.match(/(opera|chrome|safari|firefox|msie|trident(?=\/))\/?\s*(\d+)/i) || []; 
    if(/trident/i.test(M[1])){
        tem=/\brv[ :]+(\d+)/g.exec(ua) || []; 
        return {name:'IE',version:(tem[1]||'')};
        }   
    if(M[1]==='Chrome'){
        tem=ua.match(/\bEdg\/(\d+)/)
        if(tem!=null)   {return {name:'Edge(Chromium)', version:tem[1]};}
        tem=ua.match(/\bOPR\/(\d+)/)
        if(tem!=null)   {return {name:'Opera', version:tem[1]};}
        }   
    M=M[2]? [M[1], M[2]]: [navigator.appName, navigator.appVersion, '-?'];
    if((tem=ua.match(/version\/(\d+)/i))!=null) {M.splice(1,1,tem[1]);}
    return {
      name: M[0],
      version: M[1]
    };
 }

 ;(function (window) {

    var browser,
        version,
        mobile,
        os,
        osversion,
        bit,
        ua = window.navigator.userAgent,
        platform = window.navigator.platform;

    if ( /MSIE/.test(ua) ) {
        
        browser = 'Internet Explorer';
        
        if ( /IEMobile/.test(ua) ) {
            mobile = 1;
        }
        
        version = /MSIE \d+[.]\d+/.exec(ua)[0].split(' ')[1];
        
    } else if ( /Chrome/.test(ua) ) {
        // Platform override for Chromebooks
        if ( /CrOS/.test(ua) ) {
            platform = 'CrOS';
        }

        browser = 'Chrome';
        version = /Chrome\/[\d\.]+/.exec(ua)[0].split('/')[1];
        
    } else if ( /Opera/.test(ua) ) {
        
        browser = 'Opera';
        
        if ( /mini/.test(ua) || /Mobile/.test(ua) ) {
            mobile = 1;
        }
        
    } else if ( /Android/.test(ua) ) {
        
        browser = 'Android Webkit Browser';
        mobile = 1;
        os = /Android\s[\.\d]+/.exec(ua)[0];
        
    } else if ( /Firefox/.test(ua) ) {
        
        browser = 'Firefox';
        
        if ( /Fennec/.test(ua) ) {
            mobile = 1;
        }
        version = /Firefox\/[\.\d]+/.exec(ua)[0].split('/')[1];
        
    } else if ( /Safari/.test(ua) ) {
        
        browser = 'Safari';
        
        if ( (/iPhone/.test(ua)) || (/iPad/.test(ua)) || (/iPod/.test(ua)) ) {
            os = 'iOS';
            mobile = 1;
        }
        
    }

    if ( !version ) {
        
         version = /Version\/[\.\d]+/.exec(ua);
         
         if (version) {
             version = version[0].split('/')[1];
         } else {
             version = /Opera\/[\.\d]+/.exec(ua)[0].split('/')[1];
         }
         
    }
    
    if ( platform === 'MacIntel' || platform === 'MacPPC' ) {
        os = 'Mac OS X';
        osversion = /10[\.\_\d]+/.exec(ua)[0];
        if ( /[\_]/.test(osversion) ) {
            osversion = osversion.split('_').join('.');
        }
    } else if ( platform === 'CrOS' ) {
        os = 'ChromeOS';
    } else if ( platform === 'Win32' || platform == 'Win64' ) {
        os = 'Windows';
        bit = platform.replace(/[^0-9]+/,'');
    } else if ( !os && /Android/.test(ua) ) {
        os = 'Android';
    } else if ( !os && /Linux/.test(platform) ) {
        os = 'Linux';
    } else if ( !os && /Windows/.test(ua) ) {
        os = 'Windows';
    }

    window.ui = {
        browser : browser,
        version : version,
        mobile : mobile,
        os : os,
        osversion : osversion,
        bit: bit
    };
}(this));
//console.log( "BROWSER INFO" );
//console.log( window.ui.browser );
//console.log( window.ui.version );
//console.log( window.ui.os );