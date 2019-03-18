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