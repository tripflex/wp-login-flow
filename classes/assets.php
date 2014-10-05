<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Login_Flow_Assets {

	function __construct() {

		add_action( 'admin_enqueue_scripts', array( $this, 'register_assets' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'death_to_heartbeat' ), 1 );

	}

	/**
	 * Register Vendor/Core CSS and Scripts
	 *
	 * @since @@version
	 *
	 */
	function register_assets() {

		$styles          = '/assets/css/wplf.min.css';
		$vendor_styles   = '/assets/css/vendor.min.css';
		$vendor_scripts  = '/assets/js/vendor.min.js';
		$scripts         = '/assets/js/wplf.min.js';
		$scripts_version = WP_LOGIN_FLOW_VERSION;

		if ( defined( 'WPLF_DEBUG' ) ) {
			$styles          = '/assets/css/build/wplf.css';
			$vendor_styles   = '/assets/css/build/vendor.css';
			$vendor_scripts  = '/assets/js/build/vendor.js';
			$scripts         = '/assets/js/build/wplf.js';
			$scripts_version = filemtime( WP_LOGIN_FLOW_PLUGIN_DIR . $scripts );
		}

		wp_register_style( 'wplf-styles', WP_LOGIN_FLOW_PLUGIN_URL . $styles );
		wp_register_style( 'wplf-vendor-styles', WP_LOGIN_FLOW_PLUGIN_URL . $vendor_styles );
		wp_register_script( 'wplf-vendor-scripts', WP_LOGIN_FLOW_PLUGIN_URL . $vendor_scripts, array( 'jquery' ), $scripts_version, TRUE );
		wp_register_script( 'wplf-scripts', WP_LOGIN_FLOW_PLUGIN_URL . $scripts, array( 'jquery' ), $scripts_version, TRUE );

	}

	function enqueue_assets() {

		wp_enqueue_style( 'wplf-styles' );
		wp_enqueue_style( 'wplf-vendor-styles' );
		wp_enqueue_script( 'wplf-vendor-scripts' );
		wp_enqueue_script( 'wplf-scripts' );

	}

	/**
	 * Deregister WP Heartbeat Script
	 *
	 * @since @@version
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

}