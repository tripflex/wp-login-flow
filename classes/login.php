<?php

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class WP_Login_Flow_Login
 *
 * @since @@version
 *
 */
class WP_Login_Flow_Login {

	/**
	 * @var
	 */
	protected $step;
	/**
	 * @var
	 */
	protected $action;

	/**
	 * WP_Login_Flow_Login constructor.
	 */
	function __construct() {

		add_action( 'login_init', array( $this, 'login_init' ) );
		add_filter( 'wp_login_errors', array( $this, 'wp_login_errors' ), 10, 2 );
//		add_action( 'login_form_rp', array($this, 'activation_redirect'), 9999 );
		add_filter( 'gettext', array( $this, 'check_for_string_changes' ), 1 );
//		Action right before output of password being reset ( wp-login.php:603 )
//		add_filter( 'validate_password_reset', array( $this, 'activation_password_set' ), 9999, 1 );
		add_filter( 'login_enqueue_scripts', array( $this, 'login_assets' ) );
		add_action( 'login_header', array( $this, 'login_header' ) );
	}

	/**
	 * Output Loader HTML in Login Header (also Register, etc)
	 *
	 *
	 * @since @@version
	 *
	 */
	function login_header(){

		$action = isset( $_REQUEST['action'] ) ? $_REQUEST['action'] : 'login';

		if ( ( $action === 'register' && get_option( 'wplf_register_loader', false ) ) || ( $action === 'login' && get_option( 'wplf_login_loader', false ) ) ) {
			?>
			<div id="wplf-loader">
				<span id="login-loader-icon" class="dashicons dashicons-image-rotate"></span>
			</div>
			<?php
		}
	}

	/**
	 * Enqueue CSS/JS on Frontend if Enabled in Settings
	 *
	 *
	 * @since @@version
	 *
	 */
	function login_assets(){

		WP_Login_Flow_Assets::frontend();

		$action = isset( $_REQUEST['action'] ) ? $_REQUEST['action'] : 'login';

		if( ( $action === 'register' && get_option( 'wplf_register_loader', false ) ) || ( $action === 'login' && get_option( 'wplf_login_loader', false ) ) ){
			wp_enqueue_script( 'wplf-frontend' );
			wp_enqueue_style( 'wplf-frontend' );
			wp_enqueue_style('dashicons' );
		}

	}

	/**
	 * Return current action
	 *
	 *
	 * @since 2.0.0
	 *
	 * @return mixed
	 */
	function get_action(){
		if( empty( $this->action ) ) $this->login_init();
		return $this->action;
	}

	/**
	 * Return current step
	 *
	 *
	 * @since 2.0.0
	 *
	 * @return mixed
	 */
	function get_step(){
		if ( empty( $this->step ) ) $this->login_init();
		return $this->step;
	}

	/**
	 * Execute login init handling
	 *
	 *
	 * @since 2.0.0
	 *
	 */
	function login_init(){

		$step = filter_input( INPUT_GET, 'step', FILTER_SANITIZE_URL );
		$action = filter_input( INPUT_GET, 'action', FILTER_SANITIZE_URL );

		$this->step = empty( $step ) ? FALSE : $step;
		$this->action = empty( $action ) ? FALSE : $action;

	}

	/**
	 * Change wp-login.php wording through translation
	 *
	 * Since we are using the core WordPress login/register/lostpw (wp-login.php) we need
	 * to make sure some of the strings match what is actually being done (ie set password instead of reset)
	 *
	 * @since 2.0.0
	 *
	 * @param $text
	 *
	 * @return string|void
	 */
	function check_for_string_changes( $text ){

		if( $text === 'Reset Password' && ( $this->get_step() === 'activate' || $this->get_step() === 'setpw' ) ){
			return __( 'Set Password' );
		}

		return $text;

	}

	/**
	 * Output custom page if method exists
	 *
	 * Because wp_login_errors executes before page is output, we use this hook
	 * to check for step and action arguments, and if a method exists for that
	 * page then output the custom page.
	 *
	 *
	 * @since 2.0.0
	 *
	 * @param $errors
	 * @param $redirect_to
	 *
	 * @return mixed
	 */
	function wp_login_errors( $errors, $redirect_to ) {

		if( $this->get_step() && $this->get_action() ){
			$method = "{$this->action}_{$this->step}_page";
			if ( method_exists( $this, $method ) ) call_user_func( array($this, $method) );
		}

		return $errors;
	}

	/**
	 * Activation Pending Page
	 *
	 * Output default or custom template for custom activation pending page.
	 *
	 *
	 * @since 2.0.0
	 *
	 */
	function activation_pending_page() {

		$thankyou_notice = sprintf( __( '<p>Thank you for registering.  Please check your email for your activation link.</p><p>If you do not receive the email please request a <a href="%s">password reset</a> to have the email sent again.</p>' ), '%wp_lost_pw_url%' );
		$template        = new WP_Login_Flow_Template();
		$thankyou_notice = $template->generate( 'wplf_notice_activation_required', $thankyou_notice );
		login_header( __( 'Pending Activation' ), '<div class="message reset-pass">' . $thankyou_notice . '</div>' );
		login_footer();
		exit;

	}

	/**
	 * Unset any WP Errors necessary
	 *
	 * The core of WordPress wp-login.php uses WP Errors for handling notices, and in order
	 * to use our own custom templates we need to sometimes unset those errors to prevent them
	 * from showing.
	 *
	 * @since 3.0.0 -- not sure if this is even used anymore?
	 *
	 * @since 2.0.0
	 *
	 * @param $errors
	 * @param $unset
	 * @param $specific_value
	 *
	 * @return bool|\WP_Error
	 */
	function unset_wperror( $errors, $unset, $specific_value ){

		if( ! is_wp_error( $errors ) ) return false;

		$newError = new WP_Error();

		foreach( $errors->errors as $name => $values ){

			if( $name === $unset && ( ! $specific_value || $values[ 0 ] === $specific_value )) continue;

			$type = $errors->error_data[$name];
			$message = $values[0];

			$newError->add( $name, $message, $type );

		}

		return $newError;
	}

}