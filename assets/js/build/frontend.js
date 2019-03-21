jQuery( function ( $ ) {
	$.fn.isValid = function () {
		return document.getElementById( this[ 0 ].id ).checkValidity();
	};

	$( '#login #wp-submit' ).click( function ( e ) {
		var form = $(this).closest('form');

		if ( form && form.length > 0 && form.isValid() ) {
			$( '#wplf-loader' ).show();
			// Same ID for login/register/lostpw/etc
			$( '#login' ).css( 'opacity', '0.5' );
		}

	});

});