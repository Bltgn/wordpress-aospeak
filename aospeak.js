function aospeak_request( target, mode, dim, org ) {
	
	// Request parameters
	request = {
		mode: mode,
		dim: dim,
		org: org
	};
	
	// Request for data
	jQuery.getJSON( aospeak_setup.url, request, function( data ) {
		jQuery("#" + target + " .aospeak").html( data.html )
	}).error( jQuery("#" + target + " .aospeak").html( "An error occured, please try again later." ) );
	
}