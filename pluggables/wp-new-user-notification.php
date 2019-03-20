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
		$user_set_pw = get_option( 'wplf_register_set_pw', false );

		if( ! $user_set_pw ) {
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
		}

		/**
		 *
		 *  End WordPress Core password reset key generation
		 *
		 */
		$activation = new WP_Login_Flow_User_Activation();

		// Check for cookie being set and a few POST vars to determine if new user created in admin backend and require activation is unchecked
		if ( isset( $_POST['createuser'] ) && ! isset( $_POST['send_password'] ) && isset( $_COOKIE[ 'wp_logged_in_' . COOKIEHASH ] ) ){
			// Set account to activated and exit to prevent activation email from being sent
			$activation->set( $user_id, 1 );
			return;
		}

		$user_activated = $user_set_pw ? 1 : 0;
		// Set meta to activated (or not activated if user set own pw)
		$activation->set( $user_id, $user_activated );

		// Values passed to email template
		$template_data = array(
			'wp_user_name'      => $user_login,
			'wp_user_email'     => $user_email
		);

		// Add activation template data if account requires activation
		if( ! $user_set_pw && isset( $key ) ){
			$template_data['wp_activation_key'] = $key;
			$template_data['wp_activate_url'] = $activation->get_url( $key, $user_login );
		}

		$template = new WP_Login_Flow_Template();

		if( ! $user_set_pw ){
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


		// New User Activation Email
		if ( ! wp_mail( $user_email, wp_specialchars_decode( $subject ), $message , 'Content-type: text/html') ) return FALSE;

		return TRUE;

	}

endif;
