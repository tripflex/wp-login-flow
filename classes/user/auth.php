<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Login_Flow_User_Auth extends WP_Login_Flow_User {


	function __construct() {

		add_action( 'authenticate', array( $this, 'check' ), 30, 3 );
		add_action( 'set_auth_cookie', array( $this, 'attempt_login' ), 20, 5 );
	}

	/**
	 * Redirect to pending activation
	 *
	 * If the user's account is still pending activation, this method
	 * will redirect to the pending activation page when core WordPress
	 * attempts to set the authorization cookie (if supplied password is correct).
	 *
	 * @param $auth_cookie
	 * @param $expire
	 * @param $expiration
	 * @param $user_id
	 * @param $scheme
	 */
	public function attempt_login( $auth_cookie, $expire, $expiration, $user_id, $scheme ) {

		// Exit function is user is already activated
		if ( ! $this->activation()->check( $user_id ) ) {
			wp_redirect( wp_login_url() . '?registration=complete&activation=pending' );
			exit();
		}

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