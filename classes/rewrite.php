<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Login_Flow_Rewrite {

	public static $prevent_rewrite;

	function __construct() {

		add_action( 'shutdown', array( $this, 'check_for_updates' ) );
		add_filter( 'lostpassword_url', array( $this, 'lostpassword_url' ), 9999, 2 );
		add_filter( 'login_url', array( $this, 'login_url' ), 9999, 2 );
		add_filter( 'register_url', array( $this, 'register_url' ), 9999, 1 );
		add_filter( 'site_url', array( $this, 'site_url' ), 9999, 4 );
		add_filter( 'wp_redirect', array( $this, 'site_url_redirect' ), 9999, 2 );
		add_action( 'login_form_resetpass', array( $this, 'handle_activate_rewrite' ), 9999 );
		add_action( 'login_form_rp', array( $this, 'handle_activate_setpw' ), 9999 );

	}

	/**
	 * Handle wp_redirect rewrites
	 *
	 * The core wp-login.php does not use permalinks/rewrites and as such it has
	 * hard-coded wp-login.php urls.  We use a filter on wp_redirect to redirect
	 * to a rewrite/permalink if enabled.
	 *
	 * @since @@version
	 *
	 * @param $location
	 * @param $status
	 *
	 * @return string
	 */
	function site_url_redirect( $location, $status ){

		$site_url = get_site_url();
		$path = str_replace( $site_url, '', $location );
		$location = $this->site_url( $location, $path, null, null );

		return $location;
	}

	/**
	 *
	 *
	 *
	 * @since @@version
	 *
	 */
	function handle_activate_setpw(){

		$rp_cookie = 'wp-resetpass-' . COOKIEHASH;
		// Check for activation/lost password cookie and non-permalink URL
		if ( $_SERVER[ 'REQUEST_URI' ] === '/wp-login.php?action=rp' && isset( $_COOKIE[ $rp_cookie ] ) && 0 < strpos( $_COOKIE[ $rp_cookie ], ':' ) ) {
			// Exit if activate rewrite is disabled
			if( ! get_option( 'wplf_rewrite_activate' ) ) return;
			$rp_path = 'wp-login.php?action=rp&step=password';
			$redirect = $this->get_url( 'activate', site_url( $rp_path ), 'password' );
			$value = wp_unslash( $_COOKIE[ $rp_cookie ] );
			// Set new cookie under our new path, this is required
			setcookie( $rp_cookie, $value, 0, $rp_path, COOKIE_DOMAIN, is_ssl(), TRUE );
			wp_redirect( $redirect );
			exit();
		}

	}


	/**
	 *
	 *
	 *
	 * @since @@version
	 *
	 */
	function handle_activate_rewrite(){

		$activation_key = filter_input( INPUT_GET, 'key', FILTER_SANITIZE_STRING );
		$activation_user = filter_input( INPUT_GET, 'login', FILTER_SANITIZE_EMAIL );
		list( $rp_path ) = explode( '?', wp_unslash( $_SERVER[ 'REQUEST_URI' ] ) );

		if ( $activation_key && $activation_user && $rp_path != '/wp-login.php' ) {
			$redirect = site_url( 'wp-login.php?action=rp&key=' . $activation_key . '&login=' . $activation_user );
			wp_redirect( $redirect );
			exit;
		}

	}

	/**
	 * Filter the site URL.
	 *
	 * @since @@version
	 *
	 * @param string      $url     The complete site URL including scheme and path.
	 * @param string      $path    Path relative to the site URL. Blank string if no path is specified.
	 * @param string|null $scheme  Scheme to give the site URL context. Accepts 'http', 'https', 'login',
	 *                             'login_post', 'admin', 'relative' or null.
	 * @param int|null    $blog_id Blog ID, or null for the current blog.
	 *
	 * @return string
	 */
	function site_url( $url, $path, $scheme, $blog_id ){

		// Lost Password
		if( $path === "wp-login.php?action=lostpassword" ) return $this->get_url( 'lost_pw', $url );
		if( $path === "wp-login.php?action=rp" ) return $this->get_url( 'lost_pw', $url, 'rp' );
		if( $path === "wp-login.php?action=resetpass" ) return $this->get_url( 'lost_pw', $url, 'resetpass' );
		if( $path === "wp-login.php?action=lostpassword&error=expiredkey" ) return $this->get_url( 'lost_pw', $url, 'expired' );
		if( $path === "wp-login.php?action=lostpassword&error=invalidkey" ) return $this->get_url( 'lost_pw', $url, 'invalid' );
		// Register
		if( $path === "wp-login.php?action=register" ) return $this->get_url( 'register', $url );
		if( $path === "wp-login.php?checkemail=confirm" ) return $this->get_url( 'register', $url, 'confirm' );
		if( $path === "wp-login.php?checkemail=registered" ) return $this->get_url( 'register', $url, 'registered' );
		if( $path === "wp-login.php?registration=disabled" ) return $this->get_url( 'register', $url, 'disabled' );
		// Login
		if( $path === "wp-login.php" ) return $this->get_url( 'login', $url );
		// Logout
		if ( $path === "wp-login.php?loggedout=true" ) return $this->get_url( 'loggedout', $url );
		// Activate
		//if ( $path === "wp-login.php?action=rp&activatecookie=set" ) return $this->get_url( 'activate', $url, 'setpw' );

		#$url = $this->check_for_cookie( $url, $path );

		return $url;

	}

	/**
	 *
	 *
	 *
	 * @since @@version
	 *
	 * @param $url
	 * @param $redirect
	 *
	 * @return string
	 */
	function login_url( $url, $redirect ){
		if( $redirect ) $redirect = "?redirect_to={$redirect}";
		if( ! $this->get_url( 'login' ) ) return $url . $redirect;
		return $this->get_url( 'login' ) . $redirect;
	}

	/**
	 *
	 *
	 *
	 * @since @@version
	 *
	 * @param $url
	 * @param $redirect
	 *
	 * @return string
	 */
	function lostpassword_url( $url, $redirect ){
		if ( $redirect ) $redirect = "?redirect_to={$redirect}";
		if ( ! $this->get_url( 'lost_pw' ) ) return $url . $redirect;
		return $this->get_url( 'lost_pw' ) . $redirect;
	}

	/**
	 *
	 *
	 *
	 * @since @@version
	 *
	 * @param $url
	 *
	 * @return bool|null|string
	 */
	function register_url( $url ){
		if ( ! $this->get_url( 'register' ) ) return $url;
		return $this->get_url( 'register' );
	}

	/**
	 *
	 *
	 *
	 * @since @@version
	 *
	 * @param      $name
	 * @param null $original_url
	 * @param null $extra_rewrite
	 *
	 * @return bool|null|string
	 */
	function get_url( $name, $original_url = null, $extra_rewrite = null ){

		$enabled = get_option( "wplf_rewrite_{$name}" );
		$slug = get_option( "wplf_rewrite_{$name}_slug" );

		if( ! $original_url ){ $url_response = false; } else { $url_response = $original_url; };
		if( ! $enabled || ! $slug ) return $url_response;

		$build_url = home_url() . "/{$slug}";
		if( $extra_rewrite ) $build_url .= "/{$extra_rewrite}";

		return $build_url;
	}

	/**
	 *
	 *
	 *
	 * @since @@version
	 *
	 */
	function check_for_updates(){

		$option_page = ( isset( $_POST[ 'option_page' ] ) ? $_POST[ 'option_page' ] : null );
		$action = ( isset( $_POST[ 'action' ] ) ? $_POST[ 'action' ] : null );

		if( $option_page === 'wp_login_flow' && $action === 'update' ){
			$this->set_rewrite_rules();
			flush_rewrite_rules();
		}

	}

	/**
	 *
	 *
	 *
	 * @since @@version
	 *
	 * @param $prevent
	 */
	static function prevent_rewrite( $prevent ){

		self::$prevent_rewrite = $prevent;

	}

	/**
	 *
	 *
	 *
	 * @since @@version
	 *
	 * @param $option
	 *
	 * @return bool
	 */
	function filter_rewrite_options( $option ){
		$rewrite = strpos( $option, 'wplf_rewrite' ) !== FALSE;
		$slug = strpos( $option, '_slug' ) !== FALSE;
		if( $rewrite && $slug ) $rewrite = FALSE;
		return $rewrite;
	}

	/**
	 *
	 *
	 *
	 * @since @@version
	 *
	 * @param null $options
	 *
	 * @return bool
	 */
	function set_rewrite_rules( $options = NULL ) {

		if( self::$prevent_rewrite ) return false;

		// Set known variable variables to false to prevent PHP notices
		$login = $lost_pw = $activate = $register = $loggedout = FALSE;
		// Get all options and filter out only the rewrite ones
		$settings = WP_Login_Flow_Settings::get_settings( 'rewrites' );
		$options = array_column_recursive( $settings, 'name' );
		$options = array_filter( $options, array( $this, 'filter_rewrite_options' ) );

		foreach ( $options as $option ){
			$enabled = get_option( $option );
			$value = get_option( $option . '_slug' );
			$variable = str_replace( 'wplf_rewrite_', '', $option );
			if( $enabled ) ${$variable} = $value; // wplf_rewrite_lost_pw is @var $lost_pw
		}

		/** @var self $login */
		if ( $login ) {
			add_rewrite_rule( $login . '/?', 'wp-login.php', 'top' );
		}

		/** @var self $lost_pw */
		if ( $lost_pw ) {
			add_rewrite_rule( $lost_pw . '/?', 'wp-login.php?action=lostpassword', 'top' );
			add_rewrite_rule( $lost_pw . '/rp/?', 'wp-login.php?action=rp', 'top' );
			add_rewrite_rule( $lost_pw . '/resetpass/?', 'wp-login.php?action=resetpass', 'top' );
			add_rewrite_rule( $lost_pw . '/expired/?', 'wp-login.php?action=lostpassword&error=expiredkey', 'top' );
			add_rewrite_rule( $lost_pw . '/invalid/?', 'wp-login.php?action=lostpassword&error=invalidkey', 'top' );
		}

		/** @var self $activate */
		if ( $activate ) {
			add_rewrite_rule( $activate . '/password/?', 'wp-login.php?action=rp&step=password', 'top' );
			add_rewrite_rule( $activate . '/([^/]*)/([^/]*)/', 'wp-login.php?action=rp&key=$2&login=$1', 'top' );
		}

		/** @var self $register */
		if ( $register ) {
			add_rewrite_rule( $register . '/?', 'wp-login.php?action=register', 'top' );
			add_rewrite_rule( $register . '/registered/?', 'wp-login.php?checkemail=registered', 'top' );
			add_rewrite_rule( $register . '/confirm/?', 'wp-login.php?checkemail=confirm', 'top' );
			add_rewrite_rule( $register . '/disabled/?', 'wp-login.php?registration=disabled', 'top' );
		}

		/** @var self $loggedout */
		if( $loggedout ){

			add_rewrite_rule( $loggedout . '/?', 'wp-login.php?loggedout=true', 'top' );

		}
	}
}