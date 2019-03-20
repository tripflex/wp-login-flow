<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WP_Login_Flow_Redirects
 *
 * @since @@version
 *
 */
class WP_Login_Flow_Redirects {

	/**
	 * WP_Login_Flow_Redirects constructor.
	 */
	function __construct() {
		add_filter( 'login_redirect', array( $this, 'get_user_login_redirect' ), 999999, 3 );
		add_filter( 'logout_redirect', array( $this, 'get_user_logout_redirect' ), 11, 3 );

		add_filter( 'registration_redirect', array( $this, 'register_redirect' ), 999999, 1 );
	}

	/**
	 * Registration Redirect
	 *
	 *
	 * @param $redirect
	 *
	 * @return string|void
	 * @since @@version
	 *
	 */
	public function register_redirect( $redirect ){

		// Redirect to activation step pending if activation enabled and set pw disabled
		if( get_option( 'wplf_require_activation', true ) && ! get_option( 'wplf_register_set_pw', false ) ){
			return site_url( 'wp-login.php?action=activation&step=pending' );
		}

		return $redirect;
	}

	/**
	 * Get User Configured Redirect (based on User/Role/Default)
	 *
	 *
	 * @param string $type
	 * @param string $redirect_to
	 * @param string $requested
	 * @param bool   $user
	 *
	 * @return string|void
	 * @since @@version
	 *
	 */
	public static function get_user_redirect( $type = 'login', $redirect_to = '', $requested = '', $user = false ){

		$default = get_option( "wplf_default_{$type}_redirect", '' );
		$users   = get_option( "wplf_user_{$type}_redirects", array() );
		$roles   = get_option( "wplf_role_{$type}_redirects", array() );

		// Allow "redirect_to" GET/POST params to take priority (if enabled in settings)
		$req_priority = get_option( "wplf_redirect_to_{$type}_redirects", false );
		if( ! empty( $req_priority ) && ! empty( $requested ) ){
			return $requested;
		}

		// Check users
		if ( ! empty( $users ) && ! empty( $user ) && ! is_wp_error( $user ) && isset( $user->ID )) {
			foreach ( $users as $ucfg ) {
				if ( absint( $ucfg['id'] ) === absint( $user->ID ) ) {
					return site_url( $ucfg['redirect'] );
				}
			}
		}

		// Check roles
		if ( ! empty( $roles ) && ! empty( $user ) && ! is_wp_error( $user ) && isset( $user->roles ) ) {
			foreach ( (array) $roles as $rcfg ) {
				if ( in_array( $rcfg['role'], (array) $user->roles ) ) {
					return site_url( $rcfg['redirect'] );
				}
			}
		}

		return ! empty( $default ) ? site_url( $default ) : $redirect_to;
	}

	/**
	 * Get User Login Redirect URL
	 *
	 *
	 * @param $redirect_to
	 * @param $requested
	 * @param $user
	 *
	 * @return string|void
	 * @since @@version
	 *
	 */
	public static function get_user_login_redirect( $redirect_to, $requested, $user ){
		return self::get_user_redirect( 'login', $redirect_to, $requested, $user );
	}

	/**
	 * Get User Logout Redirect URL
	 *
	 *
	 * @param $redirect_to
	 * @param $requested
	 * @param $user
	 *
	 * @return string|void
	 * @since @@version
	 *
	 */
	public static function get_user_logout_redirect( $redirect_to, $requested, $user ){
		return self::get_user_redirect( 'logout', $redirect_to, $requested, $user );
	}
}