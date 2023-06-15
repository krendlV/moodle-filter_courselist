define(['resize_cards'], function() {

    var SlickResizer = function(slideshow) {                                
        this.resize(slideshow)             
        this.show(slideshow)  
    };

    SlickResizer.prototype.resize = function(slideshow) {        
    
        // Fix height for cards.
        var cards = slideshow.getElementsByClassName('card')            
        var height = 0    
        var heightPx = ""

        // Fix carousel width inside text labels.
        var labelFix = slideshow.parentElement.parentElement.parentElement.parentElement
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
        var totalHeight = height + 100            
        var totalHeightPx = totalHeight + "px"                
        slideshow.parentElement.style.height = totalHeightPx   

    }

    
    SlickResizer.prototype.show = function(slideshow) {        
        slideshow.parentElement.classList.remove('invisible')        
    };
    
    return {
        'init': function(slideshow) {                        
            return new SlickResizer(slideshow);
        }
    };
})