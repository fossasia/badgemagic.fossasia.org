jQuery(document).ready(function ($) {
	if($(".loklakAPI").prop('checked')) {
		stt_update_twitter_auth(true);
	}

	$(".loklakAPI").live('change', function() {
		if($(this).is(':checked')){
	    	stt_update_twitter_auth(true);
	    }
	    else {
	    	stt_update_twitter_auth(false);
	    }
	});

	function stt_update_twitter_auth(arg) {
		if (arg == true) {
	    	$(".consumerKey").prop('disabled', arg);
			$(".consumerSecret").prop('disabled', arg);
			$(".accessToken").prop('disabled', arg);
			$(".accessTokenSecret").prop('disabled', arg);
		}
	    else {
	    	$(".consumerKey").prop('disabled', arg);
			$(".consumerSecret").prop('disabled', arg);
			$(".accessToken").prop('disabled', arg);
			$(".accessTokenSecret").prop('disabled', arg);
	    }
	}
	
});