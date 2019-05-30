<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WP_Login_Flow_Assets
 *
 * @since 3.0.0
 *
 */
class WP_Login_Flow_Assets {

	/**
	 * WP_Login_Flow_Assets constructor.
	 */
	function __construct() {

		add_action( 'admin_enqueue_scripts', array( $this, 'register_assets' ), 10 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ), 20 );
		add_action( 'admin_enqueue_scripts', array( $this, 'death_to_heartbeat' ), 1 );
		add_action( 'admin_enqueue_scripts', array( $this, 'pointer' ), 50 );
		add_action( 'wp_enqueue_scripts', array( $this, 'frontend' ) );
	}

	/**
	 * Register Frontend CSS/JS
	 *
	 *
	 * @since 3.0.0
	 *
	 */
	public static function frontend(){

		$styles    = '/assets/css/frontend.min.css';
		$scripts   = '/assets/js/frontend.min.js';
		$scripts_version = WP_LOGIN_FLOW_VERSION;

		if ( defined( 'WPLF_DEBUG' ) ) {
			$styles   = '/assets/css/build/frontend.css';
			$scripts  = '/assets/js/build/frontend.js';
			$scripts_version = filemtime( WP_LOGIN_FLOW_PLUGIN_DIR . $scripts );
		}

		wp_register_style( 'wplf-frontend', WP_LOGIN_FLOW_PLUGIN_URL . $styles );
		wp_register_script( 'wplf-frontend', WP_LOGIN_FLOW_PLUGIN_URL . $scripts, array( 'jquery' ), $scripts_version, true );
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
		wp_register_script( 'wplf-scripts', WP_LOGIN_FLOW_PLUGIN_URL . $scripts, array( 'jquery', 'wp-color-picker', 'jquery-ui-sortable' ), $scripts_version, TRUE );
		wp_register_script( 'wplf-pointer', WP_LOGIN_FLOW_PLUGIN_URL . $pointer, array( 'jquery' ), $scripts_version, TRUE );

		if ( function_exists( 'wp_enqueue_code_editor' ) ) {

			// Bail if user disabled CodeMirror.
			if ( apply_filters( 'wp_login_flow_disable_custom_css_codemirror', false ) ) {
				return;
			}

			// Enqueue code editor and settings for Custom CSS
			$css_settings = wp_enqueue_code_editor( array( 'type' => 'css' ) );

			wp_localize_script( 'wplf-scripts', 'wplf_css_codeeditor', array( 'codeEditor' => $css_settings ) );

		}
//		$this->enqueue_assets();
	}

	/**
	 * Enqueue Admin Assets (ONLY ON OUR PAGES!)
	 *
	 *
	 * @param $hook
	 *
	 * @since 3.0.0
	 *
	 */
	function enqueue_assets( $hook ) {

		if ( empty( $hook ) || ! empty( $hook ) && ! in_array( $hook, array( 'users_page_wp-login-flow' ) ) ) {
			return;
		}

		wp_enqueue_style( 'wplf-styles' );
		wp_enqueue_style( 'wplf-vendor-styles' );
		wp_enqueue_script( 'wplf-vendor-scripts' );
		wp_enqueue_script( 'wplf-scripts' );
		wp_enqueue_style( 'wp-codemirror' );

	}

	/**
	 * Deregister WP Heartbeat Script
	 *
	 * @since 1.0.0
	 *
	 * @param $hook
	 */
	function death_to_heartbeat( $hook ) {

		if ( empty( $hook ) || ! empty( $hook ) && ! in_array( $hook, array( 'users_page_wp-login-flow' ) ) ) {
			return;
		}

		wp_deregister_script( 'heartbeat' );

	}

	/**
	 * Enqueue and add Pointer in Admin area
	 *
	 *
	 * @return bool|void
	 * @since 3.0.0
	 *
	 */
	function pointer() {

		if ( ! current_user_can( 'manage_options' ) ) {
			return FALSE;
		}

		$dismissed = explode( ',', (string) get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', TRUE ) );
		if ( in_array( 'wplf_activate_pointer', $dismissed ) ) {
			return;
		}

		wp_localize_script( 'wplf-pointer', 'wplf_pointer', array(
			'h3' => __( 'WP Login Flow Settings', 'wp-login-flow' ),
			'p'  => __( 'The settings for WP Login Flow can be found under the <strong>User</strong> menu', 'wp-login-flow' )
		) );

		wp_enqueue_style( 'wp-pointer' );
		wp_enqueue_script( 'wp-pointer' );
		wp_enqueue_script( 'wplf-pointer' );

	}

}