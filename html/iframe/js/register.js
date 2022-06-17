$(document).ready(function(){

	language = $("#langcode").val();
	$(".btnregister").on('click', function() {

      $("#bday").val($("#bday_year").val() + '-' + $("#bday_month").val() + '-' + $("#bday_day").val());
       
       $.ajax({
          url:  "/" + language + "/pepsi/register/ajaxaction",
          type: "POST",
          data:  $('#snackperksreg').serialize(),
          success:function(data) {
              if (data.status){
                  
                  console.log("success");
                  $(".snackperksreg-confirm").removeClass("hidden");
                  $(".reg-form-wrapper").addClass("hidden");

                  // window.location.href = data.route;
              }
              else {
                  // $('.has-error').children('.help-block').html('');
                  // $('.has-error').removeClass('has-error');
                                    
                  //refresh the token
                  // $('#csrfToken').val(data.token);
                  // $.each(data.errors, function(field, msg) {
                  //     console.log("error at " + field)
                  //     console.log("message : " + msg)
                  // });
              }
          }
        });
      
  });
});