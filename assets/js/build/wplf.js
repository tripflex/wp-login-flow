jQuery(function($){
   $( '.wplf-color-picker' ).wpColorPicker();
});
var wplf = {
	repeatable: {
		init: function(){
			var body = $( 'body' );

			body.on( 'click', '.repeatable-add-group-row-button', function () {

				         var clicked = $( this );
				         var rowtemplate = clicked.data( 'rowtemplate' );
				         var group = clicked.data( 'group' );
						 var template = $( '#' + rowtemplate ).html();

						 // console.log( clicked, rowtemplate, group, template );

						 template.replace( /{{id}}/g, 'i_rowID' );
			             // template = template.replace( /{{label_option_value}}/g, 'option_value' ),
			             // template = template.replace( /{{label_option_label}}/g, 'option_label' ),
			             // template = template.replace( /{{label_option_default}}/g, 'option_default_label' ),
			             // template = template.replace( /{{label_option_disabled}}/g, 'option_disabled_label' ),
			             // template = template.replace( /{{style_option_default}}/g, 'option_default' ),
			             // template = template.replace( /{{style_option_disabled}}/g, 'option_disabled' );

				         if ( clicked.data( 'field' ) ) {
					         var ref = clicked.data( 'field' ).split( '-' );
					         template = template.replace( /\_\_i\_\_/g, ref[ ref.length - 2 ] );
				         }

				         template = template.replace( /\_\_count\_\_/g, clicked.parent().parent().find( '.' + group + '-groupitems' ).length );
				         $( '#repeatable-' + group + '-form' ).append( template );

				         wplf.repeatable.sortCreate( group );
			         }
			);

			body.on( 'click', '.repeatable-fields-remove-group-row', function () {
				var group = $(this).data( 'group' );
		         $( this ).closest( 'table' ).remove();
				wplf.repeatable.reIndex( group );
	         }
			);

			$('.wplf-repeatable-form').each( function(){
				var group = $(this).data( 'group' );
				wplf.repeatable.sortCreate( group );

				var single_val_field = $(this).data('singleval');
				if( single_val_field ){
					console.log( 'single val field ', single_val_field );
				}
			});
		},
		sortCreate: function (group) {
			var $f = $( '#repeatable-' + group + '-form' );

			// wplf.repeatable.sortDestroy( group ).sortable( {
			$f.sortable( {
				                                                  animation: 150,
				                                                  handle   : '.' + group + '-repeatable-fields-handle',
				                                                  onEnd    : function ( evt ) {
					                                                  wplf.repeatable.reIndex(group);
				                                                  }
			                                                  } );

		},

		sortDestroy: function (group) {
			var $f = $( '#repeatable-' + group + '-form' );
			console.log( group, $f );
			return $f.sortable( 'destroy' );
		},
		reIndex: function (group) {
			console.log( 'REINDEX ', group );

			$( '.' + group + '-groupitems' ).each( function ( table_index ) {

				$( this ).find( "[name^='" + group +  "[']" ).each( function ( option_index ) {
					var option_name = $( this ).attr( 'name' );
					console.log( 'FOUND REINDEX GROUP NAME', option_name, table_index, option_index );
					if ( !option_name ) return;

					// Replace old index with new index value
					var new_option_name = option_name.replace( /\[\d+]/, '[' + table_index + ']' );

					// Set value on element
					$( this ).attr( 'name', new_option_name );
				} )

			} );

		},
	}
};

jQuery( function($){
	wplf.repeatable.init();
});
jQuery(function($){
    var css_editor = false;

    if ( typeof wplf_css_codeeditor !== "undefined" ) {
        css_editor = wp.codeEditor.initialize( $( '#wplf_custom_css' ), wplf_css_codeeditor );
    }

    $( ".nav-tab-wrapper a" ).click(
        function () {
            $( '.settings_panel' ).hide();
            $( '.nav-tab-active' ).removeClass( 'nav-tab-active wp-ui-primary' ).css( 'color', '#555555' ).css( 'background', '#e4e4e4' );
            $( $( this ).attr( 'href' ) ).show();
            var bg_color = $( '.wp-ui-primary' ).getHexBackgroundColor();
            $( this ).addClass( 'nav-tab-active' ).css( 'background', bg_color );
            $( '#wplf-all-settings .settings_panel' ).css( 'border', '' );
            if( $(this).data('tab') === 'custom_page' && css_editor ){
                css_editor.codemirror.refresh();
            }
            return false;
        }
    );

    $( ".nav-tab-wrapper a" ).hover(
        function () {
            var bg_color = $( '.wp-ui-primary' ).getHexBackgroundColor();
            $( this ).css( 'color', '#FFFFFF' ).css( 'background', bg_color );
        },
        function () {
            if( ! $( this ).hasClass( 'nav-tab-active' ) ) $( this ).css( 'color', '#555555' ).css( 'background', '#e4e4e4' );
        }
    );

    $( '.nav-tab-wrapper a:first' ).click();

    $( '#wplf_register_set_pw' ).click( function(){
	    if ( $( this ).is( ':checked' ) ) {
		    $( '#wplf_require_activation' ).prop( 'checked', false );
	    }
    });

    $( '#wplf_require_activation' ).click( function(){

    	if( $(this).is(':checked') ){
		    $( '#wplf_register_set_pw' ).prop( 'checked', false );
	    }

    });


});

jQuery.fn.getHexBackgroundColor = function () {
    var rgb = jQuery( this ).css( 'background-color' );
    if ( ! rgb ) {
        return '#FFFFFF'; //default color
    }
    var hex_rgb = rgb.match( /^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/ );

    function hex( x ) {return ("0" + parseInt( x ).toString( 16 )).slice( - 2 );}

    if ( hex_rgb ) {
        return "#" + hex( hex_rgb[ 1 ] ) + hex( hex_rgb[ 2 ] ) + hex( hex_rgb[ 3 ] );
    } else {
        return rgb;
    }
}
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