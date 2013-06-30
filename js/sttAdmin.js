jQuery(document).ready(function($) {

	$('.twitterFollow > div').hide();

	$('.twitterFollow h4').live('click', function() {

		var tFollow = $(this).next('div');
	    tFollow.slideToggle('fast', function(){
	    
		    if(!$(this).is(":hidden")) {
	    		$('.twitterFollow h4').children("span").html("&#9650;");
			}else{
				$('.twitterFollow h4').children("span").html("&#9660;");
			}
	    	
	    });
	});

});