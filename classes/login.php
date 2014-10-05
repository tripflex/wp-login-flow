<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Login_Flow_Login extends WP_Login_Flow {


	function __construct() {

		add_action( 'authenticate', array( $this, 'login_check_activation' ), 30, 3 );
		add_action( 'set_auth_cookie', array( $this, 'set_auth_cookie' ), 20, 5 );
		add_filter( 'wp_login_errors', array( $this, 'wp_login_errors' ), 10, 2 );

		$activation = new WP_Login_Flow_User_Activation();

	}

	/**
	 * @param $auth_cookie
	 * @param $expire
	 * @param $expiration
	 * @param $user_id
	 * @param $scheme
	 */
	public function set_auth_cookie( $auth_cookie, $expire, $expiration, $user_id, $scheme ) {


		// Exit function is user is already activated
		if ( ! $this->auth->check( $user_id ) ) {
			wp_redirect( home_url( $this->wp_login . '?registration=complete&activation=pending' ) );
			exit();
		}

	}

	/**
	 * @param $user
	 * @param $username
	 * @param $password
	 *
	 * @return WP_Error
	 */
	public function login_check_activation( $user, $username, $password ) {

		if ( strpos( $username, '@' ) ) {
			$user_data = get_user_by( 'email', trim( $username ) );
		} else {
			$login     = trim( $username );
			$user_data = get_user_by( 'login', $login );
		}

		$user_id = $user_data->ID;

		if ( ! WP_Login_Flow::is_activated( $user_id ) ) {
			$user = new WP_Error();
			$user->add( 'pendingactivation', self::get_locale_pending_activation_notice() );
		}

		return $user;

	}

}