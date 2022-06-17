
$(document).ready(function(){
    //language variable coming from twig (registration.html.tgiw)
    $(".btnregister").on('click', function() {
      language = $("#langcode").val();
       $.ajax({
          url:"/" + language + "/pepsi/unsubscribe/ajaxaction",
          type: "POST",
          data:  $('#snackperksreg').serialize(),
          success:function(data) {
              if (data.status){
                  // window.location.href = data.route;
                  $(".snackperksreg-confirm").removeClass("hidden");
                  $(".reg-form-wrapper").addClass("hidden");

              }
              else {
                  // $('.has-error').children('.help-block').html('');
                  // $('.has-error').removeClass('has-error');
                                    
                  
              }
          }
        });
        
    });
});