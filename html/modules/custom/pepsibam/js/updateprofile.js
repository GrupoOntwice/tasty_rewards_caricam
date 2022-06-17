(function ($, Drupal, window) {
    $("#casl").val($('.casl').html());
    //language variable coming from twig (registration.html.tgiw)
    $(document).on('change', '.js-language-select', function(){
        var lang_preference = $(this).find("option:selected").attr('value');
        $(".js-language").val(lang_preference);
    });

    $(".btnregister").on('click', function() {
       $("#bday").val($("#bday_year").val() + '-' + $("#bday_month").val() + '-' + $("#bday_day").val());
       $(".btnregister span").addClass('glyphicon-refresh');

       if ($("#optin").is(":checked")){
         window.saveOptin(1);
       } else {
         window.saveOptin(0);
       }

       $.ajax({
          url:"/" + language + "/pepsi/updateprofile/ajaxaction",
          type: "POST",
          data:  $('#update-form').serialize(),
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
                      if (field == 'bday') {
                        $("#"+field).parent().parent().addClass('has-error')
                        $(".err_"+field).html(msg);

                      }
                      else {
                        $("#"+field).parent().addClass('has-error')
                        $(".err_"+field).html(msg);
                      }
                  });
                  $(".btnregister span").removeClass('glyphicon-refresh');
                  scrollToError();
              }
          }
        });

    });

    $(".btndeleteprofile").on('click', function() {

       $(".btndeleteprofile span").addClass('glyphicon-refresh');

       $.ajax({
          url:"/" + language + "/my-account/delete/ajaxaction",
          type: "POST",
          data:  $('#delete-form').serialize(),
          success:function(data) {
              if (data.status){

                $(".btndeleteprofile span").removeClass('glyphicon-refresh');
                $("#user_id").val(data.user_id);
                //show the section with the reasons
                $("#deleteprofilereasons").show();
                $("#deleteprofilereasons").removeClass("hidden");
                $("#delete-form").hide();

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
                  $(".btndeleteprofile span").removeClass('glyphicon-refresh');
              }
          }
        });

    });

    $(".btndeleteprofilereasons").on('click', function() {

       $(".btndeleteprofilereasons span").addClass('glyphicon-refresh');
       gatherReasons();
       if ($("#reasons").val() == ""){
           window.location.href = "/";
       }else{
            $.ajax({
               url:"/" + language + "/my-account/delete/reasons/ajaxaction",
               type: "POST",
               data:  $('#delete-form-reasons').serialize(),
               success:function(data) {
                   if (data.status){
                       window.location.href = data.route;
                       $(".btndeleteprofilereasons span").removeClass('glyphicon-refresh');

                   }else {

                       $('.has-error').children('.help-block').html('');
                       $('.has-error').removeClass('has-error');

                       //refresh the token
                       $('#csrfToken').val(data.token);

                       $.each(data.errors, function(field, msg) {
                           $("#"+field).parent().addClass('has-error')
                           $(".err_"+field).html(msg);
                       });
                       $(".btndeleteprofilereasons span").removeClass('glyphicon-refresh');
                   }
               }
             });
         }
    });


    $("#other-reason").bind("paste keyup", function() {
        if ($("#optionOther").is(':checked')){
            //console.log("checked");
        } else{
            $( "#optionOther" ).click();
        }
    });



    //gather all the reasons selected and add it to the hidden field to submit
    function gatherReasons(){

        $("#reasons").val(""); //clear the reasons input
        $("#deleteprofilereasons :checkbox").each( function() {

            if ($(this).is(':checked')){
                var str_value = $(this).closest('label').children('span.casl').text();  //get span value
                var reasons =  $("#reasons").val();
                var new_reasons = "";
                if ($(this).attr("id") == "optionOther"){
                    new_reasons = reasons + $.trim(str_value) + $.trim($("#other-reason").val());
                }else{
                    new_reasons = reasons + $.trim(str_value);
                }

                $("#reasons").val(new_reasons);

            }

        });

    }

    function scrollToError() {

        var errorelem = $(".has-error").first();
        var errortop = errorelem.offset().top - $(".navbarcontainer").height();

        $('html, body').animate({
            scrollTop: errortop
        }, 500);
        focusinput = errorelem.children('input');
        focusinput.blur().focus().val(focusinput.val());
    }


      // My Account Custom Language Toggle
  // var lang_attr = $('.js-language-select').children("option").attr('value');
  var lang_attr = document.querySelectorAll(".js-language-select > [value]")
  var _index = 0;
  window.langue_options = [];
  Array.from(lang_attr).forEach( function (el) {
    var langAttrValue = el.getAttribute("value");
    window.langue_options[_index] = langAttrValue;
    _index++;
    //console.log(langAttrValue);
  });

  // var lang_attr=jQuery('.js-language-select').attr("data-landing-video");

  var x, i, j, l, ll, selElmnt, a, b, c;
  /*look for any elements with the class "custom-select":*/
  x = document.getElementsByClassName("custom-select");
  l = x.length;
  for (i = 0; i < l; i++) {
    selElmnt = x[i].getElementsByTagName("select")[0];
    ll = selElmnt.length;
    /*for each element, create a new DIV that will act as the selected item:*/
    a = document.createElement("DIV");
    a.setAttribute("class", "select-selected");
    a.innerHTML = selElmnt.options[selElmnt.selectedIndex].innerHTML;
    x[i].appendChild(a);
    /*for each element, create a new DIV that will contain the option list:*/
    b = document.createElement("DIV");
    b.setAttribute("class", "select-items select-hide js-language-select");
    b.setAttribute("id", "langToggle");
    for (j = 1; j < ll; j++) {
      /*for each option in the original select element,
      create a new DIV that will act as an option item:*/
      c = document.createElement("DIV");
      // c.setAttribute("data-language", 's');
      c.setAttribute("data-language", window.langue_options[j]);
      c.innerHTML = selElmnt.options[j].innerHTML;
      c.addEventListener("click", function(e) {
        /*when an item is clicked, update the original select box,
        and the selected item:*/
        var y, i, k, s, h, sl, yl;
        s = this.parentNode.parentNode.getElementsByTagName("select")[0];
        sl = s.length;
        h = this.parentNode.previousSibling;
        for (i = 0; i < sl; i++) {
          if (s.options[i].innerHTML == this.innerHTML) {
            s.selectedIndex = i;
            h.innerHTML = this.innerHTML;
            y = this.parentNode.getElementsByClassName("same-as-selected");
            yl = y.length;
            for (k = 0; k < yl; k++) {
              y[k].removeAttribute("class");
            }
            this.setAttribute("class", "same-as-selected");
            var lang_preference=jQuery('.same-as-selected').data("language");
            $(".js-language").val(lang_preference);
            break;
          }
        }
        h.click();
      });
      b.appendChild(c);
    }
    x[i].appendChild(b);
    a.addEventListener("click", function(e) {
      /*when the select box is clicked, close any other select boxes,
      and open/close the current select box:*/
      e.stopPropagation();
      closeAllSelect(this);
      this.nextSibling.classList.toggle("select-hide");
      this.classList.toggle("select-arrow-active");
    });
  }
  function closeAllSelect(elmnt) {
    /*a function that will close all select boxes in the document,
    except the current select box:*/
    var x, y, i, xl, yl, arrNo = [];
    x = document.getElementsByClassName("select-items");
    y = document.getElementsByClassName("select-selected");
    xl = x.length;
    yl = y.length;
    for (i = 0; i < yl; i++) {
      if (elmnt == y[i]) {
        arrNo.push(i)
      } else {
        y[i].classList.remove("select-arrow-active");
      }
    }
    for (i = 0; i < xl; i++) {
      if (arrNo.indexOf(i)) {
        x[i].classList.add("select-hide");
      }
    }
  }
  /*if the user clicks anywhere outside the select box,
  then close all select boxes:*/
  document.addEventListener("click", closeAllSelect);


  /* Update Addresses */

  $(".edit-address-js").on('click', function(event) {
      event.preventDefault();
      $('.address-display').show();
      $('.address-edit').hide();
      $('.new-address-edit').hide()
      var elem = '.upd-addr-' + $(this).data( "idx" );
      container = $(this).parents(elem);
      container.children('.address-display').hide()
      container.children('.address-edit').show()
    }
  );

  $(".cancel-address-js").on('click', function(event) {
    event.preventDefault();
      container = $(this).parents('.address-container');
      container.find('.address-display').show()
      container.find('.address-edit').hide()
      $('.new-address-edit').hide()
    }
  );

  $(".add-address-js").on('click', function(event) {
      event.preventDefault();
      ErrorAddressFormClear();
      $('.new-address-label').hide()
      $('.new-address-edit').show()
    }
  );

  $(".cancel-address-new-js").on('click', function(event) {
    event.preventDefault();
      $('.new-address-label').show()
      $('.new-address-edit').hide()
    }
  );

  // $(".delete-address-cancel-js").on('click', function(event) {
  //   event.preventDefault();
  //   togglePopup();
  //   }
  // );




  $(".save-address-js").on('click', function(event) {
      event.preventDefault();
      
      ErrorAddressFormClear();
      //container = $(this).parents('.address-container');
      container = $(this).parents("form");
      data = container.serialize();
      isvalid = validateAddressForm(container);
      if (isvalid){
        showSpinner();
        $.ajax({
          url:"/" + language + "/pepsi/editaddress/ajaxaction",
          type: "POST",
          data:  data,
          success:function(data) {
              if (data.status){
                  window.location.href = data.route;
              }
              else {
                ErrorAddressFormSet(data.errors.form)
                hideSpinner();
                //console.log("error");
                //console.log(data);
                AddAddressError(container,data.errors)
              }
          }
        });
      }

    }
  );

  function AddAddressError(container,errors){
    //console.log("EERRR")
    //console.log(errors)
    $.each( errors, function( key, value ) {
      //console.log( key);
      //console.log( value);
      container.find('#'+key+'_adr').addClass('has-error-adr');
      container.find('.err_'+key+'_adr').html(value);

    });

  }

  $(".primary-address-js").on('click', function(event) {
      event.preventDefault();
      var elem = '.edit-adr-form-' + $(this).data( "idx" );
      container = $(elem);
      
      if ($(this).attr("checked") == undefined){
        container.find("#primaryAddr_adr").val("1"); //Setting as primary
        data = container.serialize();
        showSpinner();
        $.ajax({
          url:"/" + language + "/pepsi/editaddress/ajaxaction",
          type: "POST",
          data:  data,
          success:function(data) {
              if (data.status){
                  window.location.href = data.route;
              }
              else {
                hideSpinner();
                //console.log('error');
                //console.log(data);
              }
          }
        });
      }
    }
  );

  $(".delete-address-js").on('click', function(event) {
      event.preventDefault();
      var elem = '.edit-adr-form-' + $(this).data( "idx" );
      container = $(elem);
      adrtodelete = { "address-id" : container.find("#address-id").val()}
      showSpinner();
      $.ajax({
          url:"/" + language + "/pepsi/removeaddress/ajaxaction",
          type: "POST",
          data:  adrtodelete,
          success:function(data) {
              if (data.status){
                  window.location.href = data.route;
              }
              else {
                hideSpinner();
                //console.log('error');
                //console.log(data);
              }
          }
      });
    }
  );

  function validateAddressForm(editaddress){

    fname = editaddress.find('#firstname_adr');
    lname = editaddress.find('#lastname_adr');
    addr1 = editaddress.find('#address1_adr'); 
    addr2 = editaddress.find('#address2_adr');
    city  = editaddress.find('#city_adr');
    state = editaddress.find('#state_adr');
    zip   = editaddress.find('#zip_adr');

    fname.removeClass('has-error-adr'); fname.val(fname.val().trim());
    lname.removeClass('has-error-adr'); lname.val(lname.val().trim());
    addr1.removeClass('has-error-adr'); addr1.val(addr1.val().trim());
    addr2.removeClass('has-error-adr'); addr2.val(addr2.val().trim());
    city.removeClass('has-error-adr'); city.val(city.val().trim());
    state.removeClass('has-error-adr'); state.val(state.val().trim());
    zip.removeClass('has-error-adr'); zip.val(zip.val().trim());

    $('span.help-block').html('');

    var is_valid = true

    if (fname.val() == '') {
      fname.addClass('has-error-adr')
      is_valid = false;
    }

    if (lname.val() == '') {
      lname.addClass('has-error-adr')
      is_valid = false;
    }

    if (addr1.val() == '') {
      addr1.addClass('has-error-adr')
      is_valid = false;
    }

    if (city.val() == '') {
      city.addClass('has-error-adr')
      is_valid = false;
    }

    if (state.val() == '') {
      state.addClass('has-error-adr')
      is_valid = false;
    }

    if (zip.val() == '') {
      zip.addClass('has-error-adr')
      is_valid = false;
    }

    return is_valid;
  }

  function ErrorAddressFormClear(){
    $('.general_error').html('');
  }

  function ErrorAddressFormSet(msg){
    $('.general_error').html(msg);
  }

    $(".delete-address").click(function(event){
      // console.log($(this).parent().parent().children('.delete-content'));
      event.preventDefault();
      $(this).parent().parent().children('.delete-content').toggle();
    });

  $(".close").click(function(){
    $(this).parent().parent().children('.delete-content').toggle();
  });

  $(".delete-address-cancel-js").click(function(event){
    event.preventDefault();
    $(this).parent().parent().parent().children('.delete-content').toggle();
  });

  var placeSearch, autocomplete;
  var componentForm = {
          street_number: 'short_name',
          route: 'long_name',
          locality: 'long_name',
          administrative_area_level_1: 'short_name',
          country: 'long_name',
          postal_code: 'short_name'
      };
  var componentFormMaping = {
          street_number: '',
          route: '',
          locality: '',
          administrative_area_level_1: '',
          country: '',
          postal_code: ''
  };


  function initAutocomplete() {

          var acInputs = document.getElementsByClassName("autocomplete");

          const options = {
              componentRestrictions: { country: "us" },
              fields: ["address_components"],
              strictBounds: false
          };

          for (var i = 0; i < acInputs.length; i++) {

              var autocomplete = new google.maps.places.Autocomplete(acInputs[i],options);
              //autocomplete.inputId = acInputs[i].id;
              autocomplete.inputId = acInputs[i].dataset.ndr;

              google.maps.event.addListener(autocomplete, 'place_changed',fillInAddress);
          }
  }

  function fillInAddress() {
      componentFormMaping = {
              street_number: '',
              route: '',
              locality: '',
              administrative_area_level_1: '',
              country: '',
              postal_code: ''
      };

      formedit = jQuery("#form-edit-"+ this.inputId);
      var place = this.getPlace()
      for (var i = 0; i < place.address_components.length; i++) {
          var addressType = place.address_components[i].types[0];
          if (componentForm[addressType]) {
              var val = place.address_components[i][componentForm[addressType]];
              componentFormMaping[addressType] = val;
          }
      }
          
      formedit.find('#address1_adr').val(componentFormMaping['street_number'] + ' ' + componentFormMaping['route']); 
      formedit.find('#city_adr').val(componentFormMaping['locality']);
      formedit.find('#state_adr').val(componentFormMaping['administrative_area_level_1']);
      formedit.find('#zip_adr').val(componentFormMaping['postal_code']);
  }

  initAutocomplete();

}(jQuery, Drupal, window));
