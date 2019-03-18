<?php

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class WP_Login_Flow_Register
 *
 * @since @@version
 *
 */
class WP_Login_Flow_Register extends WP_Login_Flow_Login {

	/**
	 * WP_Login_Flow_Register constructor.
	 */
	function __construct() {
		add_action( 'user_register', array( $this, 'user_registered' ) );
		add_action( 'register_form', array( $this, 'register_fields' ) );
		add_filter( 'registration_errors', array( $this, 'registration_errors'), 10, 3 );
		add_filter( 'gettext', array( $this, 'check_for_string_changes' ), 1 );
		add_filter( 'login_form_register', array( $this, 'login_form_register' ) );
	}

	/**
	 * Login Form Register Hook
	 *
	 *
	 * @since @@version
	 *
	 */
	function login_form_register(){

		// Set user_login equal to user_email if enabled in settings
		if ( get_option( 'wplf_registration_email_as_un', false ) && isset( $_POST['user_login'] ) && isset( $_POST['user_email'] ) && ! empty( $_POST['user_email'] ) ) {
			$_POST['user_login'] = $_POST['user_email'];
		}

	}

	/**
	 * Change wp-login.php wording through translation
	 *
	 * Since we are using the core WordPress login/register/lostpw (wp-login.php) we need
	 * to make sure some of the strings match what is actually being done (ie set password instead of reset)
	 *
	 * @param $text
	 *
	 * @return string|void
	 * @since 2.0.0
	 *
	 */
	function check_for_string_changes( $text ) {

		if ( $text === 'Registration confirmation will be emailed to you.' && $this->get_action() === 'register' ) {
			if ( get_option( 'wplf_register_set_pw', false ) ) {
				return ''; // Return empty string if user is allowed to set password
			}
		}

		return $text;

	}

	public function output_pw_fields(){

		if ( get_option( "wplf_register_set_pw", false ) ) {
			wp_enqueue_script( 'password-strength-meter' );
			wp_enqueue_script( 'user-profile' );

			?>
			<div class="user-pass1-wrap">
				<p>
					<label for="pass1"><?php _e( 'Password' ); ?></label>
				</p>

				<div class="wp-pwd">
					<div class="password-input-wrapper">
						<input type="password" data-reveal="1" data-pw="<?php echo esc_attr( wp_generate_password( 16 ) ); ?>" name="pass1" id="pass1" class="input password-input" size="24" value="" autocomplete="off" aria-describedby="pass-strength-result"/>
						<span class="button button-secondary wp-hide-pw hide-if-no-js">
							<span class="dashicons dashicons-hidden"></span>
						</span>
					</div>
					<div id="pass-strength-result" class="hide-if-no-js" aria-live="polite"><?php _e( 'Strength indicator' ); ?></div>
				</div>
				<div class="pw-weak">
					<label>
						<input type="checkbox" name="pw_weak" class="pw-checkbox"/>
						<?php _e( 'Confirm use of weak password' ); ?>
					</label>
				</div>
			</div>

			<p class="user-pass2-wrap">
				<label for="pass2"><?php _e( 'Confirm new password' ); ?></label><br/>
				<input type="password" name="pass2" id="pass2" class="input" size="20" value="" autocomplete="off"/>
			</p>

			<p class="description indicator-hint"><?php echo wp_get_password_hint(); ?></p><br class="clear"/>
			<?php
		}

	}

	/**
	 * Add Password and/or Custom Registration Fields
	 *
	 *
	 * @since @@version
	 *
	 */
	public function register_fields(){
		if( get_option( 'wplf_registration_email_as_un', false ) ){
			echo "<style>#registerform > p:first-child { display: none; }</style>";
		}

		$custom_fields = get_option( 'wplf_registration_custom_fields', array() );
		$pw_custom_order = false;

		if( ! empty( $custom_fields ) ){

			foreach( $custom_fields as $custom_field ){
				$name = esc_attr( $custom_field['meta_key'] );
				// Allow setting a field with "password" meta key to define where to output password field among other custom fields
				if( $name === 'password' && get_option( "wplf_register_set_pw", false ) ){
					$this->output_pw_fields();
					$pw_custom_order = true;
					continue;
				}
				echo "<p class=\"\">";
				echo "<label for=\"{$name}\">";
				echo $custom_field['label'];
				echo "<input type=\"text\" class=\"input\" name=\"{$name}\" value=\"\" size=\"25\">";
				echo "</label>";
				echo "</p>";
			}

		}

		// If not defined with custom order, output at end of form
		if( ! $pw_custom_order ){
			$this->output_pw_fields();
		}
	}

	/**
	 * Filter Registration Errors
	 *
	 *
	 * @param $errors
	 * @param $sanitized_user_login
	 * @param $user_email
	 *
	 * @return mixed
	 * @since @@version
	 *
	 */
	public function registration_errors( $errors, $sanitized_user_login, $user_email ) {

		// Remove empty username error if setting enabled to use email as username
		if ( get_option( 'wplf_registration_email_as_un', false ) ) {
			if ( isset( $errors->errors['empty_username'] ) ) {
				unset( $errors->errors['empty_username'] );
			}
		}

		if ( get_option( "wplf_register_set_pw", false ) ) {
			if ( empty( $_POST['pass1'] ) || ( ! empty( $_POST['pass1'] ) && trim( $_POST['pass1'] ) == '' ) ) {
				$errors->add( 'password1_error', __( '<strong>ERROR</strong>: Password field is required.' ) );
			}
		}

		$custom_fields = get_option( 'wplf_registration_custom_fields', array() );

		if ( ! empty( $custom_fields ) ) {
			foreach( $custom_fields as $custom_field ){
				$mk = $custom_field['meta_key'];
				// we don't do any handling for password meta key fields -- this is used for custom sorting of fields
				if( $mk === 'password' ){
					continue;
				}
				if( array_key_exists( 'required', $custom_field ) && ! empty( $custom_field['required'] ) ){

					$custom_value = array_key_exists( $mk, $_POST ) ? sanitize_text_field( $_POST[ $mk ] ) : false;
					if( empty( $custom_value ) ){
						$errors->add( "{$mk}_error", sprintf( __( '<strong>ERROR</strong>: %s field is required.' ), $custom_field['label'] ) );
						break;
					}

				}
			}
		}

		return $errors;
	}

	/**
	 * New User Registered
	 *
	 *
	 * @param $user_id
	 *
	 * @since @@version
	 *
	 */
	public function user_registered( $user_id ){

		$custom_fields   = get_option( 'wplf_registration_custom_fields', array() );

		if ( ! empty( $custom_fields ) ) {

			foreach ( $custom_fields as $custom_field ) {
				$mk = $custom_field['meta_key'];
				// we don't do any handling for password meta key fields -- this is used for custom sorting of fields
				if ( $mk === 'password' ) {
					continue;
				}

				// Validation is done for required fields in $this->registration_errors()
				$custom_value = array_key_exists( $mk, $_POST ) ? sanitize_text_field( $_POST[ $mk ] ) : false;
				if( ! empty( $custom_value ) ){
					update_user_meta( $user_id, $mk, $custom_value );
				}
			}

		}

		if ( get_option( "wplf_auto_login", false ) ) {
			wp_set_current_user( $user_id );
			wp_set_auth_cookie( $user_id );

//			$redirect = get_option( "alnuar_auto_login_new_user_after_registration_redirect" );
//
//			if ( $redirect == "" ) {
//				global $_POST;
//				if ( $_POST['redirect_to'] == "" ) {
//					$redirect = get_home_url();
//					$redirect .= "/wp-login.php?checkemail=registered";
//				} else {
//					$redirect = $_POST['redirect_to'];
//				}
//			}
//
//			wp_redirect( $redirect );
//
//			wp_new_user_notification( $user_id, null, 'both' ); //'admin' or blank sends admin notification email only. Anything else will send admin email and user email

			exit;
		}

	}
}