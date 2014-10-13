<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Login_Flow_User_Activation extends WP_Login_Flow_User {


	function __construct() {

	}

	function check( $user_id ) {

		$status = get_user_option( 'activation_status', $user_id );

		if ( ! empty( $status ) ) return false;

		return true;
	}

	/**
	 * @param      $user_id
	 * @param int  $activated
	 */
	public static function set( $user_id, $activated = 1 ) {

		update_user_option( $user_id, 'activation_status', $activated, TRUE );

	}

}