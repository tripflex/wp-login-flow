<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Login_Flow_User_Activation extends WP_Login_Flow_User {



	function __construct() {

	}

	function check( $user_id ) {

		$status = get_user_option( $this->status_option, $user_id );

		if ( $status == 'pending' ) return false;

		return true;
	}

}