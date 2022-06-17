(function ($, Drupal, window) {

	$(document).ready(function() {


    $(".slick").not('.slick-initialized').slick({
      dots: false,
      infinite: true,
      slidesToShow: 4,
      slidesToScroll: 4,
      autoplaySpeed: 2000,
      arrows:true,
      prevArrow:"<button type='button' class='slick-prev pull-left'><i class='fa fa-angle-left' aria-hidden='true'></i></button>",
      nextArrow:"<button type='button' class='slick-next pull-right'><i class='fa fa-angle-right' aria-hidden='true'></i></button>",
      pauseOnHover: true,

      responsive: [
        {
          breakpoint: 1024,
          settings: {
            slidesToShow: 1,
            slidesToScroll: 1,
            arrows: true,
            autoplay: false
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

    $('.mario-party-js').click(function(event) {
      event.preventDefault();
      $('.nintendo-contest-game').addClass('d-none');
      $('.carousel').removeClass('d-none');
      $('.kirby-game').removeClass('active');
      $('.pokemon-game').removeClass('active');
      $('.mariokart-game').removeClass('active');
      $('.mario-game').addClass('active');
      $('.nintendo-contest-participating.pick-game').addClass('description-padding');
    })

    $('.kirby-btn-js').click(function(event) {
      event.preventDefault();
      $('.nintendo-contest-game').addClass('d-none');
      $('.carousel').removeClass('d-none');
      $('.mario-game').removeClass('active');
      $('.pokemon-game').removeClass('active');
      $('.mariokart-game').removeClass('active');
      $('.kirby-game').addClass('active');
      $('.nintendo-contest-participating.pick-game').addClass('description-padding');
    })

    $('.pokemon-btn-js').click(function(event) {
      event.preventDefault();
      $('.nintendo-contest-game').addClass('d-none');
      $('.carousel').removeClass('d-none');
      $('.mario-game').removeClass('active');
      $('.pokemon-game').addClass('active');
      $('.mariokart-game').removeClass('active');
      $('.kirby-game').removeClass('active');
      $('.nintendo-contest-participating.pick-game').addClass('description-padding');
    })

    $('.mariokart-btn-js').click(function(event) {
      event.preventDefault();
      $('.nintendo-contest-game').addClass('d-none');
      $('.carousel').removeClass('d-none');
      $('.mario-game').removeClass('active');
      $('.kirby-game').removeClass('active');
      $('.pokemon-game').removeClass('active');
      $('.mariokart-game').addClass('active');
      $('.nintendo-contest-participating.pick-game').addClass('description-padding');
    })

    $('.back-link').click(function(event)  {
      event.preventDefault();
      $('.nintendo-contest-game').removeClass('d-none');
      $('.carousel').addClass('d-none');
    })



  });





}(jQuery, Drupal, window));
