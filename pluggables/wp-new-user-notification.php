<?php

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Prevent Wordpress from sending default notification, instead use our custom one
 *
 * We use this instead of filtering on the default function to prevent running duplicate code (generating pass), and
 * because of the potential use over another theme/plugin overriding this function, and not including the standard
 * filters.
 *
 * @since @@version
 * @package WP Login Flow
 */
if ( ! function_exists( 'wp_new_user_notification' ) ) :
	/**
	 * Email login details (with activation link or basic details if set pw enabled)
	 *
	 * A new user registration notification is also sent to admin email.
	 *
	 * @param int           $user_id    User ID.
	 * @param null          $deprecated Not used (argument deprecated).
	 * @param string        $notify     Optional. Type of notification that should happen. Accepts 'admin' or an empty
	 *                                  string (admin only), 'user', or 'both' (admin and user). Default empty.
	 *
	 * @since 4.6.0 The `$notify` parameter accepts 'user' for sending notification only to the user created.
	 *
	 * @global wpdb         $wpdb       WordPress database object for queries.
	 * @global PasswordHash $wp_hasher  Portable PHP password hashing framework instance.
	 *
	 * @since 2.0.0
	 * @since 4.3.0 The `$plaintext_pass` parameter was changed to `$notify`.
	 * @since 4.3.1 The `$plaintext_pass` parameter was deprecated. `$notify` added as a third parameter.
	 */
	function wp_new_user_notification( $user_id, $deprecated = null, $notify = '' ) {

		if ( $deprecated !== null ) {
			_deprecated_argument( __FUNCTION__, '4.3.1' );
		}

		// Accepts only 'user', 'admin' , 'both' or default '' as $notify
		if ( ! in_array( $notify, array( 'user', 'admin', 'both', '' ), true ) ) {
			return;
		}

		global $wpdb, $wp_hasher;

		/**
		 *   Start WordPress Core password reset key generation
		 *
		 *  All code in this section below was pulled from the core WordPress
		 *  reset password function in wp-login.php
		 *
		 */
		$user = new WP_User( $user_id );
		$admin_created = isset( $_POST['createuser'], $_POST['action'] ) && $_POST['action'] === 'createuser';
		$user_login = stripslashes( $user->user_login );
		$user_email = stripslashes( $user->user_email );
		$user_set_pw = get_option( 'wplf_register_set_pw', false );

		// The blogname option is escaped with esc_html on the way into the database in sanitize_option
		// we want to reverse this for the plain text arena of emails.
		$blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );

		// Send admin notification email (this is same as default WordPress fn)
		if ( 'user' !== $notify ) {
			$switched_locale = switch_to_locale( get_locale() );

			/* translators: %s: site title */
			$message = sprintf( __( 'New user registration on your site %s:' ), $blogname ) . "\r\n\r\n";
			/* translators: %s: user login */
			$message .= sprintf( __( 'Username: %s' ), $user->user_login ) . "\r\n\r\n";
			/* translators: %s: user email address */
			$message .= sprintf( __( 'Email: %s' ), $user->user_email ) . "\r\n";

			$wp_new_user_notification_email_admin = array(
				'to'      => get_option( 'admin_email' ),
				/* translators: Password change notification email subject. %s: Site title */
				'subject' => __( '[%s] New User Registration' ),
				'message' => $message,
				'headers' => '',
			);

			/**
			 * Filters the contents of the new user notification email sent to the site admin.
			 *
			 * @param array   $wp_new_user_notification_email {
			 *                                                Used to build wp_mail().
			 *
			 * @type string   $to                             The intended recipient - site admin email address.
			 * @type string   $subject                        The subject of the email.
			 * @type string   $message                        The body of the email.
			 * @type string   $headers                        The headers of the email.
			 * }
			 *
			 * @param WP_User $user                           User object for new user.
			 * @param string  $blogname                       The site title.
			 *
			 * @since 4.9.0
			 *
			 */
			$wp_new_user_notification_email_admin = apply_filters( 'wp_new_user_notification_email_admin', $wp_new_user_notification_email_admin, $user, $blogname );

			@wp_mail(
				$wp_new_user_notification_email_admin['to'],
				wp_specialchars_decode( sprintf( $wp_new_user_notification_email_admin['subject'], $blogname ) ),
				$wp_new_user_notification_email_admin['message'],
				$wp_new_user_notification_email_admin['headers']
			);

			if ( $switched_locale ) {
				restore_previous_locale();
			}
		}

		/**
		 *
		 *  End WordPress Core password reset key generation
		 *
		 */
		$activation = new WP_Login_Flow_User_Activation();

		// Set meta to activated (or not activated if user set own pw)
		$activation->set( $user_id, $user_set_pw ? 1 : 0 );

		// Values passed to email template
		$template_data = array(
			'wp_user_name'      => $user_login,
			'wp_user_email'     => $user_email
		);

		$template = new WP_Login_Flow_Template();

		if( ! $user_set_pw ){

			// Generate something random for a password reset key.
			$key = wp_generate_password( 20, false );

			/**
			 * Fires when a activation key is generated.
			 *
			 * @param string $user_login The username for the user.
			 * @param string $key        The generated password reset key.
			 *
			 * @since 1.0.0
			 *
			 */
			do_action( 'retrieve_activate_key', $user_login, $key );

			// Now insert the key, hashed, into the DB.
			if ( empty( $wp_hasher ) ) {
				require_once ABSPATH . WPINC . '/class-phpass.php';
				$wp_hasher = new PasswordHash( 8, true );
			}

			$hashed = time() . ':' . $wp_hasher->HashPassword( $key );
			$wpdb->update( $wpdb->users, array( 'user_activation_key' => $hashed ), array( 'user_login' => $user->user_login ) );

			// Add activation template data if account requires activation
			$template_data['wp_activation_key'] = $key;
			$template_data['wp_activate_url']   = $activation->get_url( $key, $user_login );

			$default_subject = sprintf( __( '[%s] Account Activation' ), get_option( 'blogname' ) );
			$default_message = sprintf( __( "Welcome to %s! Please visit the link below to activate your account and set your password:" ), get_option( 'blogname' ) ) . "\r\n\r\n";
			$default_message .= '<' . $activation->get_url( $key, $user_login ) . ">\r\n";

			$subject = $template->generate( 'wplf_activation_subject', $default_subject, $template_data );
			$message = $template->generate( 'wplf_activation_message', $default_message, $template_data );
		} else {
			$default_subject = sprintf( __( '[%s] Account Information' ), get_option( 'blogname' ) );
			$default_message = sprintf( __( "Welcome to %s! Your account information is as follows:" ), get_option( 'blogname' ) ) . "\r\n\r\n";
			$default_message .= sprintf( __( "Username: %s" ), '%wp_user_name%' ) . "\r\n\r\n";
			$default_message .= __( "You can login to your account at the URL below:" ) . "\r\n\r\n" . '<a href="%wp_login_url%">%wp_login_url%</a>';

			$subject = $template->generate( 'wplf_new_user_subject', $default_subject, $template_data );
			$message = $template->generate( 'wplf_new_user_message', $default_message, $template_data );
		}

		// Send email and return bool if sent
		if ( ! wp_mail( $user_email, wp_specialchars_decode( $subject ), $message , 'Content-type: text/html') ) return FALSE;

		return TRUE;

	}

endif;
