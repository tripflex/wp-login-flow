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