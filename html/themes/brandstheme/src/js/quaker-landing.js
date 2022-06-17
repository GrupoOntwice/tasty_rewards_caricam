
(function ($, window) {

  $(document).ready(function () {

  	var form_submitted = false;
  	$(".js-submit-receipt").click(function(event){
	  		event.preventDefault();
        var submit_button = $(this);
        setTimeout(function(){
          if (!form_submitted){
            form_submitted = true;
            submit_button.unbind('click');
            $("#contestsignup-form").submit();
            console.log("sunmitted");
            // submitReceipt();
          }
        },600);
  	});

  	$("body").addClass('quaker');
  	$(".js-upload-receipt").click(function(){
  		  var elmnt = document.getElementById("contestsignup-form");
				elmnt.scrollIntoView({block: "center"});
  			$("#uploadImg").click();
  	});

	  if (window.form_processed){
		  setTimeout(function(){
			  //document.getElementById("contestsignup-form").scrollIntoView();
			  $('html, body').animate({
				  scrollTop: $("#contestsignup-form").offset().top-200
			  }, 200);
		  }, 700);
	  }
  			
  //	$("#uploadImg").click(function(){
  //		 $(".upload-quacker-label").removeClass('grey').addClass('red');
  //	});

      function collapseAccordionFAQ() {
          $(".faq-border  a.faq-plus-minus").click(function (event) {
              event.preventDefault();
              $(this).closest("h4.faq-title").next().css("display:block");
          });
      }

      collapseAccordionFAQ();


      $('#contestsignup-form input').blur(function() {
		console.log(this.id);
		if (this.id == "contest-email"){
			if (!ValidateEmail($("#contest-email").val())){
				$("#contest-email").addClass('has-error');
			}
			else{
				$("#contest-email").removeClass('has-error');
			}
		}
		if (this.id == "contest-firstname" || this.id == "contest-lastname" ){
			if (!ValidateName($(this).val())){
				$(this).addClass('has-error');
			}
			else{
				$(this).removeClass('has-error');
			}
		}
		if (this.id == "postalcode" ){
			$("#postalcode").val($("#postalcode").val().toUpperCase()); 
			if (!ValidatePcode($(this).val())){
				$(this).addClass('has-error');
			}
			else{
				$(this).removeClass('has-error');
			}
		}		


      });

	  $('#contestsignup-form input').change(function() {
		let is_ready = isFormReady();
		if (is_ready){
			$(".js-submit-receipt").removeClass('grey');
			$('.js-submit-receipt').attr('disabled', false);
		} else {
			$(".js-submit-receipt").addClass('grey');
		 }
	 });

      function isFormReady(){
		    var fields =  ['postalcode', 'uploadImg', 'contest-lastname', 'contest-firstname',
		               'contest-email'];
		    for (var i = 0; i < fields.length; i++){
		      let value = $("#" + fields[i]).val();
		      if (value.trim() === ""){
		         return false;
		      }
		    }
		    return true;
		  }
		


	  /* Brands Header Nav Scroll */


	  $('.top-logo').on('click', function (e) {

		  //e.preventDefault();
		  $('html, body').animate({
			  scrollTop: $($.attr(this, 'href')).offset().top - scrollOffset
		  }, 750);

		  if ($(window).width() < 992) {
			  $('nav .brands-navbar__ul, nav .brands-navbar__social, nav .brands-navbar_accounts-mbl').slideUp('fast');
			  $('.brands-navbar__mobile-topnav').removeClass('ham-active');
		  }
	  });

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

	  // handleBrandMenuListener();

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
		  $('.navbar-default').show();
	  });

  });

	function submitReceipt(){
		var firstname = $("#firstname").val();
		var lastname = $("#lastname").val();
		var email = $("#email").val();
		var postalcode = $("#postalcode").val();
		var file = new FormData();
		// @TODO: Find a way to add the file
		file.append('file', $('#uploadImg')[0].files[0] );
	    // var lang = $('html').attr('lang'); 
	    var lang = window.current_lang + "-ca";
	    var payload = { 'firstname' : firstname, 
	    				'lastname' : lastname , 
	    				'email' : email , 
	    				'postalcode' : postalcode , 
	    				'file' : file , 
	    				'langcode': lang
	    			};
	    
	    jQuery.ajax({
	          url:"/pepsibrands/quaker/receipt/ajaxaction",
	          type: "POST",
	          data:  payload,
	          success:function(data) {
	              console.log(data);
	          }
	        });
	}

	function ValidateEmail(mail) 
	{
	 	if (/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(mail))
	  	{
			return true;
	  	}
		return false;
	}

	function ValidatePcode(value){
		if (/^[ABCEGHJKLMNPRSTVXY]{1}\d{1}[A-Z]{1} *\d{1}[A-Z]{1}\d{1}$/.test(value))
	  	{
			return true;
	  	}
		return false;	

	}


	function ValidateName(value){
		if (/^(?:[\s,.'-]*[a-zA-Z\pL][\s,.'-]*)+$/.test(value))
	  	{
			return true;
	  	}
		return false;	
	}

}(jQuery, window));/*end of file*/

















