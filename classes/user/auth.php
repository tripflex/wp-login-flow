<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Login_Flow_User_Auth extends WP_Login_Flow_User {


	function __construct() {

		// Run at priority 21+ -- after WordPress runs filter for wp_authenticate_username_password and wp_authenticate_email_password
		add_action( 'authenticate', array( $this, 'check' ), 30, 3 );
	}

	/**
	 * Check if user is pending activation on login attempt
	 *
	 *
	 * @param $user
	 * @param $username
	 * @param $password
	 *
	 * @return WP_Error
	 */
	function check( $user, $username, $password ) {

		/**
		 * When user successfully logs in, $user will not be null or wp_error, as the filters in
		 * wp_authenticate_username_password and wp_authenticate_email_password are ran at a lower
		 * priority than this fn is called in.
		 */
		if ( ! empty( $user ) && ! is_wp_error( $user ) ) {
			// Allow user to login with username/pw is correct (regardless of activation state)
			return $user;
		}

		if ( strpos( $username, '@' ) ) {
			$user_data = get_user_by( 'email', trim( $username ) );
		} else {
			$login     = trim( $username );
			$user_data = get_user_by( 'login', $login );
		}

		if( ! $user_data ) return $user;

		$user_id = $user_data->ID;
		if ( ! $this->activation()->check( $user_id ) ) {
			$user           = new WP_Error();
			$pending_notice = sprintf( __( '<strong>ERROR</strong>: Your account is still pending activation, please check your email, or you can request a <a href="%s">password reset</a> for a new activation code.' ), '%wp_lost_pw_url%' );
			$template       = new WP_Login_Flow_Template();
			$notice         = $template->generate( 'wplf_notice_activation_pending', $pending_notice );
			$user->add( 'pendingactivation', $notice );
		}
		return $user;
	}

}