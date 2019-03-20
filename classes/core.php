<?php

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class WP_Login_Flow_Core
 *
 * @since @@version
 *
 */
class WP_Login_Flow_Core {

	/**
	 * WP_Login_Flow_Core constructor.
	 */
	function __construct() {
		new WP_Login_Flow_Assets();
		new WP_Login_Flow_Login_Styles();
		new WP_Login_Flow_Mail();
		new WP_Login_Flow_Rewrite();
		new WP_Login_Flow_Emails();
		new WP_Login_Flow_Register();
		new WP_Login_Flow_Redirects();
		add_filter( 'show_admin_bar', array( $this, 'maybe_hide_admin_bar' ) );
	}

	/**
	 * Hide Admin Bar from Non-Admins (if enabled in settings)
	 *
	 *
	 * @param $show
	 *
	 * @return bool
	 * @since @@version
	 *
	 */
	public function maybe_hide_admin_bar( $show ){

		if( ! current_user_can( 'manage_options' ) && get_option( 'wplf_show_admin_bar_only_admins', false ) ){
			return false;
		}

		return $show;
	}
}