(function ($, Drupal, window) {
    
    //language variable coming from twig (registration.html.tgiw)
    
    
    $(".btnregister").on('click', function() {
       $(".btnregister span").addClass('glyphicon-refresh');
       $.ajax({
          url:"/" + language + "/pepsi/changepassword/ajaxaction",
          type: "POST",
          data:  $('#updatepassword-form').serialize(),
          success:function(data) {
              if (data.status){
                  window.location.href = data.route;
              }
              else {
                  $('.has-error').children('.help-block').html('');
                  $('.has-error').removeClass('has-error');
                                    
                  //refresh the token
                  $('#csrfToken').val(data.token);
                  
                  $.each(data.errors, function(field, msg) {
                      $("#"+field).parent().addClass('has-error')
                      $(".err_"+field).html(msg);
                  });
                  $(".btnregister span").removeClass('glyphicon-refresh');
              }
          }
        });
        
    });
}(jQuery, Drupal, window));