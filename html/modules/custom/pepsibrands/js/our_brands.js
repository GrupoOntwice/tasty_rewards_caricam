(function($, Drupal) {

	$(document).ready(function(){

		// var active_brands = ['Frito Lay Variety Packs', 'bare logo', 'Tostitos', "Lay's logo"];
		var active_brands = [
			'bare', 'cheetos', 'doritos', 'crispyminis', 'tostitos',
			'lays', 'fritolayvarietypacks', 'produitsassortisfritolay', 'fritolayproduitsassortis',
			'missvickies', 'offtheeatenpathsnacks', 'collationsofftheeatenpath',
			'ruffles', 'quaker'
		];
		var i;
		for (i = 0; i < active_brands.length; i++ ){
			remove_target_blank(active_brands[i]);
		}

	});

	function remove_target_blank(brand){
		// $('.all-brands .brand-icon img[alt="' + brand + '"]').closest("a").attr('target', '_self');
		 $(".all-brands .brands-item .brand-icon").each(function(){
		 	var brand_url = window.location.pathname.replace(/\/$/, '') + "/" + brand 

		 	if (this.href.indexOf(brand_url) != -1){
		 		$(this).attr('target', '_self');
		 	}
		 })
	}


})(jQuery, Drupal);