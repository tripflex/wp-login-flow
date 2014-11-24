<?php
/**
 * Plugin Name: WP Login Flow
 * Plugin URI:  http://plugins.smyl.es
 * Description: Complete customization of WordPress core wp-login.php styles, structure, permalinks, including activation by email and more!
 * Author:      Myles McNamara
 * Author URI:  http://smyl.es
 * Version:     1.0.0
 * Text Domain: wp_login_flow
 * Last Updated: @@timestamp
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

include_once( 'functions.php' );

Class WP_Login_Flow {

	const PLUGIN_SLUG = 'wp-login-flow';
	const PROD_ID = 'WP Login Flow';
	const VERSION = '1.0.0';
	protected static $instance;
	public           $wp_login = 'wp-login.php';
	private          $plugin_slug;
	protected        $settings;
	protected        $assets;
	protected        $mail;
	protected        $auth;

	function __construct() {

		if ( ! defined( 'WP_LOGIN_FLOW_VERSION' ) ) define( 'WP_LOGIN_FLOW_VERSION', WP_Login_Flow::VERSION );
		if ( ! defined( 'WP_LOGIN_FLOW_PLUGIN_DIR' ) ) define( 'WP_LOGIN_FLOW_PLUGIN_DIR', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
		if ( ! defined( 'WP_LOGIN_FLOW_PLUGIN_URL' ) ) define( 'WP_LOGIN_FLOW_PLUGIN_URL', untrailingslashit( plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) ) );

		add_action( 'init', array( $this, 'load_translations' ) );
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
		add_filter( 'plugin_row_meta', array( $this, 'add_plugin_row_meta' ), 10, 4 );

		register_activation_hook( __FILE__, array( $this, 'plugin_activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'plugin_deactivate' ) );

		new WP_Login_Flow_Assets();
		new WP_Login_Flow_Login();
		new WP_Login_Flow_Login_Styles();
		new WP_Login_Flow_Mail();
		new WP_Login_Flow_User();
		new WP_Login_Flow_Rewrite();
		if ( is_admin() ) $this->settings = new WP_Login_Flow_Settings();

		$this->check_pluggables();
	}

	function check_pluggables(){

		$enable = get_option( 'wplf_require_activation' );
		if( ! empty( $enable ) ){
			include( 'pluggables/wp-new-user-notification.php' );
			include( 'pluggables/wp-password-change-notification.php' );
		}

	}

	function plugin_activate() {

		$rewrite = new WP_Login_Flow_Rewrite();
		$rewrite->set_rewrite_rules();
		flush_rewrite_rules();

	}

	function plugin_deactivate(){

		WP_Login_Flow_Rewrite::$prevent_rewrite = TRUE;
		flush_rewrite_rules();

	}

	static function plugin_uninstall(){

		delete_option( 'WP_LOGIN_FLOW_VERSION' );

	}

	public static function admin_notices() {

		// Check if first activation
		if ( WP_LOGIN_FLOW_VERSION != get_option( 'WP_LOGIN_FLOW_VERSION' ) ) {
			update_option( 'WP_LOGIN_FLOW_VERSION', WP_LOGIN_FLOW_VERSION );
			$html = '<div class="updated"><p>';
			$html .= __( 'You are now requiring users to activate their email when registering.' );
			$html .= '</p></div>';

			echo $html;
		}

		if( isset( $_GET[ 'dismiss-wplf-ms-notice' ] ) && ! empty( $_GET['dismiss-wplf-ms-notice'] ) ) update_option( 'WP_LOGIN_MS_NOTICE', 1 );

		// Check if Multisite
		if( is_multisite() && ! get_option( 'WP_LOGIN_MS_NOTICE' ) ){
			$html = '<div class="error">';
			$html .= '<p style="float:right;"><a href="'. esc_url( add_query_arg( "dismiss-wplf-ms-notice", "1" ) ) . '">' . __( 'Hide notice' ) . '</a></p>';
			$html .= '<p>' . __( 'WP Login Flow is not recommended for multisite installations.  Some features may work but other may have issues.  <strong>You have been warned.</strong>' );
			$html .= '</p></div>';

			echo $html;
		}

	}

	/**
	 * Load Plugin Translations from Languages Directory
	 *
	 * @since @@version
	 *
	 */
	function load_translations() {

		load_plugin_textdomain( 'wp-login-flow', FALSE, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}

	/**
	 * @param $plugin_meta
	 * @param $plugin_file
	 * @param $plugin_data
	 * @param $status
	 *
	 * @return array
	 */
	public function add_plugin_row_meta( $plugin_meta, $plugin_file, $plugin_data, $status ) {

		if ( self::PLUGIN_SLUG . '/' . self::PLUGIN_SLUG . '.php' == $plugin_file ) {
			$plugin_meta[ ] = '<a target="_blank" href="http://github.com/tripflex/' . self::PLUGIN_SLUG . '">' . __( 'GitHub' ) . '</a>';
			$plugin_meta[ ] = '<a target="_blank" href="http://wordpress.org/plugins/' . self::PLUGIN_SLUG . '">' . __( 'Wordpress' ) . '</a>';
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
