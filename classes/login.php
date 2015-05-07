<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Login_Flow_Login extends WP_Login_Flow {

	protected $step;
	protected $action;

	function __construct() {

		add_action( 'login_init', array( $this, 'login_init' ) );
		add_filter( 'wp_login_errors', array( $this, 'wp_login_errors' ), 10, 2 );
		add_filter( 'registration_redirect', array( $this, 'register_redirect' ), 9999, 1 );
		add_action( 'login_form_rp', array($this, 'activation_redirect'), 9999 );
		add_filter( 'gettext', array( $this, 'check_for_string_changes' ) );
//		Action right before output of password being reset ( wp-login.php:603 )
//		add_filter( 'validate_password_reset', array( $this, 'activation_password_set' ), 9999, 1 );
//		add_filter( 'login_enqueue_scripts', array( $this, 'login_script' ), 0 );

	}

	/**
	 * Return current action
	 *
	 *
	 * @since @@version
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
	 * @since @@version
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
	 * @since @@version
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
	 * @since @@version
	 *
	 * @param $text
	 *
	 * @return string|void
	 */
	function check_for_string_changes( $text ){

		if( $text === 'Reset Password' && $this->get_step() === 'activate' ){
			return __( 'Set Password' );
		}

		if( $text === 'A password will be e-mailed to you.' && $this->get_action() === 'register' ){
			if ( ! get_option( 'wplf_require_activation' ) ) return $text;
			return __( 'You will be emailed a link to set your password.' );
		}

		return $text;

	}

	/**
	 * Handle activation redirect to set password
	 *
	 * wp-login.php by default sets a cookie, removes the query args, and then redirects
	 * to wp-login.php?action=rp.  We have to check if cookie is set, and if the request_uri
	 * matches that, and then we can set the same cookie under our new path, and redirect to
	 * our activate password URL.
	 *
	 *
	 * @since @@version
	 *
	 */
	function activation_redirect() {

		$rp_cookie = 'wp-resetpass-' . COOKIEHASH;
		$is_rp = esc_url( $_SERVER[ 'REQUEST_URI' ] ) === '/wp-login.php?action=rp' ? TRUE : FALSE;
		$cookie_has_colon = 0 < strpos( $_COOKIE[ $rp_cookie ], ':' ) ? TRUE : FALSE;

		// Check for activation/lost password cookie and non-permalink URL
		if ( $this->get_step() === 'activate' && $is_rp && isset( $_COOKIE[ $rp_cookie ] ) && $cookie_has_colon ) {
			$rp_path  = 'wp-login.php?action=rp&step=activate';
			$redirect = $this->get_url( 'activate', site_url( $rp_path ), 'password' );
			$value    = wp_unslash( $_COOKIE[ $rp_cookie ] );
			// Set new cookie under our new path, this is required
			setcookie( $rp_cookie, $value, 0, $rp_path, COOKIE_DOMAIN, is_ssl(), TRUE );
			wp_redirect( $redirect );
			exit();
		}

	}

	/**
	 * Set registration redirection URL to pending activation
	 *
	 * If activation is enabled this method will set the redirect URL to the pending page URL
	 * or the standard URL if activation is disabled.
	 *
	 *
	 * @since @@version
	 *
	 * @param $url
	 *
	 * @return string|void
	 */
	function register_redirect( $url ){

		return site_url( 'wp-login.php?action=activation&step=pending' );

	}

	/**
	 * Output custom page if method exists
	 *
	 * Because wp_login_errors executes before page is output, we use this hook
	 * to check for step and action arguments, and if a method exists for that
	 * page then output the custom page.
	 *
	 *
	 * @since @@version
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
	 * @since @@version
	 *
	 */
	function activation_pending_page() {

		$thankyou_notice = sprintf( __( 'Thank you for registering.  Please check your email for your activation link.<br><br>If you do not receive the email please request a <a href="%s">password reset</a> to have the email sent again.' ), wp_lostpassword_url() );
		$template        = new WP_Login_Flow_Template();
		$thankyou_notice = $template->generate( 'wplf_notice_activation_required', $thankyou_notice );
		login_header( __( 'Pending Activation' ), '<p class="message reset-pass">' . $thankyou_notice . '</p>' );
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
	 * @since @@version
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