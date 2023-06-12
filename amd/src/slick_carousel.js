define(['jquery', 'slick', 'slick_carousel'],
  function($) {
    return {
      'init': function() {
        $('.responsive').slick({
            dots: true,
            infinite: false,
            speed: 300,
            slidesToShow: 5,
            slidesToScroll: 4,
            responsive: [
                {
                breakpoint: 2000,
                settings: {
                    slidesToShow: 4,
                    slidesToScroll: 3,
                    infinite: true,
                    dots: true
                }
                },
                {
                breakpoint: 1600,
                settings: {
                    slidesToShow: 3,
                    slidesToScroll: 2,
                    infinite: true,
                    dots: true
                }
                },
                {
                breakpoint: 1200,
                settings: {
                    slidesToShow: 2,
                    slidesToScroll: 1
                }
                },
                {
                breakpoint: 991,
                settings: {
                    slidesToShow: 1,
                    slidesToScroll: 1
                }
                },  
            ]
        });        
      }
    };  
  }
)