(function($, Drupal) {

	$(document).ready(function(){

		var menuBrandHasListener = false;

		function handleBrandMenuListener() {
		  if ($(window).width() < 992 && !menuBrandHasListener) {
			  // Header hamburger menu
			  $('.brands-navbar__mobile-topnav').on('click', function () {
				  $('nav .brands-navbar__ul, nav .brands-navbar__social, nav .brands-navbar_accounts-mbl').toggle('fast');
				  $(this).toggleClass('ham-active');
				  $('#brands_navigation hr').toggleClass('d-none');
			  });
			  menuBrandHasListener = true;
		  }
		}

		handleBrandMenuListener();

		// TR Navbar in Mobile
		$('#button-mbl-back').on('click', function (e) {
		  $('.navbar-default').hide().css('display' + 'none');
		  // $('#brands_navigation').show();
		  $('#brands_navigation').removeClass('hidden');
		  $('.brands-navbar__mobile-topnav').addClass('ham-active');
		  $('.brands-navbar__ul, .brands-navbar_accounts-mbl').css('display', 'block');
		});

		$('.tr-navbar-trigger-mobile').on('click', function () {
		  $('#brands_navigation').addClass('hidden');
		  // $('.navbar-default').show();
		  $('.navbar-default').attr('style','display:block !important');
		});

		setTranslationUrl();

		document.querySelector(".toupload").innerText = "";
    


	});

	window.fileChanged = function(input) {
        let fileName = document.querySelector('#uploadImg');
        fileName.textContent = input.files[0]['name'];
        if (fileName.textContent > ''){
            document.querySelector(".toupload").innerText = fileName.textContent;
        }
        else{
            document.querySelector(".toupload").innerText = "";
        }
    }


	function setTranslationUrl(){
      var current_url = window.location.pathname;
      if (drupalSettings.path && drupalSettings.path.currentLanguage){
        var current_lang = drupalSettings.path.currentLanguage;
        var translated_langcode = current_lang == 'en'? 'fr-ca' : 'en-ca';
        var translated_url = current_url.replace(current_lang + "-ca", translated_langcode);
        $(".js-translation").attr('href', translated_url);
        if ($(".node-url-translation").length){
          $(".js-translation").attr('href', $(".node-url-translation").data('url'));
        }
      } else if (current_url.indexOf("tostitos/products-categories") !== -1  
                || current_url.indexOf("tostitos/produits-categories") !== -1){
          var translated_langcode = window.lang_prefix == 'en-ca'? 'fr-ca' : 'en-ca';
          var translated_url = current_url.replace(window.lang_prefix, translated_langcode);
          $(".js-translation").attr('href', translated_url);
      }

      if ($('.js-no-translation').length ){
        $(".js-translation").attr('href', 'javascript:void(0);');
      }

  }


})(jQuery, Drupal);