(function ($, Drupal, window) {
    
    $(document).ready(function() {
        $('.js-age-checkbox').change(function() {
            if(this.checked) {
                $('.js-age-checkbox').not(this).prop("checked", false);
            } 
        });
    });


}(jQuery, Drupal, window));