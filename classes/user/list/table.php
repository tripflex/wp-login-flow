<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Login_Flow_User_List_Table extends WP_Login_Flow_User {


	function __construct() {

		add_filter( 'manage_users_columns', array( $this, 'add_column' ) );
		add_action( 'manage_users_custom_column', array( $this, 'column_output' ), 10, 3 );
	}

	function add_column( $columns ){

		$columns['activation_status'] = __('Activation Status');

		return $columns;
	}

	function column_output( $value, $column_name, $user_id ){

		// Return passed value if this is not the activation_status column
		if( $column_name !== 'activation_status' ) return $value;

		$activated = $this->activation()->check( $user_id, true );

		if ($activated === 0) return '<span title="' . __( 'Existing User Requires Activation' ) . '" class="dashicons dashicons-minus"></span>';

		if( $activated ) {
			return '<span title="' . __( 'Activated' ) . '" class="dashicons dashicons-yes"></span>';
		} else {
			return '<span title="' . __( 'Requires Activation' ) . '" class="dashicons dashicons-no-alt"></span>';
		}

	}

}