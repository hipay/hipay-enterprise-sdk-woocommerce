jQuery(function ($) {
	function hide_message_for_multibanco(){
		if ( $("#hipayHF-container-multibanco").length ) {
			$("#hipayHF-container-multibanco").hide();
		}
	}
	
	window.setTimeout( hide_message_for_multibanco, 1000 );
});

