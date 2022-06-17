(function ($, window) {

  function loadSocial(){
    console.log("Load Social");
    $.ajax({
      'url': Drupal.url('brands/lays/socialfeeds'),
      //'async': false,
      'success': function (response)  {
        console.log(response);
        $('.lays-social-wall').html('');
        $('.lays-social-wall').append(response);
        /*
        if (response[3] !== undefined) {
          var viewHtml = response[3].data;
          // Remove previous articles and add the new ones.
          console.log(viewHtml);
          $('.lays-social-content-wrapper').html('');
          $('.lays-social-content-wrapper').append(viewHtml);
         
          // Attach Latest settings to the behaviours and settings. 
          // it will prevent the ajax pager issue
          Drupal.settings = response[0].settings;
          drupalSettings.views = response[0].settings.views;
          Drupal.attachBehaviors($('.lays-social-content-wrapper')[0], Drupal.settings);
        }
        */
        }
      }
    );

  }
  loadSocial();

}(jQuery, window));/*end of file*/