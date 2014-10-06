// Uploading files
var file_frame;

jQuery(function ( $ ) {
	$( '.wplf-upload-show' ).live( 'click', function ( event ) {

			event.preventDefault();
			var fileInput = $( this ).data( 'name' );
			console.log( fileInput );

			// If the media frame already exists, reopen it.
			if ( file_frame ) {
				file_frame.open();
				return;
			}

			// Create the media frame.
			file_frame = wp.media.frames.file_frame = wp.media({
                title: $( this ).data( 'title' ),
                library: {
                    type: 'image'
                },
                button: {
                    text: $( this ).data( 'button' )
                },
                multiple: false
            });

			// When an image is selected, run a callback.
			file_frame.on( 'select', function () {
                // We set multiple to false so only get one image from the uploader
                var attachment = file_frame.state().get( 'selection' ).first().toJSON();
                console.log( attachment );
                $( '#' + fileInput ).val( attachment.url );
                $( '#' + fileInput + '-img' ).prop( 'src', attachment.url );
				$( '#' + fileInput + '-ul' ).show();
            });

			// Finally, open the modal
			file_frame.open();
		}
	);

	$( '.wpjm-upload-remove' ).click( function(e){
		e.preventDefault();
		var fileInput = $( this ).data( 'name' );

		$( '#' + fileInput ).val('');
		$( '#' + fileInput + '-ul' ).hide();

	});

});