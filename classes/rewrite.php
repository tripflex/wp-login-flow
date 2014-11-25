<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Login_Flow_Rewrite {

	public static $prevent_rewrite;

	function __construct() {

		add_action( 'shutdown', array( $this, 'check_for_updates' ) );
		add_action( 'admin_init', array( $this, 'preserve_rewrite_rules' ) );
		add_filter( 'lostpassword_url', array( $this, 'lostpassword_url' ), 9999, 2 );
		add_filter( 'login_url', array( $this, 'login_url' ), 9999, 2 );
		add_filter( 'register_url', array( $this, 'register_url' ), 9999, 1 );
		add_filter( 'site_url', array( $this, 'site_url' ), 9999, 4 );

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
// wp-login.php?action=rp - reset-password
// wp-login.php?action=resetpass - reset-password after setting password with prompt to login
// wp-login.php?loggedout=true
// wp-login.php?checkemail=confirm
// wp-login.php?action=lostpassword&error=expiredkey
// wp-login.php?action=lostpassword&error=invalidkey
// wp-login.php?action=resetpass
// wp-login.php?registration=disabled
// wp-login.php?checkemail=registered - Registration Complete page

		if( $path === "wp-login.php?action=lostpassword" && $this->get_url( 'lost_pw' ) )
			$url = $this->get_url( 'lost_pw' );

		if( $path === "wp-login.php?action=register" && $this->get_url( 'register' ) )
			$url = $this->get_url( 'register' );

		if( $path === "wp-login.php" && $this->get_url( 'login' ) )
			$url = $this->get_url( 'login' );

		return $url;

	}

	function login_url( $url, $redirect ){
		if( $redirect ) $redirect = "?redirect_to={$redirect}";
		if( ! $this->get_url( 'login' ) ) return $url . $redirect;
		return $this->get_url( 'login' ) . $redirect;
	}

	function lostpassword_url( $url, $redirect ){
		if ( $redirect ) $redirect = "?redirect_to={$redirect}";
		if ( ! $this->get_url( 'lost_pw' ) ) return $url . $redirect;
		return $this->get_url( 'lost_pw' ) . $redirect;
	}

	function register_url( $url ){
		if ( ! $this->get_url( 'register' ) ) return $url;
		return $this->get_url( 'register' );
	}

	function get_url( $name ){

		$enabled = get_option( "wplf_rewrite_{$name}" );
		$slug = get_option( "wplf_rewrite_{$name}_slug" );

		if( ! $enabled || ! $slug ) return false;

		return home_url() . "/{$slug}";
	}

	function check_for_updates(){

		$option_page = ( isset( $_POST[ 'option_page' ] ) ? $_POST[ 'option_page' ] : null );
		$action = ( isset( $_POST[ 'action' ] ) ? $_POST[ 'action' ] : null );

		if( $option_page === 'wp_login_flow' && $action === 'update' ){
			$this->set_rewrite_rules();
			flush_rewrite_rules();
		}

	}

	static function prevent_rewrite( $prevent ){

		self::$prevent_rewrite = $prevent;

	}

	function preserve_rewrite_rules() {


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

		$settings = WP_Login_Flow_Settings::get_settings( 'rewrites' );
		$options = array_column_recursive( $settings, 'name' );

		foreach ( $options as $option ){
			if( (strlen( $option, '_slug' ) !== false)  || ! get_option( $option ) ) continue; // Skip _slug or disabled
			${$option} = get_option( "${$option}_slug" ); // wplf_rewrite_lost_pw is @var $lost_pw
		}

		/** @var self $login */
		if ( $login ) {
			add_rewrite_rule( $login . '/?', 'wp-login.php', 'top' );
		}

		/** @var self $lost_pw */
		if ( $lost_pw ) {
			add_rewrite_rule( $lost_pw . '/?', 'wp-login.php?action=lostpassword', 'top' );
		}

		/** @var self $activate */
		if ( $activate ) {
			add_rewrite_rule( $activate . '/([^/]*)/([^/]*)/', 'wp-login.php?action=rp&key=$2&login=$1', 'top' );
		}

		/** @var self $register */
		if ( $register ) {
			add_rewrite_rule( $register . '/?', 'wp-login.php?action=register', 'top' );
		}
	}
}