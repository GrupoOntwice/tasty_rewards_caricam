(function ($, Drupal, window) {

    function scrollToError() {

        var errorelem = $(".has-error").first();
        if (errorelem.length > 0 ) {
            var errortop = errorelem.offset().top - $(".navbarcontainer").height();

            $('html, body').animate({
                scrollTop: errortop
            }, 500);
            //focusinput = errorelem.children('input');
            //focusinput.blur().focus().val(focusinput.val());
        }
    }
    scrollToError();

    $(".btnregister" ).on("click",function( event ) {
      /*  
      if (grecaptcha.getResponse() == ""){
            $("#grecaptcharesponse").parents(".form-group").addClass("has-error");
            scrollToError();
            return false;
      }
      $("#grecaptcharesponse").val(grecaptcha.getResponse());
      */
     
      $("#contestsignup-form" ).submit();
      
    });


    $("#contestsignup-form" ).submit(function( event ) {
      $('.btnregister').attr('disabled','disabled');
    });
    

    /* inline Validation */
    
    $("#bday_day, #bday_month, #bday_year").focusout(function() {
        if ( $(this).val() === "") {
            $(this).removeClass("success")
        }
        else{
            $(this).addClass("success")
        }

        if ($("#bday_day").val() !== "" && $("#bday_month").val() !== "" && $("#bday_year").val() !== ""  ){
                addSuccess($(this),true);
        }    

        if ($("#bday_day").val() === "" || $("#bday_month").val() === "" || $("#bday_year").val() === ""  ){
                addSuccess($(this),false);
        }
    });
    
    
    $(".form-group input").focusout(function() {
        var nameid = $(this).attr('id');
        //firstname| lastname | email | confirm_email | password | confirm_password | bday_day | bday_month | bday_year | postalcode
        if (nameid === 'firstname' || nameid === 'lastname' || nameid === 'contest-password' || nameid === 'confirm_password' ) {
            if ($(this).val().length === 0 ){
                addSuccess($(this),false);
            }
            else{
                addSuccess($(this),true);
            }
        }
        if (nameid === 'email' || nameid === 'confirm_email' ){
            if (isValidEmailAddress($(this).val())) {
                addSuccess($(this),true);
            }
            else{
                addSuccess($(this),false);
            }
        }
        if (nameid === 'confirm_email') {
            if (isValidEmailAddress($(this).val()) && $(this).val() === $("#email").val()  ){
                addSuccess($(this),true);
            }
            else{
                addSuccess($(this),false);
            }
        }
        
        if (nameid === 'postalcode') {
            if (isGoodPostalCode($(this).val())){
                addSuccess($(this),true);
            }
            else{
                addSuccess($(this),false);
            }
        }
        if (nameid === 'contest-password') {
            if (isGoodPassword($(this).val())){
                addSuccess($(this),true);
            }
            else{
                addSuccess($(this),false);
            }
        }
        
        if (nameid === 'confirm_password') {
            if ($(this).val() === $("#password").val() && $(this).val().length > 0  ){
                addSuccess($(this),true);
            }
            else{
                addSuccess($(this),false);
            }
        }
    });
    
    function addSuccess(obj,isSuccess){
        if (isSuccess ){
            obj.parents(".form-group").addClass("success");
            obj.parents(".form-group").removeClass("has-error");
        }
        else{
            obj.parents(".form-group").addClass("has-error");
            obj.parents(".form-group").removeClass("success");
        }
    }
    
    function isValidEmailAddress(emailAddress) {
        var pattern = /^([a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+(\.[a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+)*|"((([ \t]*\r\n)?[ \t]+)?([\x01-\x08\x0b\x0c\x0e-\x1f\x7f\x21\x23-\x5b\x5d-\x7e\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|\\[\x01-\x09\x0b\x0c\x0d-\x7f\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))*(([ \t]*\r\n)?[ \t]+)?")@(([a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.)+([a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.?$/i;
        return pattern.test(emailAddress);
    };
    
    function isGoodPostalCode(postalcode){
        var regex = new RegExp(/^[ABCEGHJKLMNPRSTVXY]\d[ABCEGHJKLMNPRSTVWXYZ]( )?\d[ABCEGHJKLMNPRSTVWXYZ]\d$/i);
        if (regex.test(postalcode))
            return true;
        else return false;
    }
    
    function isGoodPassword(password){
        var language = $('html').attr('lang');
        if (language == 'en' || language == 'fr'){
            var minMaxLength = /^[\s\S]{8,32}$/,
                    upper = /[A-Z]/,
                    lower = /[a-z]/,
                    number = /[0-9]/,
                    special = /[ !"#$%&'()*+,\-./:;<=>?@[\\\]^_`{|}~]/;
    
            if (minMaxLength.test(password) &&
                    upper.test(password) &&
                    lower.test(password) &&
                    number.test(password) &&
                    special.test(password)
            ) {
                return true;
            }
            return false;
        }
        else{
            var regex = new RegExp(/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9]).{8,16}$/);
            if (regex.test(password))
                return true;
            else return false;
        }
    }

    function ajaxSendEligibilityForm(){
        var has_province = $(".popup-eligibility #province").length;
        if (window.contest_type === 'cheetos'){
            var province = $("#age_province").val();
            has_province = 1;
        } else {
            var province = $("#province").val();
        }
        var bday_day = $("#agegate_bday_day").val();
        var bday_month = $("#agegate_bday_month").val();
        var bday_year = $("#agegate_bday_year").val();
        if (bday_year && bday_month && bday_year && ( !has_province || province.length ) ){
            // form is valid
            $(".popup-main-wrapper .js-validate-error").addClass("hidden");
        } else {
            $(".popup-main-wrapper .js-validate-error").removeClass("hidden");
            return;
        }

        var bday = bday_year + '-' + bday_month + '-' + bday_day

        var contest_name = $("#contest").val();
        var contest_type = $("#contest_type").val();
        var lang_prefix = window.lang_prefix || 'en-ca' ;
        var langcode = window.langcode || 'en' ;

        popup_data = {'province': province, 'bday': bday, 
                    'contest': contest_name, 'lang_prefix': lang_prefix,
                    'contest_type': contest_type, 'langcode': langcode
                };

        jQuery.ajax({
          url:"/contest/custom/age/ajaxaction",
          type: "POST",
          data:  popup_data,
          success:function(data) {
              console.log(data);
              if (data.legal == 1){
                // window.location.href = data.redirect;
                let now = new Date();
                localStorage.setItem(window.contest_name + '_canplay', now.getTime() + 3600*1000 );
                enableContestForm();
                if (window.contest_type == 'cheetos'){
                    if ( province == 'quebec' || province == 'québec'){
                        $(".cheetos-roc").addClass("hidden");
                    } else {
                        $(".cheetos-qc").addClass("hidden");
                    }
                    $(".noprovince").removeClass("noprovince");
                    localStorage.setItem('cheetos_province', province);
                }
                // @TODO: Automatically close the popup
                $(".popup-close-button").click();
                $(".popup-main-wrapper.popup-contest").addClass("hidden");
              } else {
                $(".js-legal-error").removeClass("hidden");
                $("#popup-subscribe select").addClass('hidden');
                $(".js-submit-eligibility").remove();
                $(".popup-sub-title").addClass('hidden');
                if (window.contest_type == 'cheetos'){
                    $("#cheetos-agegate select").prop("disabled", "disabled");
                    $("#cheetos-agegate select").addClass('hidden');
                    $(".js-age-gate-hide").addClass('hidden');
                    $("#cheetos-agegate").addClass('underage');

                }

                localStorage.setItem(window.contest_name + "_disable_form", 1);
              }
              // Do the same for french
          }
        });
    }
    /* end inline Validation */ 

    function enableContestForm(){
        $("#contestsignup-form input").prop("disabled", false);
    }
    
    if (!window.contest_type ){
        $("#submitcontest").click(function(event){
            // event.preventDefault();
            // submitsignup = false;
            //grecaptcha.execute().then(function (token) {
                //console.log(token);
                //$('#grecaptcharesponse_contest').val(token);
                // $('#contestsignup-form').submit();
            //});
        });
    }

    $(".js-tr-signup").click(function(){
        event.preventDefault();
        $(".jsModalSignUp").click();

    });



    $(".js-submit-eligibility").click(function(){
        event.preventDefault();
        // $('.popup-eligibility').submit();
         ajaxSendEligibilityForm()

    });

    $(".js-legal").click(function(){
        $(".popup-contest").removeClass("hidden");
        $(".popup-contest").removeClass("popup-modal-none");

        window.popup_contest = 1;
    });

    
}(jQuery, Drupal, window));