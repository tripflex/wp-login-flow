<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Login_Flow_Rewrite {

	public static $prevent_rewrite;

	function __construct() {

		add_action( 'shutdown', array( $this, 'check_for_updates' ) );
		add_action( 'admin_init', array( $this, 'preserve_rewrite_rules' ) );

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

	function set_rewrite_rules( $options = NULL ) {

		if( self::$prevent_rewrite ) return false;

		$enable_login = get_option( 'wplf_rewrite_login' );
		$login_rewrite = get_option( 'wplf_rewrite_login_slug' );
		$enable_lostpw = get_option( 'wplf_rewrite_lost_pw' );
		$lostpw_rewrite = get_option( 'wplf_rewrite_lost_pw_slug' );
		$enable_activate = get_option( 'wplf_rewrite_activate' );
		$activate_rewrite = get_option( 'wplf_rewrite_activate_slug' );
		$enable_register = get_option( 'wplf_rewrite_register' );
		$register_rewrite = get_option( 'wplf_rewrite_register_slug' );

		if ( ! empty( $enable_login ) && ! empty( $login_rewrite ) ) {
			add_rewrite_rule( $login_rewrite . '/?', 'wp-login.php', 'top' );
		}
		if ( ! empty( $enable_lostpw ) && ! empty( $lostpw_rewrite ) ) {
			add_rewrite_rule( $lostpw_rewrite . '/?', 'wp-login.php?action=lostpassword', 'top' );
		}
		if ( ! empty( $enable_activate ) && ! empty( $activate_rewrite ) ) {
			add_rewrite_rule( $activate_rewrite . '/([^/]*)/([^/]*)/', 'wp-login.php?action=rp&key=$2&login=$1', 'top' );
		}
		if ( ! empty( $enable_register ) && ! empty( $register_rewrite ) ) {
			add_rewrite_rule( $register_rewrite . '/?', 'wp-login.php?action=register', 'top' );
		}
	}
}