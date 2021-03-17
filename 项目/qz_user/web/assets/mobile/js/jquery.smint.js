/*

SMINT V1.0 by Robert McCracken

SMINT is my first dabble into jQuery plugins!

http://www.outyear.co.uk/smint/

If you like Smint, or have suggestions on how it could be improved, send me a tweet @rabmyself

*/
(function(){

	
	$.fn.smint = function( options ) {

		// adding a class to users div
		$(this).addClass('smint')

		var settings = $.extend({
            'scrollSpeed '  : 200
            }, options);


		return $('.smint').each( function() {

            
			if ( settings.scrollSpeed ) {

				var scrollSpeed = settings.scrollSpeed

			}


			///////////////////////////////////

			// get initial top offset for the menu 
			var stickyTop = $('.smint').offset().top;	

			// check position and make sticky if needed
			var stickyMenu = function(){
				
				// current distance top
				var scrollTop = $(window).scrollTop(); 
							
				// if we scroll more than the navigation, change its position to fixed and add class 'fxd', otherwise change it back to absolute and remove the class
				if (scrollTop > stickyTop) { 
					$('.smint').addClass('fxd');

					} else {
						$('.smint').removeClass('fxd'); 
					}   
			};
					
			// run function
			stickyMenu();
					
			// run function every time you scroll
			$(window).scroll(function() {
				 stickyMenu();
			});

			///////////////////////////////////////
    
        
        	

            

		});

	}

})();
