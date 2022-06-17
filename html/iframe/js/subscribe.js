$(document).ready(function(){
    
    //language variable coming from twig (registration.html.tgiw)
    $("#casl").val($('.casl').html());
      
      language = $("#langcode").val();
    
    $(".btnregister").on('click', function() {

       clearError();
       $("#bday").val($("#bday_year").val() + '-' + $("#bday_month").val() + '-' + $("#bday_day").val());
       
       
       $("#grecaptcharesponse").parents(".form-group").removeClass("has-error");
       
       if (grecaptcha.getResponse() == ""){
            $("#grecaptcharesponse").parents(".form-group").addClass("has-error");
            scrollToError();
            return false;
       }
       $("#grecaptcharesponse").val(grecaptcha.getResponse());
               
       $(".btnregister span").addClass('glyphicon-refresh');
       $.ajax({
          url:  "/" + language + "/pepsi/register/ajaxaction",
          type: "POST",
          data:  $('#register-form').serialize(),
          success:function(data) {
              if (data.status){
                  
                  // START: tracking the registration on signup: SubscriptionMember / SubscriptionNewsletter
                  var event_value = "";
                  if(jQuery('#optin').is(':checked')){
                      // event_value = "SubscriptionMember";
                      // window.dataLayer = window.dataLayer || [];
                      // window.dataLayer.push({
                      //     'event': event_value
                      // });
                      // console.log(event_value);

                      event_value = "SubscriptionNewsletter";
                      window.dataLayer = window.dataLayer || [];
                      window.dataLayer.push({
                          'event': event_value
                      });
                      console.log(event_value);
                  }else{
                      event_value = "SubscriptionMember";
                      window.dataLayer = window.dataLayer || [];
                      window.dataLayer.push({
                          'event': event_value
                      });
                      console.log(event_value);
                  }
                  // END: tracking the registration on signup

                  window.location.href = data.route;
              }
              else {
                  $('.has-error').children('.help-block').html('');
                  $('.has-error').removeClass('has-error');
                                    
                  //refresh the token
                  $('#csrfToken').val(data.token);
                  clearError();
                  $.each(data.errors, function(field, msg) {
                      $("#"+field).parents(".form-group").removeClass("success");
                      if (field == 'bday') {
                        $("#"+field).parent().parent().addClass('has-error');
                        $(".err_"+field).html(msg);
                      }
                      else {
                        $(".err_"+field).html(msg);
                        $("#"+field).parent().addClass('has-error');
                      }
                  });
                  grecaptcha.reset();
                  $(".btnregister span").removeClass('glyphicon-refresh');
                  scrollToError();
              }
          }
        });
        
    });
    clearError();
    
    //Clear error msg and label 
    function clearError() {
        $(".has-error").each(function() {
            $(this).removeClass('.has-error');
            
        });
    }
    
    function scrollToError() {

        var errorelem = $(".has-error").first();
        var errortop = errorelem.offset().top - $(".navbarcontainer").height();

        //console.log(errortop);
        
        $('html, body').animate({
            scrollTop: errortop
        }, 500);
        //focusinput = errorelem.children('input');
        //focusinput.blur().focus().val(focusinput.val());
    }
            
    
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
        if (nameid === 'firstname' || nameid === 'lastname' || nameid === 'password' || nameid === 'confirm_password' ) {
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
        if (nameid === 'password') {
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
        var regex = new RegExp(/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9]).{8,16}$/);
        if (regex.test(password))
            return true;
        else return false;
    }
    /* end inline Validation */
    
});