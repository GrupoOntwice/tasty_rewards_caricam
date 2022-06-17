(function ($, window) {

  $(document).ready(function(){
    $('.slick').slick({
      dots: true,
      infinite: true,
      speed: 300,
      slidesToShow: 3,
      slidesToScroll: 1,
      arrows: true,
      prevArrow:"<button type='button' class='slick-prev pull-left'><i class='fa fa-angle-left' aria-hidden='true'></i></button>",
      nextArrow:"<button type='button' class='slick-next pull-right'><i class='fa fa-angle-right' aria-hidden='true'></i></button>",
      responsive: [
        {
          breakpoint: 1024,
          settings: {
            slidesToShow: 1,
            slidesToScroll: 1,
            infinite: true,
            arrows: true,
            dots: true
          }
        },
        {
          breakpoint: 600,
          settings: {
            slidesToShow: 1,
            slidesToScroll: 1,
            arrows: true,
          }
        },
        {
          breakpoint: 480,
          settings: {
            slidesToShow: 1,
            slidesToScroll: 1,
            arrows: true,
          }
        }
      ]
    });
    $('#bannerCarousel').on('slide.bs.carousel', function() {
      currentIndex = $('.video.active').index() + 1;
      if(currentIndex == 0) {
        setTimeout(function () {
          $(".play").removeClass("d-none").delay(300);
        }, 300);
      }
      else {
        setTimeout(function () {
          $(".play").addClass("d-none").delay(300);
        }, 300);
      }
    });

    $('.play').click(function(){
      $('.modal').addClass('index');
      $('#video-missvickies').trigger('play');
    });

    $('.video-close').click(function(){
      $('#video-missvickies').trigger('pause');
    });

  });

  $(".js-flavour-slider").change(function(){

      let sweet_salty = $("#sweet_salty").val();
      let tangy_spicy = $("#tangy_spicy").val();
      let id = this.id;

        $.ajax({
            url:"/missvickies/findflavour",
            type: "POST",
            data: {"tangy_spicy": tangy_spicy, 'sweet_salty': sweet_salty, 'id':id},
            success:function(json) {
                updateMatchedImage(json);

            }
          });

  });

  function updateMatchedImage(json){
    $(".js-flavourfinder .js-flavour-link").prop('href', json.product.link);
    $(".js-flavourfinder .js-flavour-img").prop('src', json.product.image_url);
    $(".js-flavourfinder .js-flavour-img").prop('alt', json.product.image_alt);
    $(".js-flavourfinder .js-flavour-title").text(json.product.title);
  }

  $('.play').click(function() {
    $('.modal').toggleClass('index');
  })

  $('.video-close').click(function() {
    $('.modal').toggleClass('index');
  })

  $('.buynow-btn').click(function() {
    $('.missvickies-navbar__vertical-align').toggleClass('nav-index');
  })


  $('.buynow-close').click(function() {
    $('.missvickies-navbar__vertical-align').toggleClass('nav-index');
  })

}(jQuery, window));/*end of file*/
