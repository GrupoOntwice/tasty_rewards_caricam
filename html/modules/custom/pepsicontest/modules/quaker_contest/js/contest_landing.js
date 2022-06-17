(function ($, Drupal, window) {

	$(document).ready(function() {
        var $toActivateVideo = $(".activate-video");

        function activateVideos() {
            $toActivateVideo.off().on('click', function () {
                let key = $(this).data('key');
                let iframe = '<div class="quaker-video-iframe-container"><iframe src="https://www.youtube.com/embed/' + key + '?autoplay=1&rel=0" frameborder="0" allow="autoplay" allowfullscreen></iframe><div>';
                $(this).closest('.quaker-video-box').prepend(iframe);
                $(this).closest('.quaker-video-box').find('.quaker-video-preimg').remove();
                return false;
            });
        }

        activateVideos();




		if (localStorage.getItem(window.contest_name + "_disable_form") == 1){
			$("#popup-subscribe input").prop("disabled", "disabled")
			$("#popup-subscribe select").prop("disabled", "disabled")
		}

		if ( window.contest_name ){
			if (!localStorage.getItem(window.contest_name + "_canplay") || hasAgeGateExpired() ){
				$(".popup-contest").removeClass("hidden");
	        	$(".popup-contest").removeClass("popup-modal-none");
			} else {
				if (!$(".user-logged-in").length) {
					$("#contestsignup-form input").prop("disabled", false);
				} else {
					$("#contestsignup-form input#contest-upccode").prop("disabled", false);
				}
        $(".noprovince").removeClass("noprovince");
			}
			if (!localStorage.getItem(window.contest_name + "_claim")){
				$(".js-popup-claim").removeClass("hidden");
    			$(".js-popup-claim").removeClass("popup-modal-none");
			}
		}

      setCheetosProvince(); 
      var form_submitted = false;

      $(".js-submit-contest").click(function(event){
        event.preventDefault();
        var submit_button = $(this);
        setTimeout(function(){
          if (!form_submitted){
            form_submitted = true;
            submit_button.unbind('click');
            submitCustomContestForm(submit_button);
            
          }
        },300);

      });


      function submitCustomContestForm(submit_button){
          if (window.contest_type == 'cheetos'){
            if (submit_button.hasClass('cheetos-qc')){
              $(".js-submit-contest-button.cheetos-qc").click();
            } else {
              $(".js-submit-contest-button.cheetos-roc").click();
            }
          } else {
            $("#contestsignup-form").submit();
          }
      }

    	$(".js-submit-popup-claim").click(function(event){
          event.preventDefault();
           ajaxClaimValidation()
      });

      $(".js-submit-claim").click(function(event){
          event.preventDefault();
          let has_error = validateClaimAddressForm();
          if (has_error === false){
            $(".js-submit-claim").unbind('click').click();
            $('form#prize-claim').submit();
          }
      });

      if ($(".js-prizename").length){
        // ajaxConfirmationEmail();
      }


      $(".js-challenge-submit").click(function(event){
          event.preventDefault();
          ajaxChallengeValidation()
      });

      $(".js-entercontest").click(function(event){
          let link  = $(this).data('link');
          window.location.href = link;
      });

	    $(".js-cheetos-play").click(function(event){
            var elmnt = document.getElementById("contestsignup-form");
            elmnt.scrollIntoView();
	    });

      $(".js-start").click(function(event){
          event.preventDefault();
          /* Cheetos-specific start */
          $('.cheetos-skilltest-controls').hide();
          $('.cheetos-skilltest-time').removeClass('hidden');
          /* Cheetos-specific end*/

          $(".start-cancel-button").addClass('hidden');
          $('.skill-timer-container').show();
          $('.skill-test label.skill-test-question').show();
          $(".grabsnack-address-form").removeClass('form-disabled');
          $("#challenge").removeAttr('disabled');
          $(".js-challenge-submit").removeClass('disabled').removeAttr('disabled');
          $(".quaker-hockey-contest-claim-button").removeClass('grey');

          if (! window.claim_submitted){
            start_math_challenge();
          }

          if (window.innerWidth < 768) {
            $([document.documentElement, document.body]).animate({
              scrollTop: $("#form-scroll-anchor").offset().top
            }, 1000);
          }
      });

      if (window.claim_submitted == 1){
         $(".js-start").click();
         processValidSkillTest();
      }

      $('#contestsignup-form input, .grabsnack-contest-form select').change(function() {
         let is_ready = isContestFormReady();
         if (is_ready){
            $("#submitcontest").addClass('js-form-ready');
         } else {
            $("#submitcontest").removeClass('js-form-ready');
          }
      });

        $('#contestsignup-form input, .nintendo-contest-form select').change(function() {
            let is_ready = isContestFormReady();
            if (is_ready){
                $("#submitcontest").addClass('js-form-ready');
            } else {
                $("#submitcontest").removeClass('js-form-ready');
            }
        });

        $('#contestsignup-form input, #contestsignup-form select').change(function() {
            let is_ready = isContestFormReady();

            if (is_ready){
                $(".contest-detail__sign-up-button").removeClass('grey');
                $('.contest-detail__sign-up-button').attr('disabled', false);
            } else {
                $(".contest-detail__sign-up-button").addClass('grey');
            }
        });

      /* ************* */
      /* CHEETOS START */
      /* ************* */
        $("#cheetos-faq .faq-question").click(function() {
          $(this).siblings('.faq-answer').slideToggle();
          $(this).toggleClass('active');
        });
        
        $('#cheetos-start-skilltest').click(function() {
          $('#cheetos-skilltest-question').show();
        });

        var countdownDate = new Date(window.contest_opendate).getTime();
        url = new URL(window.location.href);

        if (url.searchParams.get('comingsoon') || window.comingsoon == 1) {
          countdownDate = new Date("Jun 28, 2021 12:00:00").getTime();
        }

        var countdownTimer = setInterval(function() {
          var now = new Date().getTime();
          var timeLeft = countdownDate - now;

          if (timeLeft < 0) {
            clearInterval(countdownTimer);
            return;
          }

          var daysLeft = Math.floor(timeLeft / (1000 * 60 * 60 * 24));
          var hoursLeft = Math.floor((timeLeft % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
          var minutesLeft = Math.floor((timeLeft % (1000 * 60 * 60)) / (1000 * 60));
          var secondsLeft = Math.floor((timeLeft % (1000 * 60)) / 1000);

          var days = $("#cheetos-days");
          var hours = $("#cheetos-hours");
          var minutes = $("#cheetos-minutes");
          var seconds = $("#cheetos-seconds");

          if (daysLeft.toString().length < 2) {
            daysLeft = "0" + daysLeft.toString();
          }
          if (hoursLeft.toString().length < 2) {
            hoursLeft = "0" + hoursLeft.toString();
          }
          if (minutesLeft.toString().length < 2) {
            minutesLeft = "0" + minutesLeft.toString();
          }
          if (secondsLeft.toString().length < 2) {
            secondsLeft = "0" + secondsLeft.toString();
          }

          days.html(daysLeft);
          hours.html(hoursLeft);
          minutes.html(minutesLeft);
          seconds.html(secondsLeft);
        }, 1000);
      /* *********** */
      /* CHEETOS END */
      /* *********** */

	});	

  function hasAgeGateExpired(){
      let now = new Date();
      let timestamp = localStorage.getItem(window.contest_name + "_canplay");
      if (now.getTime() > timestamp){
        return 1;
      }

      if (window.contest_type == 'cheetos'){
        var province = localStorage.getItem('cheetos_province');
        if (!province){
          return 1;
        }
      }
      return 0;

     // if (window.show_age_popup == 1){
     //    return 1;
     // }
     // return 0;
  }

  function isContestFormReady(){
    var fields =  ['postalcode', 'contest-upccode', 'contest-lastname', 'contest-firstname',
               'contest-email'];
    for (var i = 0; i < fields.length; i++){
      let value = $("#" + fields[i]).val();
      if (value.trim() === ""){
         return false;
      }
    }

      if (  $(".grabsnack-contest-form select").length && 
            $(".grabsnack-contest-form select").val().trim() === "" )
      {
         return false;
      }

      if ( $(".nintendo-contest-form select").length && 
        $(".nintendo-contest-form select").val().trim() === ""){
          return false;
      }

    return true;
  }


  function validateClaimAddressForm(){
      var has_error = false;
      var fields =  ['firstname', 'lastname', 'city', 'address1', 'postalcode', 'phone'];
      if (window.contest_name == 'cheetos'){
        fields.push('email');
      }

      for (var i = 0; i < fields.length; i++){
        $("span.help-block." + fields[i]).text('');
        if ( $('#' + fields[i]).val() === undefined){
          continue;
        }


        if ($('#' + fields[i]).val().trim() === ""){
          has_error = true;
          // error_message = fields[i] +  " is required";
          error_message = getClaimFormErrorMessage(fields[i]);
          $("span.help-block." + fields[i]).text(error_message);
        }
      }

      return has_error;

  }

  function getClaimFormErrorMessage(fieldname){
    var lang = window.language;
      var _err = {
        'en-ca':' is a required field',
        'fr-ca':' est obligatoire'
      };
      var messages = {
         'firstname' : (lang == 'fr-ca' ? 'Le prénom' : 'First name') + _err[lang], 
         'lastname' : (lang == 'fr-ca' ? 'Le prénom' : 'Last name') + _err[lang], 
         'city' : (lang == 'fr-ca' ? 'La ville' : 'City') + _err[lang], 
         'phone' : (lang == 'fr-ca' ? 'Le numero de telephone' : 'Phone number') + _err[lang], 
         'postalcode' : (lang == 'fr-ca' ? 'Le code postal' : 'Postal Code') + _err[lang], 
         'address1' : (lang == 'fr-ca' ? "L'adresse" : 'Address') + _err[lang], 
         'email' : (lang == 'fr-ca' ? 'Le couriel' : 'Email') + _err[lang], 
      }
      return messages[fieldname];
  }

  function setCheetosProvince() {
     if(localStorage.getItem('cheetos_province') ){
        window.cheetos_province = localStorage.getItem('cheetos_province');
        if (window.cheetos_province == 'quebec' || window.cheetos_province == 'québec'){
          $(".cheetos-roc").addClass("hidden"); 
        } else {
          $(".cheetos-qc").addClass("hidden"); 
        }
     }

     if (  window.contest_type == 'cheetos' && 
          ( $("#cheetos-agegate").length == 0 || $("#cheetos-agegate").hasClass("hidden") )
      )
      {
        $(".noprovince").removeClass("noprovince");
      }
  }



  function validateEmail(email) {
    const re = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(String(email).toLowerCase());
  }

	function ajaxClaimValidation(){
        var firstname = $("#claim_firstname").val();
        var lastname = $("#claim_lastname").val();
        var email = $("#claim_email").val();
        var enterdate = $("#enterdate").val();
        var contest_id = $("#contest_id").val();

        $(".js-claim-email").text('');

        if (!validateEmail(email)){
          $(".js-claim-email").removeClass('hidden');
          $(".js-claim-email").text('Please enter a valid email address.');
          return false;
        }


        var lang_prefix = window.lang_prefix || 'en-ca' ;
        var langcode = window.langcode || 'en' ;

        popup_data = {'firstname': firstname, 'lastname': lastname, 
                    'contest_id': contest_id, 'email': email,
                    'enterdate': enterdate, 'langcode': langcode
                };

        console.log(popup_data);

        jQuery.ajax({
          url:"/contest/custom/claim/ajaxaction",
          type: "POST",
          data:  popup_data,
          success:function(data) {
              console.log(data);
              if (data.valid == 1){
                // window.location.href = data.redirect;
                localStorage.setItem(window.contest_name + '_claim', 1);
                // @TODO: Automatically close the popup
                $(".popup-close-button").click();
                $("#email").val(email);
              } else {
                  $(".js-claim-email").removeClass('hidden');
                  $(".js-claim-email").text(data.message);
                // $(".modal__sign-up-birth .has-error").removeClass("hidden");
                // $("#popup-subscribe select").prop("disabled", "disabled")
                // localStorage.setItem("disable_form", 1);
              }
              // Do the same for french
          }, error: function (xhr, ajaxOptions, thrownError) {
            console.log(xhr.status);
            console.log(thrownError);
            console.log(ajaxOptions);
          }
        });
    }

    function ajaxConfirmationEmail(){
        var prize = $(".js-prizename").text();
        var vars = {};
        var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
          vars[key] = value;
        });


        var claim = $("#claim_url").val(); //vars['claim'];
        var firstname = $("#winner_firstname").val(); // vars['firstname'];
        var uid = vars['uid'];
        var email = $("#winner_email").val();

        var nid = $("#prize-claim").data('nid');



        payload = {'claim': claim, 'firstname': firstname,
                          'prize' : prize, 'uid': uid, 'email': email};

        jQuery.ajax({
          url:"/contest/custom/email/ajaxaction",
          type: "POST",
          data:  payload,
          success:function(data) {
              // console.log(data);
              if (data.success == 1){
                console.log("success");
              } else {
              }
          }
        });
    }


    function ajaxChallengeValidation(){
        var answer = $("#challenge").val();
        var question_id  = $("#challenge_id").val();
        var vars = {};
        var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
          vars[key] = value;
        });


        var enterdate = vars['date'];
        var uid = vars['uid'];

        var nid = $("#prize-claim").data('nid');



        challenge_data = {'answer': answer, 'question_id': question_id,
                          'enterdate' : enterdate, 'uid': uid, 'nid' : nid};

        jQuery.ajax({
          url:"/contest/custom/math-challenge/ajaxaction",
          type: "POST",
          data:  challenge_data,
          success:function(data) {
              console.log(data);
              window.challenge_completed = 1;
              if (data.correct == 1){
                processValidSkillTest();
                

              } else {
                $(".js-challenge-submit").addClass('hidden');
                if (window.challenge_timeout_expired){
                  $(".js-challenge-timeout").removeClass('hidden');
                }else {
                  $(".js-challenge-error").removeClass('hidden');
                }
                $("#challenge").prop('disabled', true).addClass('js-disabled');

              }
              localStorage.removeItem(window.contest_name + '_claim');
              // Do the same for french
          }
        });
    }

    function processValidSkillTest(){
      $('.show-on-correct').show();
      $('.show-on-correct').removeClass('hidden');
      $(".grabsnack-address-form select#province").removeAttr('disabled');
      $("#prize-claim input").removeAttr('disabled');
      $(".js-challenge-success").removeClass('hidden');
      $(".js-challenge-submit").addClass('hidden');
      $(".js-submit-claim").removeClass('disabled').removeAttr('disabled');
      $("#challenge").prop('disabled', true).addClass('js-disabled');
      // $(".hockey-contest-form .skill-test").addClass("hidden");
    }

  function start_math_challenge(){

    var countDownDate = new Date();
    countDownDate.setMinutes(countDownDate.getMinutes() + 3);

    // Update the count down every 1 second
    var x = setInterval(function() {

      // Get today's date and time
      var now = new Date().getTime();

      // Find the distance between now and the count down date
      var distance = countDownDate - now;

      // Time calculations for days, hours, minutes and seconds
      var days = Math.floor(distance / (1000 * 60 * 60 * 24));
      var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
      var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
      var seconds = Math.floor((distance % (1000 * 60)) / 1000);

      // Display the result in the element with id="skill-timer"
      document.getElementById("skill-timer").innerHTML = minutes + "m " + seconds + "s ";

      // If the count down is finished, write some text
      if (distance < 0) {
        clearInterval(x);
        document.getElementById("skill-timer").innerHTML = "";
        window.challenge_timeout_expired = 1;
        ajaxChallengeValidation();

        let inputs = document.querySelectorAll("#prize-claim input");
        for (let i = 0; i < inputs.length ;  i++){
          inputs[i].disabled = true;
        }
      }

      if (window.challenge_completed){
        clearInterval(x);
        document.getElementById("skill-timer").innerHTML = "";

      }
    }, 1000);
  }
          

}(jQuery, Drupal, window));