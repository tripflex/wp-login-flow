<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Login_Flow_Login extends WP_Login_Flow {

	function __construct() {

		add_filter( 'wp_login_errors', array( $this, 'wp_login_errors' ), 10, 2 );
		add_filter( 'register_form', array( $this, 'after_email_field' ), 0 );
		add_filter( 'login_enqueue_scripts', array( $this, 'login_script' ), 0 );

	}

	public function wp_login_errors( $errors, $redirect_to ) {

		// Replace core WordPress thank you with our own custom onePl
		$code = $errors->get_error_code();
		if ( $code === 'registered' ) {
			// $errors->remove( 'registered' ); // WordPress >=4.1 required
			$errors = $this->unset_wperror( $errors, 'registered' );

			$thankyou_notice = sprintf( __( 'Thank you for registering.  Please check your email for your activation link.<br><br>If you do not receive the email please request a <a href="%s">password reset</a> to have the email sent again.' ), wp_lostpassword_url() );
			$template        = new WP_Login_Flow_Template();
			$notice          = $template->generate( 'wplf_notice_activation_required', $thankyou_notice );
			$errors->add( 'registered', $notice, 'message' );
		}

		return $errors;
	}

	function after_email_field(){

		if ( ! get_option( 'wplf_require_activation' ) ) return FALSE;
		// Enqueue jQuery so we can access it from other hooks or areas on page
		// wp_enqueue_script( 'jquery' );
	}

	function login_script(){

		if( ! get_option( 'wplf_require_activation' ) ) return false;
		// For now use pure JavaScript as jQuery is not loaded by default on wp-login.php
		?>

		<script type="text/javascript">
			document.addEventListener(
				"DOMContentLoaded", function ( event ) {
					document.getElementById( "reg_passmail" ).innerHTML = '<?php _e( 'You will be emailed a link to set your password.' ) ?>';
				}
			);
		</script>

		<?php

	}

	function unset_wperror( $errors, $unset ){

		if( ! is_wp_error( $errors ) ) return false;

		$newError = new WP_Error();

		foreach( $errors->errors as $name => $values ){

			if( $name === $unset ) continue;
			$type = $errors->error_data[$name];
			$message = $values[0];

			$newError->add( $name, $message, $type );

		}

		return $newError;
	}

}