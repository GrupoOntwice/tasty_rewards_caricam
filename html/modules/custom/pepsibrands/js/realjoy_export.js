(function($, Drupal) {

	$(document).ready(function(){
		$(".js-export-csv").click(function(event){
			window.open($(this).data('link') , '_blank').focus();
		});

	});


})(jQuery, Drupal);