<?php

if ( ! defined( 'ABSPATH' ) ) exit;

// Prevent Wordpress from sending default notification, instead use our custom one
if ( ! function_exists( 'wp_new_user_notification' ) ) :

	/**
	 * @param        $user_id
	 * @param string $plaintext_pass
	 *
	 * @return bool
	 */
	function wp_new_user_notification( $user_id, $plaintext_pass = '' ) {
		global $wpdb, $wp_hasher;

		/**
		 *   Start WordPress Core password reset key generation
		 *
		 *  All code in this section below was pulled from the core WordPress
		 *  reset password function in wp-login.php
		 *
		 */
		$user = new WP_User( $user_id );

		$user_login = stripslashes( $user->user_login );
		$user_email = stripslashes( $user->user_email );

		// Generate something random for a password reset key.
		$key = wp_generate_password( 20, FALSE );

		/**
		 * Fires when a activation key is generated.
		 *
		 * @since @@version
		 *
		 * @param string $user_login The username for the user.
		 * @param string $key        The generated password reset key.
		 */
		do_action( 'retrieve_activate_key', $user_login, $key );

		// Now insert the key, hashed, into the DB.
		if ( empty( $wp_hasher ) ) {
			require_once ABSPATH . WPINC . '/class-phpass.php';
			$wp_hasher = new PasswordHash( 8, TRUE );
		}

		$hashed = $wp_hasher->HashPassword( $key );
		$wpdb->update( $wpdb->users, array( 'user_activation_key' => $hashed ), array( 'user_login' => $user_login ) );

		/**
		 *
		 *  End WordPress Core password reset key generation
		 *
		 */

		// Set option needs to be activated
		$activation = new WP_Login_Flow_User_Activation();
		$activation->set( $user_id, 1 );

		// Values passed to activation email template
		$template_data = array( 'wp_user_name' => $user_login, 'wp_user_email' => $user_email, 'wp_activation_key' => $key,
		                        'wp_activate_url' => $activation->get_url( $key, $user_login ) );

		$template = new WP_Login_Flow_Template();

		$default_subject = sprintf( __( '[%s] Account Activation' ), get_option( 'blogname' ) );
		$default_message  = sprintf( __( "Welcome to %s! Please visit the link below to activate your account and set your password:" ), get_option( 'blogname' ) ) . "\r\n\r\n";
		$default_message .= '<' . $activation->get_url( $key, $user_login ) . ">\r\n";

		$subject = $template->generate( 'wplf_activation_subject', $default_subject, $template_data );
		$message = $template->generate( 'wplf_activation_message', $default_message, $template_data );

		// New User Activation Email
		if ( ! wp_mail( $user_email, wp_specialchars_decode( $subject ), $message ) ) return FALSE;

		return TRUE;

	}

endif;
