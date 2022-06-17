(function ($, Drupal, window) {

	window.submit_contestform = function (){
		$('#contestsignup-form').submit();
	}

    $("#submitcontest").click(function(event){
        event.preventDefault();
        submitsignup = false;
        var tmp = grecaptcha;
        grecaptcha.execute().then(function (token) {
            //console.log(token);
            $('#grecaptcharesponse_contest').val(token);
            // $('#contestsignup-form').submit();
        });
    });	



}(jQuery, Drupal, window));