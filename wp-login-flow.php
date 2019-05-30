<?php
/**
 * Plugin Name: WP Login Flow
 * Plugin URI:  http://plugins.smyl.es
 * Description: Complete wp-login.php customization, including rewrites, require email activation, email templates, custom colors, logo, link, responsiveness, border radius, and more!
 * Author:      Myles McNamara
 * Author URI:  http://smyl.es
 * Version:     3.0.1
 * Last Updated: @@timestamp
 * Domain Path: /languages
 * Text Domain: wp_login_flow
 * Tested up to: 5.2.1
 * Requires at least: 4.4.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

include_once( 'functions.php' );

/**
 * Class WP_Login_Flow
 *
 * @since 3.0.0
 *
 */
Class WP_Login_Flow {

	const PLUGIN_SLUG = 'wp-login-flow';
	const PROD_ID = 'WP Login Flow';
	const VERSION = '3.0.1';
	/**
	 * @var Singleton Instance
	 */
	protected static $instance;
	/**
	 * @var string
	 */
	public    $wp_login = 'wp-login.php';
	/**
	 * @var \WP_Login_Flow_Settings
	 */
	protected $settings;
	/**
	 * @var
	 */
	protected $assets;
	/**
	 * @var
	 */
	protected $mail;
	/**
	 * @var
	 */
	protected $auth;

	/**
	 * WP_Login_Flow constructor.
	 */
	function __construct() {

		if ( ! defined( 'WP_LOGIN_FLOW_VERSION' ) ) define( 'WP_LOGIN_FLOW_VERSION', WP_Login_Flow::VERSION );
		if ( ! defined( 'WP_LOGIN_FLOW_PLUGIN_DIR' ) ) define( 'WP_LOGIN_FLOW_PLUGIN_DIR', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
		if ( ! defined( 'WP_LOGIN_FLOW_PLUGIN_URL' ) ) define( 'WP_LOGIN_FLOW_PLUGIN_URL', untrailingslashit( plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) ) );

		add_action( 'init', array( $this, 'load_translations' ) );
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
		add_filter( 'plugin_row_meta', array( $this, 'add_plugin_row_meta' ), 10, 4 );
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'settings_plugin_link' ) );

		register_activation_hook( __FILE__, array( $this, 'plugin_activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'plugin_deactivate' ) );

		new WP_Login_Flow_Core();

		if ( $this->activation_enabled() ) {
			new WP_Login_Flow_Login();
			new WP_Login_Flow_User();
			include( 'pluggables/wp-password-change-notification.php' );
		}

		include( 'pluggables/wp-new-user-notification.php' );

		if ( is_admin() ) $this->settings = new WP_Login_Flow_Settings();

	}

	/**
	 * Check if activation is enabled
	 *
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	function activation_enabled(){
		if( get_option( 'wplf_require_activation', true ) ) return true;
		return false;
	}

	/**
	 * Set rewrites on activate
	 *
	 *
	 * @since 2.0.0
	 *
	 */
	function plugin_activate() {

		$rewrite = new WP_Login_Flow_Rewrite();
		$rewrite->set_rewrite_rules();
		flush_rewrite_rules();

	}

	/**
	 * Handle rewrites on deactivate
	 *
	 *
	 * @since 2.0.0
	 *
	 */
	function plugin_deactivate(){

		WP_Login_Flow_Rewrite::$prevent_rewrite = TRUE;
		flush_rewrite_rules();

	}

	/**
	 * Executed on uninstall
	 *
	 *
	 * @since 2.0.0
	 *
	 */
	static function plugin_uninstall(){

		delete_option( 'WP_LOGIN_FLOW_VERSION' );

	}

	/**
	 * Notices on activation
	 *
	 *
	 * @since 2.0.0
	 *
	 */
	public static function admin_notices() {

		// Check if first activation
		if ( WP_LOGIN_FLOW_VERSION != get_option( 'WP_LOGIN_FLOW_VERSION' ) ) {
			update_option( 'WP_LOGIN_FLOW_VERSION', WP_LOGIN_FLOW_VERSION );
			$html = '<div class="updated"><p>';
			$html .= __( 'WP Login Flow is now activated, you can find <strong>Login Flow</strong> under the <strong>Users</strong> menu.' );
			$html .= '</p></div>';

			echo $html;
		}

		if( isset( $_GET[ 'dismiss-wplf-ms-notice' ] ) && ! empty( $_GET['dismiss-wplf-ms-notice'] ) ) update_option( 'WP_LOGIN_MS_NOTICE', 1 );

		// Check if Multisite
		if( is_multisite() && ! get_option( 'WP_LOGIN_MS_NOTICE' ) ){
			$html = '<div class="error">';
			$html .= '<p style="float:right;"><a href="'. esc_url( add_query_arg( "dismiss-wplf-ms-notice", "1" ) ) . '">' . __( 'Hide notice' ) . '</a></p>';
			$html .= '<p>' . __( 'WP Login Flow is recommended for single site installations -- not multisite installations.  All the features "should" work, but PLEASE thoroughly test everything manually, and report any issues you find on GitHub.' );
			$html .= '</p></div>';

			echo $html;
		}

	}

	/**
	 * Load Plugin Translations from Languages Directory
	 *
	 * @since 1.0.0
	 *
	 */
	function load_translations() {

		load_plugin_textdomain( 'wp-login-flow', FALSE, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}

	/**
	 * Add Settings link to plugins page
	 *
	 *
	 * @param $links
	 *
	 * @return array
	 * @since 3.0.0
	 *
	 */
	public function settings_plugin_link( $links ){

		$links[] = '<a href="' . admin_url( '/users.php?page=wp-login-flow' ) . '">' . __( 'Settings' ) . '</a>';
		return $links;
	}

	/**
	 * Add links on plugin page
	 *
	 * @param $plugin_meta
	 * @param $plugin_file
	 * @param $plugin_data
	 * @param $status
	 *
	 * @return array
	 */
	public function add_plugin_row_meta( $plugin_meta, $plugin_file, $plugin_data, $status ) {

		if ( self::PLUGIN_SLUG . '/' . self::PLUGIN_SLUG . '.php' == $plugin_file ) {
			$plugin_meta[] = '<a target="_blank" href="https://www.patreon.com/smyles"><span class="dashicons dashicons-heart" style="color: #ca2424; font-size: 15px; line-height: 1.5;"></span></a>';
			$plugin_meta[] = '<a target="_blank" href="http://wordpress.org/plugins/' . self::PLUGIN_SLUG . '/reviews/#new-post"><span class="dashicons dashicons-star-filled" style="font-size: 15px; line-height: 1.5;"></span></a>';
			$plugin_meta[] = '<a href="' . admin_url( '/users.php?page=wp-login-flow' ) . '">' . __( 'Settings' ) . '</a>';
			$plugin_meta[ ] = '<a target="_blank" href="http://github.com/tripflex/' . self::PLUGIN_SLUG . '">' . __( 'Open Source' ) . '</a>';
			$plugin_meta[ ] = '<a target="_blank" href="http://wordpress.org/plugins/' . self::PLUGIN_SLUG . '">' . __( 'WordPress' ) . '</a>';
			$plugin_meta[ ] = '<a target="_blank" href="https://www.transifex.com/projects/p/' . self::PLUGIN_SLUG . '">' . __( 'Translate' ) . '</a>';
		}

		return $plugin_meta;
	}

	/**
	 * Singleton Instance
	 *
	 * @since 1.0.0
	 *
	 * @return WP_Login_Flow
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * WP_Login_Flow autoloader
	 *
	 *
	 * @since 2.0.0
	 *
	 * @param $class
	 */
	public static function autoload( $class ){

		$class_file = str_replace( 'WP_Login_Flow_', '', $class );
		$file_array = array_map( 'strtolower', explode( '_', $class_file ) );

		$dirs = 0;
		$file = untrailingslashit( plugin_dir_path( __FILE__ ) ) . '/classes/';

		while ( $dirs ++ < count( $file_array ) ) {
			$file .= '/' . $file_array[ $dirs - 1 ];
		}

		$file .= '.php';

		if ( ! file_exists( $file ) || $class === 'WP_Login_Flow' ) return;

		include $file;

	}

}

spl_autoload_register( array( 'WP_Login_Flow', 'autoload' ) );
register_uninstall_hook( __FILE__, array( 'WP_Login_Flow', 'plugin_uninstall' ) );

WP_Login_Flow::get_instance();
