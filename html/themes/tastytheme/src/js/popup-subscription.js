(function ($, window) {
	var skip_delay = false;
	var delay = skip_delay? 0.5 : 15;
	$(document).ready(function() {
		var no_activityWatcher = 1;
		// var logged_in = (drupalSettings.user.uid === 0 || drupalSettings.user.uid === 1)? 0 : 1;		
		var logged_in = parseInt($(".user-logged-in").length);	
		if (!logged_in && activityWatcher()){
			showPopup();
		} else if (logged_in){
			showPopup();
		}
		// else {
		// 	activityWatcher();
		// }
		submitPopup();
		loadSubscriberPopup();
		doNotShowAgain();

	});

	function showPopup(){
		setTimeout(function(){
			if (goodToShow()) {
				// Add lazy load here. 
				LazyLoadPackshot();
				$("div.popup-main-wrapper").removeClass("hidden");
			}
			// wait for n seconds
		}, 1000* delay)
	}

	function LazyLoadPackshot(){
		if (window.packshot_loaded){
			return;
		}

		// if ($(".popup-existing-member").length > 0 ){
		// 	// for now only for anonymous user
		// 	return;
		// }

		if (window.screen_width > 768) {
			 var device = 'desktop';
		} else {
			 var device = 'mobile';
		}
		 var _source = $(".popup-wrapper-right-" + device).data('src');
		 var _class = $(".popup-wrapper-right-" + device).data('class');
		 $(".popup-wrapper-right-" + device).append(`<img class="${_class}" src="${_source}" />`);
		 window.packshot_loaded = 1;


	}

	function goodToShow(){
		// never show popup when on the / page 
		if (window.location.pathname == '/'){
			return 0;
		}
		// @TODO: also handle the case for logged in users
		var hideUntil = localStorage.getItem("hidePopupUntil");
		// var logged_in = (drupalSettings.user.uid === 0 || drupalSettings.user.uid === 1)? 0 : 1;	
		var logged_in = parseInt($(".user-logged-in").length);	
		if (drupalSettings.user.uid === 1){
			// Never show the popup for the Admin user. 
			return 0;
		}

		if (window.popup_contest === 1){
			// hide this popup while the Contest popup form is active
			return 0;
		}

		if (hideUntil !== null){
			hideUntil = parseInt(hideUntil);
		}
		var now = new Date();
		if (!logged_in && (hideUntil === null || hideUntil < now.getTime() ) ){
			// Add a condition that the user is anonymous
			return 1;
		}

		if (logged_in){
			var current_path = window.location.pathname;
			if (current_path.indexOf("/coupons") !== -1 
				|| (current_path.indexOf("/contest") !== -1 && current_path.indexOf("/signup") !== -1) 
				|| (current_path.indexOf("/contest") !== -1 && current_path.indexOf("thank") !== -1) 
				){
				return 0;
			}
			var hideUntil = localStorage.getItem("hidePopupUntil-" + drupalSettings.user.uid)
			if (hideUntil !== null){
				hideUntil = parseInt(hideUntil);
			}
			// Check if the user has already clicked on close.
			// var show_form = check_popup_closed(drupalSettings.user.uid);
			// var showMemberPopup = parseInt($(".js-show-popup").text());
			// var optin = parseInt(drupalSettings.user.optin) ;
			var optin = getOptin() ;

			// If the user isn't subscribed and the "Close button" delay has expired
			var show_form = !optin && (hideUntil === null || hideUntil < now.getTime() ) ;
			return show_form;
		}
		return 0;

	}

	function check_popup_closed(user_id){
		var popup_data = {'uid': user_id}
		var show_form = 0;
    	jQuery.ajax({
          url:"/popup/get/date_close",
          type: "POST",
          data:  popup_data,
          success:function(data) {
              if (data.status === "SUCCESS"){
              		var show_popup = data.show_popup;
              		show_form = show_popup? 1 : 0;
              }
             
          }
        });

        return show_form;
	}

	function closePopup(){
		var now = new Date();
		var nextweek = new Date(now.getTime() + 7 * 24 * 60 * 60 * 1000);
		if (skip_delay){
			var nextweek = new Date(now.getTime() +  5 * 60 * 1000);
		}
		localStorage.setItem("hidePopupUntil", nextweek.getTime());
	}

	function noUserAction(){
		return true;
	}

	function saveOptinValue(){
    	var optin = $("#popup-optin").val();
    	var optin3 = 0;
    	if ($("#popup-optin3").length ){
    		var optin3 = $("#popup-optin3").val();
    	}

    	var langcode = window.getLangcode() 

    	popup_data = {'uid': drupalSettings.user.uid, 'optin': optin, 
    								'optin3': optin3, 'langcode': langcode}
    	window.saveOptin(optin);

    	jQuery.ajax({
          url:"/" + langcode + "/popup/optin/ajaxaction",
          type: "POST",
          data:  popup_data,
          success:function(data) {
              
              console.log(" success ");
              // var myaccount = window.language === "fr-ca"? "/fr-ca/mon-compte" : "/en-ca/my-account"; 
              var myaccount = "/" + langcode + "/form/updateprofile/processed";
              window.location.href = myaccount;
              // Do the same for french
          }
        });

        setTimeout(function(){
	        $("div.popup-main-wrapper").addClass("hidden");
			// wait for n seconds
		}, 1000* 2)
	}

	function loadSubscriberPopup(){
		// var currentUserName = drupalSettings.username; 
		var firstName = $(".js-username").text();
		$("span.js-firstname").text(firstName)
	}
		
	function submitPopup(){

		// Add email validation step hgere
		$(".js-register").click(function(event){
			event.preventDefault();
	    	var email = $("#popup-email").val();
	    	window.popupEmail = email;
	    	if (!validateEmail(email)){
	    		// Show error message
	    		$(".popup-email").addClass("popup-error");
	    		$(".popup-error-message").removeClass("hidden");
	    		// alert("Please enter a valid email");
	    		return false;
	    	}
			var signupButton = document.getElementsByClassName("jsModalSignUp")[0];
	    	var popup_source = "popupTastyrewards";
	    	window.popup_source = popup_source;
	    	signupButton.click();

	    	closePopup();
	    	// Need to make sure there is a Registration Source Content 
	    	// that matches this in the CMS
	    	window.popup_optin = document.getElementById('popup-optin').checked;
	    	window.popup_optin3 = document.getElementById('popup-optin3').checked;
	    	window.saveOptin(window.popup_optin);
	    	$("#email").val(email);	    	
	    	$("#source").val(popup_source);

	    	Drupal.behaviors.popupSubscription = {
			  attach: function (context, settings) {
			  	// check if this behavior is working properly
	    			document.getElementById('optin').checked = document.getElementById('popup-optin').checked;
	    			document.getElementById('optin3').checked = document.getElementById('popup-optin3').checked;
	    			$("#source").val(popup_source);
			  }
			};
	    	
	    	

		})

		$(".js-subscribe").click(function(event){
			event.preventDefault();
			saveOptinValue();

		})
	}

	function validateEmail(email) {
	    const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
	    return re.test(String(email).toLowerCase());
	}


	function activityWatcher() {
		//The number of seconds that have passed
		//since the user was active.
		var secondsSinceLastActivity = 0;

		var maxInactivity = 5; //secons

		//Setup the setInterval method to run
		//every second. 1000 milliseconds = 1 second.
		setInterval(function() {
			secondsSinceLastActivity++;
			//console.log(secondsSinceLastActivity + ' seconds since the user was last active');
			//if the user has been inactive or idle for longer
			//then the seconds specified in maxInactivity
			if (secondsSinceLastActivity > maxInactivity) {
				//console.log('User has been inactive for more than ' + maxInactivity + ' seconds');
				//Redirect them to your logout.php page.
				// location.href = '/' + document.documentElement.lang + '/logout';
				showPopup();
			}
		}, 1000);

		//The function that will be called whenever a user is active
		function activity() {
			//reset the secondsSinceLastActivity variable
			//back to 0
			secondsSinceLastActivity = 0;
		}

		//An array of DOM events that should be interpreted as
		//user activity.
		// var activityEvents = [ 'mousedown', 'mousemove', 'keydown', 'scroll', 'touchstart' ];
		var activityEvents = [ 'mousedown', 'keydown', 'touchstart' ];
		//add these events to the document.
		//register the activity function as the listener parameter.
		activityEvents.forEach(function(eventName) {
			document.addEventListener(eventName, activity, true);
		});
	}

	function formatdateToYMD(date) {
	    var d = date.getDate();
	    var m = date.getMonth() + 1; //Month from 0 to 11
	    var y = date.getFullYear();
	    return '' + y + '-' + (m<=9 ? '0' + m : m) + '-' + (d <= 9 ? '0' + d : d);
	}

	// window.dateToYMD = (date) => {
	//     var d = date.getDate();
	//     var m = date.getMonth() + 1; //Month from 0 to 11
	//     var y = date.getFullYear();
	//     return '' + y + '-' + (m<=9 ? '0' + m : m) + '-' + (d <= 9 ? '0' + d : d);
	// }


	window.rememberClosePopup = (str_date) => {
    	popup_data = {'uid': drupalSettings.user.uid, 'date': str_date}

    	jQuery.ajax({
          url:"/popup/close/savedate",
          type: "POST",
          data:  popup_data,
          success:function(data) {
              
              //console.log(" success ");
          }
        });
	}

	window.getLangcode = () => {
		var pathname = window.location.pathname;
		if (pathname.includes('/fr-ca')){
			return 'fr-ca';
		} else if (pathname.includes('/en-us')){
			return 'en-us';
		} else if (pathname.includes('/es-us')){
			return 'es-us';
		} else {
			return 'en-ca';
		}

	}


	/**
	 * This global function saves the optin value in the browser cookie.
	 * This avoids relying on the value of DrupalSettings.user.optin which 
	 * serves the cached value of the user optin. 
	 * Changes to DrupalSettings.user.optin are only reflected when the cache is cleared.
	 * @param  {[type]} optin_value [description]
	 */
	window.saveOptin = (optin_value) => {
    	if(optin_value == 1){
    		var now = new Date();
			var next_hour = new Date(now.getTime() + 60 * 60 * 1000);
			// This value is only valid for one hour because we assume
			// there is a cron process on the server that clears the Drupal
			// cache every hour. 
			// When the cache is cleared, drupalSettings.user.optin can be used
			// as shown in the getOptin() function 
			localStorage.setItem("optin_expire_date", next_hour.getTime());
    	} else {
			localStorage.setItem("optin_expire_date", 0);
    	}
	}

	function getOptin() {
    	var now = new Date();
		var optin_date = localStorage.getItem("optin_expire_date");
		if (optin_date > now.getTime() ) {
			return 1;
		} else {
			// This value may not be up to date until 
			// the cache is cleared, which is why we rely on the 
			// optin_expire_date value
			return parseInt(drupalSettings.user.optin);
		}

	}

	// function testFunction(){
	// 	$(".popup-title").click(function(){
	// 		var now = new Date();
	// 		var date_today  = window.dateToYMD(now); 
 //            window.rememberClosePopup(date_today);
	// 	});
	// }


	function doNotShowAgain(){
		$(".js-hide-forever").click(function(){
			var now = new Date();
			var nextyear = new Date(now.getTime() + 365 * 24 * 60 * 60 * 1000);
			if (skip_delay){
				var nextyear = new Date(now.getTime() +  5 *60 * 1000);
			}

			$("div.popup-main-wrapper").addClass("hidden");
			var logged_in = (drupalSettings.user.uid === 0)? 0 : 1;
			if (!logged_in){
				localStorage.setItem("hidePopupUntil", nextyear.getTime());
			} else {
				window.rememberClosePopup(nextyear);
				// var next_hour = new Date(now.getTime() +  3600 * 1000);
				localStorage.setItem("hidePopupUntil-" + drupalSettings.user.uid, nextyear.getTime());
			}
		})
	}

	//remove modal popupsubscription
	const popupModal = document.querySelector(".popup-main-wrapper")
	const closePopupSubscription = document.querySelector('.popup-close-button')

	if(closePopupSubscription){
		closePopupSubscription.addEventListener('click', function(event) {
			popupModal.classList.add('popup-modal-none');
			console.log(popupModal);
			var now = new Date();
			var nextweek = new Date(now.getTime() + 7 * 24 * 60 * 60 * 1000);
			if (skip_delay){
				var nextweek = new Date(now.getTime() +  300 * 1000);
			}

			var logged_in = (drupalSettings.user.uid === 0)? 0 : 1;
			if (!logged_in){
				localStorage.setItem("hidePopupUntil", nextweek.getTime());
			} else {
				// options = {day: "2-digit", month: "2-digit", year: 'numeric'}
				// var date_today  = window.dateToYMD(now);
				var date_today  = formatdateToYMD(now);
				window.rememberClosePopup(date_today);
				// var next_hour = new Date(now.getTime() +  3600 * 1000);
				localStorage.setItem("hidePopupUntil-" + drupalSettings.user.uid, nextweek.getTime());
			}
			// if user is logged in
		})
	}

}(jQuery, window));/*end of file*/

