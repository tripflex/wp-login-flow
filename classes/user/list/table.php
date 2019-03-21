<?php

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class WP_Login_Flow_User_List_Table
 *
 * @since @@version
 *
 */
class WP_Login_Flow_User_List_Table extends WP_Login_Flow_User {


	/**
	 * WP_Login_Flow_User_List_Table constructor.
	 */
	function __construct() {

		add_filter( 'manage_users_columns', array( $this, 'add_column' ) );
		add_action( 'manage_users_custom_column', array( $this, 'column_output' ), 10, 3 );
		// add_filter( 'user_row_actions', array( $this, 'row_actions' ), 10, 2 );
	}

	/**
	 *
	 *
	 *
	 * @param array    $actions
	 * @param \WP_User $user
	 *
	 * @return array
	 * @since @@version
	 *
	 */
	function row_actions( array $actions, WP_User $user ){

		$link                   = admin_url( 'users.php?page=wp-login-flow&activation=toggle&user_id=' . $user->ID );
		$actions[ 'toggle_activation' ] = '<a href="' . $link . '">' . __( 'Toggle Activation' ) . '</a>';

		return $actions;
	}

	/**
	 *
	 *
	 *
	 * @param $columns
	 *
	 * @return mixed
	 * @since @@version
	 *
	 */
	function add_column( $columns ){

		$columns['activation_status'] = __('Activation Status');

		return $columns;
	}

	/**
	 *
	 *
	 *
	 * @param $value
	 * @param $column_name
	 * @param $user_id
	 *
	 * @return string
	 * @since @@version
	 *
	 */
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