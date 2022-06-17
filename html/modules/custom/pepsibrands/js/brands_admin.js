(function($, Drupal) {

Drupal.behaviors.brandsContentAdmin = {
        attach: function (context, settings) {
            var selected_option = $('#edit-field-brand').val();
            if (selected_option == 'Lays'){
                changeLaysProductLabels();
        	}


        	$('#edit-field-brand').on('change', function (e) {
	            var brand = $(this).val();
	            if (brand == 'Lays'){
	                changeLaysProductLabels();
	        	}

        	});

        }
};

function changeLaysProductLabels(){
	$(".field--name-field-extra-image1 .js-form-managed-file").siblings('label').text('Featured Image');
}

})(jQuery, Drupal);