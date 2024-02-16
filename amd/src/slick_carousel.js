define(['jquery', 'slick'],
    function($) {
        return {
        'init': function(carouselId) {
            console.log("Initializing slick carousel")
            $(carouselId).slick({
                dots: true,
                dotsClass: 'slick-dots',
                infinite: true,
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

            // Fix height for cards.
            carouselId = carouselId.substring(1);
            var carousel = document.getElementById(carouselId)
            var cards = carousel.getElementsByClassName('card')
            var height = 0
            var heightPx = ""

            // Fix carousel width inside text labels.
            var labelFix = carousel.parentElement.parentElement.parentElement.parentElement
            if (labelFix) {
                if (labelFix.classList.contains('description-inner')) {
                    if (!labelFix.classList.contains('w-100')) {
                        console.log('Fixing width inside labels for slick-carousel')
                        labelFix.classList.add('w-100')
                        labelFix.parentElement.classList.add('p-0')
                    }
                }
            }

            // Resize card height.
            if (cards) {
                // Get max height.
                for (var i = 0; i < cards.length; i++) {
                    if (cards[i].offsetHeight > height) {
                        cards[i].style.minHeight = 0;
                        height = cards[i].offsetHeight
                    }
                }
                // Set min height.
                heightPx = height + 'px'
                for (var i = 0; i < cards.length; i++) {
                    cards[i].style.minHeight = heightPx;
                }
            }

            // Fix carousel height inside text labels.
            var totalHeight = height + 70
            var totalHeightPx = totalHeight + "px"
            carousel.parentElement.style.height = totalHeightPx

            // Add total number of items to dots.
            var dots = carousel.getElementsByClassName("slick-dots")[0]
            if (dots) {
                if (dots.getElementsByTagName("li")) {
                    var totalItems =  dots.getElementsByTagName("li").length
                    var totalElement = document.createElement("li")
                    totalElement.innerHTML = totalItems
                    totalElement.classList.add("dots-total")
                    dots.appendChild(totalElement)
                }
            }

            // Show finished slideshow.
            carousel.parentElement.classList.remove('invisible')

        }

        };
    }
)