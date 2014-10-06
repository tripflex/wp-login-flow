jQuery(function($){
    $( ".nav-tab-wrapper a" ).click(
        function () {
            $( '.settings_panel' ).hide();
            $( '.nav-tab-active' ).removeClass( 'nav-tab-active wp-ui-primary' );
            $( $( this ).attr( 'href' ) ).show();
            var bg_color = $( '.wp-ui-primary' ).css( 'background' );
            $( this ).addClass( 'nav-tab-active' ).css( 'background', bg_color );
            $( '#wplf-all-settings .settings_panel' ).css( 'border', '' )
            return false;
        }
    );
    $( '.nav-tab-wrapper a:first' ).click();
});