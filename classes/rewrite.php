<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Login_Flow_Rewrite extends WP_Login_Flow {

	protected $status_option = 'wplf_activated';
	public static $prevent_rewrite;

	function __construct() {

		add_action( 'update_option_wplf_options', array( $this, 'parse_options_updated' ), 30, 2 );
		add_action( 'admin_init', array( $this, 'preserve_rewrite_rules' ) );

	}

	static function prevent_rewrite( $prevent ){

		self::$prevent_rewrite = $prevent;

	}

	public function parse_options_updated( $old_value, $new_value ) {

		$this->set_rewrite_rules( $new_value );
		flush_rewrite_rules();

	}

	function preserve_rewrite_rules() {

		global $pagenow;

		do_action( 'wplf_pre_set_rewrite_rules' );

		if ( ! ( $_GET[ 'page' ] == 'wp-login-flow' ) && ! ( $pagenow == 'options.php' ) && ! ( $pagenow == 'users.php' ) && ! ( self::$prevent_rewrite ) ) {
			$this->set_rewrite_rules();
		}

		do_action( 'wplf_post_set_rewrite_rules' );
	}

	function set_rewrite_rules( $options = NULL ) {

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