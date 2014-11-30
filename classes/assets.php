<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Login_Flow_Assets {

	function __construct() {

		add_action( 'admin_enqueue_scripts', array( $this, 'register_assets' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'death_to_heartbeat' ), 1 );
		add_action( 'admin_enqueue_scripts', array( $this, 'pointer' ), 1000 );

	}

	/**
	 * Register Vendor/Core CSS and Scripts
	 *
	 * @since 1.0.0
	 *
	 */
	function register_assets() {

		$styles          = '/assets/css/wplf.min.css';
		$vendor_styles   = '/assets/css/vendor.min.css';
		$vendor_scripts  = '/assets/js/vendor.min.js';
		$scripts         = '/assets/js/wplf.min.js';
		$pointer         = '/assets/js/pointer.min.js';
		$scripts_version = WP_LOGIN_FLOW_VERSION;

		if ( defined( 'WPLF_DEBUG' ) ) {
			$styles          = '/assets/css/build/wplf.css';
			$vendor_styles   = '/assets/css/build/vendor.css';
			$vendor_scripts  = '/assets/js/build/vendor.js';
			$scripts         = '/assets/js/build/wplf.js';
			$pointer         = '/assets/js/build/pointer.js';
			$scripts_version = filemtime( WP_LOGIN_FLOW_PLUGIN_DIR . $scripts );
		}

		wp_register_style( 'wplf-styles', WP_LOGIN_FLOW_PLUGIN_URL . $styles );
		wp_register_style( 'wplf-vendor-styles', WP_LOGIN_FLOW_PLUGIN_URL . $vendor_styles );
		wp_register_script( 'wplf-vendor-scripts', WP_LOGIN_FLOW_PLUGIN_URL . $vendor_scripts, array( 'jquery' ), $scripts_version, TRUE );
		wp_register_script( 'wplf-scripts', WP_LOGIN_FLOW_PLUGIN_URL . $scripts, array( 'jquery', 'wp-color-picker' ), $scripts_version, TRUE );
		wp_register_script( 'wplf-pointer', WP_LOGIN_FLOW_PLUGIN_URL . $pointer, array( 'jquery' ), $scripts_version, TRUE );

		$this->enqueue_assets();
	}

	function enqueue_assets() {

		global $pagenow;
		if ( $pagenow == 'users.php' && $_GET[ 'page' ] == 'wp-login-flow' ){
			wp_enqueue_style( 'wplf-styles' );
			wp_enqueue_style( 'wplf-vendor-styles' );
			wp_enqueue_script( 'wplf-vendor-scripts' );
			wp_enqueue_script( 'wplf-scripts' );
		}

	}

	/**
	 * Deregister WP Heartbeat Script
	 *
	 * @since 1.0.0
	 *
	 */
	function death_to_heartbeat() {

		global $pagenow;
		$current_page = ( isset( $_GET[ 'page' ] ) ? $_GET[ 'page' ] : '' );

		$plugin_pages = array(
			'wp_login_flow'
		);

		if ( $pagenow == 'users.php' && in_array( $current_page, $plugin_pages ) ) {
			wp_deregister_script( 'heartbeat' );
		}
	}

	function pointer() {

		if( ! current_user_can( 'manage_options') ) return false;

		$dismissed = explode( ',', (string) get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', TRUE ) );
		if( in_array( 'wplf_activate_pointer', $dismissed ) ) return;

		wp_localize_script( 'wplf-pointer', 'wplf_pointer', array(
			'h3' => __( 'WP Login Flow Settings' ),
			'p'  => __( 'The settings for WP Login Flow can be found under the <strong>User</strong> menu' )
		) );

		wp_enqueue_style( 'wp-pointer' );
		wp_enqueue_script( 'wp-pointer' );
		wp_enqueue_script( 'wplf-pointer' );

	}

}