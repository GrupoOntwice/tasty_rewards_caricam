(function ($, Drupal, window) {

    window.getCountry = () => {
        var pathname = window.location.pathname;
        if (pathname.includes('/fr-ca') || pathname.includes('/en-ca')){
            return 'canada';
        } else if (pathname.includes('/en-us') || pathname.includes('/es-us') ){
            return 'usa';
        }
        return '';
    }

    $(document).ready(function(){

        if (window.scrollToForm === true){
            setTimeout(
                function(){
                    var elem = document.getElementById("contestsignup-form");
                     elem.scrollIntoView();
                }
                , 1000);
        }

        $(".contest-loggedin .js-contest-submit").click(function(e){
            if ( $(".contest-loggedin #contest_optin_rules").prop('checked') != true 
                && window.getCountry() == 'usa'
                ) {
                e.preventDefault();
                $(".err_optin_rules").text(window.error_requiredField);
            }

        });

    });
}(jQuery, Drupal, window));